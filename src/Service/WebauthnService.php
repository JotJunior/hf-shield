<?php

declare(strict_types=1);
/**
 * This file is part of the hf_shield module, a package build for Hyperf framework that is responsible for OAuth2 authentication and access control.
 *
 * @author   Joao Zanon <jot@jot.com.br>
 * @link     https://github.com/JotJunior/hf-shield
 * @license  MIT
 */

namespace Jot\HfShield\Service;

use Cose\Algorithms;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Annotation\Inject;
use Jot\HfShield\Entity\UserWebauthnChallenge\UserWebauthnChallenge;
use Jot\HfShield\Entity\UserWebauthnCredential\UserWebauthnCredential;
use Jot\HfShield\Exception\UnauthorizedAccessException;
use Jot\HfShield\Exception\WebauthnInvalidAttestationResponse;
use Jot\HfShield\Exception\WebauthnInvalidCredentialData;
use Jot\HfShield\Exception\WebauthnInvalidCredentialResponse;
use Jot\HfShield\Exception\WebauthnMissingActiveChallenge;
use Jot\HfShield\Exception\WebauthnMissingParameters;
use Jot\HfShield\Repository\WebauthnChallengeRepository;
use Jot\HfShield\Repository\WebauthnCredentialRepository;
use Throwable;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\PublicKeyCredential;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialDescriptor;
use Webauthn\PublicKeyCredentialParameters;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\PublicKeyCredentialUserEntity;
use Webauthn\TrustPath\EmptyTrustPath;

class WebauthnService
{
    #[Inject]
    protected WebauthnCredentialRepository $credentialRepository;

    #[Inject]
    protected WebauthnChallengeRepository $challengeRepository;

    private string $rpId;

    private string $rpName;

    private ?string $rpIcon;

    private string $origin;

    public function __construct(
        private AuthenticatorAttestationResponseValidator $attestationResponseValidator,
        private AuthenticatorAssertionResponseValidator   $assertionResponseValidator,
        private ConfigInterface                           $config
    )
    {
        $config = $this->config->get('hf_webauthn');
        $this->rpId = $config['rp_id'] ?? parse_url($config['origin'] ?? 'https://example.com', PHP_URL_HOST);
        $this->rpName = $config['rp_name'] ?? 'HF Shield Application';
        $this->rpIcon = $config['rp_icon'] ?? null;
        $this->origin = $config['origin'] ?? 'https://example.com';
    }

    public function getRegistrationOptions(array $data): array
    {
        if (empty($data['user_id']) || empty($data['user_name'])) {
            throw new UnauthorizedAccessException();
        }

        $existingCredentials = $this->credentialRepository->findByUserId($data['user_id']);
        $excludeCredentials = [];

        foreach ($existingCredentials as $credential) {
            if (is_array($credential) && isset($credential['content'])) {
                $credentialSource = $this->deserializeCredential($credential['content']);
                $excludeCredentials[] = new PublicKeyCredentialDescriptor(
                    PublicKeyCredentialDescriptor::CREDENTIAL_TYPE_PUBLIC_KEY,
                    $credentialSource->publicKeyCredentialId
                );
            } elseif (is_object($credential) && method_exists($credential, 'getContent')) {
                $credentialSource = $this->deserializeCredential($credential->getContent());
                $excludeCredentials[] = new PublicKeyCredentialDescriptor(
                    PublicKeyCredentialDescriptor::CREDENTIAL_TYPE_PUBLIC_KEY,
                    $credentialSource->publicKeyCredentialId
                );
            }
        }

        $rpEntity = new PublicKeyCredentialRpEntity(
            $this->rpName,
            $this->rpId,
            $this->rpIcon
        );

        $userEntity = new PublicKeyCredentialUserEntity(
            $data['user_name'],
            $data['user_id'],
            $data['user_name']
        );

        $authenticatorSelectionCriteria = new AuthenticatorSelectionCriteria(
            authenticatorAttachment: AuthenticatorSelectionCriteria::AUTHENTICATOR_ATTACHMENT_PLATFORM,
            userVerification: AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_PREFERRED,
            residentKey: AuthenticatorSelectionCriteria::RESIDENT_KEY_REQUIREMENT_NO_PREFERENCE
        );

        $publicKeyCredentialParametersList = [
            new PublicKeyCredentialParameters(
                type: PublicKeyCredentialDescriptor::CREDENTIAL_TYPE_PUBLIC_KEY,
                alg: Algorithms::COSE_ALGORITHM_ES256
            ),
            new PublicKeyCredentialParameters(
                type: PublicKeyCredentialDescriptor::CREDENTIAL_TYPE_PUBLIC_KEY,
                alg: Algorithms::COSE_ALGORITHM_RS256
            ),
        ];

        $publicKeyCredentialCreationOptions = new PublicKeyCredentialCreationOptions(
            rp: $rpEntity,
            user: $userEntity,
            challenge: random_bytes(32),
            pubKeyCredParams: $publicKeyCredentialParametersList,
            authenticatorSelection: $authenticatorSelectionCriteria,
            attestation: PublicKeyCredentialCreationOptions::ATTESTATION_CONVEYANCE_PREFERENCE_NONE,
            excludeCredentials: $excludeCredentials,
            timeout: null,
            extensions: null
        );

        $challenge = base64_encode($publicKeyCredentialCreationOptions->challenge);

        $challengeEntity = new UserWebauthnChallenge([
            'user' => [
                'id' => $data['user_id'],
            ],
            'challenge' => $challenge,
            'status' => 'pending',
        ]);

        return $this->challengeRepository->create($challengeEntity)->toArray();
    }

    private function deserializeCredential(string $serialized): PublicKeyCredentialSource
    {
        return unserialize($serialized);
    }

    public function verifyRegistration(array $data)
    {
        if (empty($data['user_id']) || empty($data['user_name']) || empty($data['credential'])) {
            throw new WebauthnMissingParameters();
        }

        $challenge = $this->challengeRepository->findActiveByUserId($data['user_id']);
        if (!$challenge) {
            throw new WebauthnMissingActiveChallenge();
        }

        $rpEntity = new PublicKeyCredentialRpEntity(
            $this->rpName,
            $this->rpId,
            $this->rpIcon
        );

        $userEntity = new PublicKeyCredentialUserEntity(
            $data['user_name'],
            $data['user_id'],
            $data['user_name']
        );

        $publicKeyCredential = $this->parseCredential($data['credential']);
        $attestationResponse = $publicKeyCredential->response;

        $publicKeyCredentialSource = $this->attestationResponseValidator->check(
            $attestationResponse,
            new PublicKeyCredentialCreationOptions(
                rp: $rpEntity,
                user: $userEntity,
                challenge: base64_decode($challenge['challenge']),
                pubKeyCredParams: [],
                authenticatorSelection: null,
                attestation: PublicKeyCredentialCreationOptions::ATTESTATION_CONVEYANCE_PREFERENCE_NONE,
                excludeCredentials: [],
                timeout: null
            ),
            $this->origin
        );

        $this->saveCredentialSource($publicKeyCredentialSource, $data['user_id'], $data['user_name']);

        $this->challengeRepository->complete($challenge['id']);

        return [
            'result' => 'success',
            'data' => [
                'credential_id' => base64_encode($publicKeyCredentialSource->getPublicKeyCredentialId()),
            ],
        ];

    }

    private function parseCredential(array $credentialData): PublicKeyCredential
    {
        if (!isset($credentialData['id'], $credentialData['rawId'], $credentialData['type'], $credentialData['response'])) {
            throw new WebauthnInvalidCredentialData();
        }

        $rawId = base64_decode($credentialData['rawId']);

        return new PublicKeyCredential(
            $credentialData['type'],
            $rawId,
            $this->parseCredentialResponse($credentialData['response'], $rawId)
        );
    }

    private function parseCredentialResponse(array $responseData, string $rawId): AuthenticatorAssertionResponse|AuthenticatorAttestationResponse
    {
        try {
            // Criar um PublicKeyCredential a partir dos dados recebidos
            $publicKeyCredential = $this->parseCredential([
                'id' => $rawId,
                'rawId' => $rawId,
                'type' => 'public-key',
                'response' => $responseData,
            ]);

            // Obter a resposta do authenticator
            $response = $publicKeyCredential->response;

            // Verificar o tipo de resposta
            if ($response instanceof AuthenticatorAttestationResponse) {
                return $response;
            }

            if ($response instanceof AuthenticatorAssertionResponse) {
                return $response;
            }

            throw new WebauthnInvalidCredentialResponse('Invalid credential response type');
        } catch (\Throwable $e) {
            throw new WebauthnInvalidCredentialResponse('Failed to parse credential: ' . $e->getMessage());
        }
    }

    private function saveCredentialSource(PublicKeyCredentialSource $publicKeyCredentialSource, string $userId, string $userName): void
    {
        $credentialEntity = new UserWebauthnCredential();
        $credentialEntity->hydrate([
            'data' => [
                'user' => [
                    'id' => $userId,
                    'name' => $userName,
                ],
                'content' => serialize($publicKeyCredentialSource),
                'status' => 'active',
            ],
        ]);
        $this->credentialRepository->create($credentialEntity);
    }

    public function getAuthenticationOptions(array $data): array
    {
        if (empty($data['user_id'])) {
            throw new WebauthnMissingParameters();
        }

        $existingCredentials = $this->credentialRepository->findByUserId($data['user_id']);
        if (empty($existingCredentials)) {
            throw new WebauthnMissingActiveChallenge();
        }

        $allowedCredentials = [];
        foreach ($existingCredentials as $credential) {
            if (is_array($credential) && isset($credential['content'])) {
                $credentialSource = $this->deserializeCredential($credential['content']);
                $allowedCredentials[] = new PublicKeyCredentialDescriptor(
                    PublicKeyCredentialDescriptor::CREDENTIAL_TYPE_PUBLIC_KEY,
                    $credentialSource->getPublicKeyCredentialId()
                );
            } elseif (is_object($credential) && method_exists($credential, 'getContent')) {
                $credentialSource = $this->deserializeCredential($credential->getContent());
                $allowedCredentials[] = new PublicKeyCredentialDescriptor(
                    PublicKeyCredentialDescriptor::CREDENTIAL_TYPE_PUBLIC_KEY,
                    $credentialSource->getPublicKeyCredentialId()
                );
            }
        }

        $publicKeyCredentialRequestOptions = new PublicKeyCredentialRequestOptions(
            random_bytes(32),
            rpId: $this->rpId,
            allowCredentials: $allowedCredentials,
            userVerification: 'preferred',
            timeout: null,
            extensions: null
        );

        $challenge = base64_encode($publicKeyCredentialRequestOptions->challenge);

        // Obter o primeiro usuário
        $user = null;
        if (is_array($existingCredentials[0]) && isset($existingCredentials[0]['user'])) {
            $user = $existingCredentials[0]['user'];
        } elseif (is_object($existingCredentials[0]) && method_exists($existingCredentials[0], 'getUser')) {
            $user = $existingCredentials[0]->getUser();
        }

        if (!$user) {
            throw new WebauthnMissingParameters();
        }

        $challengeEntity = new UserWebauthnChallenge();
        $challengeEntity->hydrate([
            'user' => $user,
            'challenge' => $challenge,
        ]);

        $savedChallenge = $this->challengeRepository->create($challengeEntity)->toArray();

        return [
            'result' => 'success',
            'data' => $publicKeyCredentialRequestOptions,
            'challenge' => $savedChallenge,
        ];
    }

    /**
     * Get user by ID - this method should be implemented according to your user repository
     */
    private function getUser(string $userId): ?array
    {
        // Implementar de acordo com o repositório de usuários
        // Por enquanto, retornaremos um array simples
        return [
            'id' => $userId,
            'name' => 'User ' . $userId,
        ];
    }

    public function verifyAuthentication(array $data): array
    {
        if (empty($data['user_id']) || empty($data['credential'])) {
            throw new WebauthnMissingParameters();
        }

        $challenge = $this->challengeRepository->findActiveByUserId($data['user_id']);
        if (!$challenge) {
            throw new WebauthnMissingActiveChallenge();
        }

        try {
            $publicKeyCredential = $this->parseCredential($data['credential']);
            $assertionResponse = $publicKeyCredential->getResponse();

            if (!$assertionResponse instanceof AuthenticatorAssertionResponse) {
                throw new WebauthnInvalidAttestationResponse();
            }

            $credentialSources = $this->getCredentialSourcesForUser($data['user_id']);
            if (empty($credentialSources)) {
                throw new WebauthnMissingParameters();
            }

            $publicKeyCredentialSource = new PublicKeyCredentialSource(
                publicKeyCredentialId: $publicKeyCredential->rawId,
                type: PublicKeyCredentialDescriptor::CREDENTIAL_TYPE_PUBLIC_KEY,
                transports: [],
                attestationType: $assertionResponse->attestationType,
                trustPath: new EmptyTrustPath(),
                aaguid: $assertionResponse->aaguid,
                credentialPublicKey: $assertionResponse->credentialPublicKey,
                userHandle: $data['user_id'],
                counter: $assertionResponse->counter
            );

            $publicKeyCredentialRequestOptions = new PublicKeyCredentialRequestOptions(
                challenge: base64_decode($challenge['challenge']),
                rpId: $this->rpId,
                allowCredentials: [],
                userVerification: 'preferred',
                timeout: null
            );

            $this->assertionResponseValidator->check(
                publicKeyCredentialSource: $publicKeyCredentialSource,
                authenticatorAssertionResponse: $assertionResponse,
                publicKeyCredentialRequestOptions: $publicKeyCredentialRequestOptions,
                host: $this->origin,
                userHandle: $data['user_id'],
            );

            $publicKeyCredentialSource = $this->assertionResponseValidator->check(
                $publicKeyCredentialSource,
                $assertionResponse,
                new PublicKeyCredentialRequestOptions(
                    challenge: base64_decode($challenge['challenge']),
                    rpId: $this->rpId,
                    allowCredentials: [],
                    userVerification: 'preferred',
                    timeout: null
                ),
                $this->origin,
                $data['user_id']
            );

            $this->challengeRepository->complete($challenge['id']);

            $user = $this->getUser($data['user_id']);
            if (!$user) {
                throw new WebauthnMissingParameters();
            }

            return [
                'result' => 'success',
                'data' => [
                    'user' => $user,
                ],
            ];
        } catch (Throwable $e) {
            return [
                'result' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get credential sources for user
     *
     * @return PublicKeyCredentialSource[]
     */
    private function getCredentialSourcesForUser(string $userId): array
    {
        $credentials = $this->credentialRepository->findByUserId($userId);
        $result = [];

        foreach ($credentials as $credential) {
            if (is_array($credential) && isset($credential['content'])) {
                $result[] = $this->deserializeCredential($credential['content']);
            } elseif (is_object($credential) && method_exists($credential, 'getContent')) {
                $result[] = $this->deserializeCredential($credential->getContent());
            }
        }

        return $result;
    }

    public function listCredentials(string $userId): array
    {

        $credentials = $this->credentialRepository->findByUserId($userId);
        $result = [];

        foreach ($credentials as $credential) {
            $credentialSource = $this->deserializeCredential($credential->getContent());
            $result[] = [
                'id' => $credential->getId(),
                'credential_id' => base64_encode($credentialSource->getPublicKeyCredentialId()),
                'created_at' => $credential->getCreatedAt(),
            ];
        }

        return $result;

    }

    public function delete(string $id): bool
    {
        return $this->credentialRepository->delete($id);
    }
}
