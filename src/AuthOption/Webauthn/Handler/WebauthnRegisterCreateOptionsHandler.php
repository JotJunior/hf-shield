<?php

declare(strict_types=1);
/**
 * This file is part of the hf_shield module, a package build for Hyperf framework that is responsible for OAuth2 authentication and access control.
 *
 * @author   Joao Zanon <jot@jot.com.br>
 * @link     https://github.com/JotJunior/hf-shield
 * @license  MIT
 */

namespace Jot\HfShield\AuthOption\Webauthn\Handler;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Jot\HfRepository\Entity\EntityInterface;
use Jot\HfShield\Entity\UserChallenge\UserChallenge;
use Jot\HfShield\Repository\UserChallengeRepository;
use Jot\HfShield\Repository\UserCredentialRepository;
use ParagonIE\ConstantTime\Base64UrlSafe;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialDescriptor;
use Webauthn\PublicKeyCredentialParameters;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialUserEntity;

use function Hyperf\Support\make;

trait WebauthnRegisterCreateOptionsHandler
{
    #[Inject]
    protected UserChallengeRepository $userChallengeRepository;

    #[Inject]
    protected UserCredentialRepository $userCredentialRepository;

    private function createPublicKeyCredentialOptions(array $user): PublicKeyCredentialCreationOptions
    {
        $rpEntity = PublicKeyCredentialRpEntity::create(
            name: $this->configService->get('hf_shield.app_name', ''),
            id: $this->request->getUri()->getHost()
        );

        $userEntity = PublicKeyCredentialUserEntity::create(
            name: $user['email'],
            id: $user['id'],
            displayName: $user['name'],
            icon: $user['picture'] ?? null
        );

        $challenge = random_bytes(32);

        $myCredentials = $this->userCredentialRepository->paginate(
            params: ['user.id' => $user['id'], '_sort' => 'created_at:desc'],
            perPage: 100
        );

        $excludedPublicKeyDescriptors = [];
        foreach ($myCredentials['data'] as $credential) {
            $excludedPublicKeyDescriptors[] = PublicKeyCredentialDescriptor::create('public-key', Base64UrlSafe::decode($credential['id']));
        }

        $algorithms = $this->configService->get('hf_webauthn.creation_profile.public_key_credential_parameters');
        $publicKeyCredentialParametersList = array_map(fn ($algo) => PublicKeyCredentialParameters::create('public-key', $algo), $algorithms);

        return PublicKeyCredentialCreationOptions::create(
            rp: $rpEntity,
            user: $userEntity,
            challenge: $challenge,
            pubKeyCredParams: $publicKeyCredentialParametersList,
            authenticatorSelection: AuthenticatorSelectionCriteria::create(
                authenticatorAttachment: AuthenticatorSelectionCriteria::AUTHENTICATOR_ATTACHMENT_PLATFORM,
                userVerification: AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_REQUIRED,
                residentKey: AuthenticatorSelectionCriteria::RESIDENT_KEY_REQUIREMENT_REQUIRED
            ),
            attestation: PublicKeyCredentialCreationOptions::ATTESTATION_CONVEYANCE_PREFERENCE_NONE,
            excludeCredentials: $excludedPublicKeyDescriptors,
            timeout: 60000
        );
    }

    private function storeChallenge(PublicKeyCredentialCreationOptions $options, array $user): EntityInterface
    {
        $userChallenge = make(UserChallenge::class, [
            'data' => [
                'id' => Base64UrlSafe::encodeUnpadded($options->challenge),
                'name' => $options::class,
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['name'],
                ],
                'content' => $this->encrypt(serialize($options)),
            ],
        ]);

        return $this->userChallengeRepository->create($userChallenge);
    }

    private function serializeToJson(PublicKeyCredentialCreationOptions $options): string
    {
        return $this->serializer->serialize(
            data: $options,
            format: 'json',
            context: [
                AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
                JsonEncode::OPTIONS => JSON_THROW_ON_ERROR,
            ]
        );
    }

    private function createJsonResponse(string $jsonContent): ResponseInterface
    {
        return $this->response
            ->withHeader('Content-Type', self::CONTENT_TYPE_JSON)
            ->withStatus(self::HTTP_STATUS_OK)
            ->withBody(new SwooleStream($jsonContent));
    }
}
