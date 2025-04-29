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
use Jot\HfShield\AuthOption\Webauthn\Exception\InvalidPublicKeyCredentialException;
use Jot\HfShield\Entity\User\User;
use Jot\HfShield\Entity\UserCredential\UserCredential;
use Jot\HfShield\Repository\UserChallengeRepository;
use Jot\HfShield\Repository\UserCredentialRepository;
use Jot\HfShield\Repository\UserRepository;
use League\OAuth2\Server\CryptTrait;
use ParagonIE\ConstantTime\Base64UrlSafe;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\CeremonyStep\CeremonyStepManagerFactory;
use Webauthn\PublicKeyCredential;
use Webauthn\PublicKeyCredentialSource;
use function Hyperf\Support\make;

trait WebauthnRegisterCredentialsHandler
{
    use CryptTrait;

    #[Inject]
    protected UserChallengeRepository $userChallengeRepository;

    #[Inject]
    protected UserCredentialRepository $userCredentialRepository;

    #[Inject]
    protected UserRepository $userRepository;

    private function loadPublicKeyCredential(string $publicKeyCredential): PublicKeyCredential
    {
        $publicKeyCredential = $this->serializer->deserialize(
            data: $publicKeyCredential,
            type: PublicKeyCredential::class,
            format: 'json'
        );

        if (! $publicKeyCredential->response instanceof AuthenticatorAttestationResponse) {
            throw new InvalidPublicKeyCredentialException();
        }

        return $publicKeyCredential;
    }

    private function storeCredentials(PublicKeyCredentialSource $publicKeyCredential, array $user): void
    {
        $userCredentialEntity = make(UserCredential::class, [
            'data' => [
                'id' => Base64UrlSafe::encodeUnpadded($publicKeyCredential->publicKeyCredentialId),
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['name'],
                ],
                'content' => $this->encrypt(serialize($publicKeyCredential)),
            ],
        ]);

        $this->userCredentialRepository->create($userCredentialEntity);
    }

    private function updateUserTags(array $user): void
    {
        $user = $this->userRepository->find($user['id']);
        $userData = $user->hide(['password', 'password_salt'])->toArray();
        $userData['tags'][] = 'webauthn_enabled';
        $userData['tags'] = array_values(array_unique($userData['tags']));

        $entity = make(User::class, ['data' => $userData]);
        $this->userRepository->update($entity);
    }

    private function checkAttestation(PublicKeyCredential $publicKeyCredential, string $userId): PublicKeyCredentialSource
    {
        $authenticatorAttestationResponseValidator = new AuthenticatorAttestationResponseValidator(
            ceremonyStepManager: (new CeremonyStepManagerFactory())->creationCeremony(),
        );

        $userChallenge = $this->userChallengeRepository
            ->find(Base64UrlSafe::encodeUnpadded($publicKeyCredential->response->clientDataJSON->challenge))
            ->toArray();

        $publicKeyCredentialOptions = unserialize($this->decrypt($userChallenge['content']));

        return $authenticatorAttestationResponseValidator->check(
            authenticatorAttestationResponse: $publicKeyCredential->response,
            publicKeyCredentialCreationOptions: $publicKeyCredentialOptions,
            host: $this->request->getUri()->getHost()
        );
    }
}
