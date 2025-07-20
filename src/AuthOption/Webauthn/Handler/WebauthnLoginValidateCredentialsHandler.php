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
use Jot\HfShield\Repository\UserChallengeRepository;
use Jot\HfShield\Repository\UserCredentialRepository;
use Jot\HfShield\Repository\UserRepository;
use League\OAuth2\Server\CryptTrait;
use ParagonIE\ConstantTime\Base64UrlSafe;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\CeremonyStep\CeremonyStepManagerFactory;
use Webauthn\PublicKeyCredential;

trait WebauthnLoginValidateCredentialsHandler
{
    use CryptTrait;
    use AccessTokenHandler;

    #[Inject]
    protected UserRepository $userRepository;

    #[Inject]
    protected UserChallengeRepository $userChallengeRepository;

    #[Inject]
    protected UserCredentialRepository $userCredentialRepository;

    private function loadPublicKeyCredential(string $publicKeyCredential): PublicKeyCredential
    {
        $publicKeyCredential = $this->serializer->deserialize(
            data: $publicKeyCredential,
            type: PublicKeyCredential::class,
            format: 'json'
        );

        if (! $publicKeyCredential->response instanceof AuthenticatorAssertionResponse) {
            throw new InvalidPublicKeyCredentialException();
        }

        return $publicKeyCredential;
    }

    private function validateAssertion(PublicKeyCredential $publicKeyCredential): void
    {
        $authenticatorAssertionResponseValidator = new AuthenticatorAssertionResponseValidator(
            (new CeremonyStepManagerFactory())->requestCeremony()
        );

        $userId = $publicKeyCredential->response->userHandle;

        $credentialData = $this->userCredentialRepository->first([
            'host' => $this->request->getUri()->getHost(),
            'user.id' => $userId,
            '_sort' => 'created_at:desc',
        ])?->toArray();
        if (empty($credentialData)) {
            throw new InvalidPublicKeyCredentialException();
        }
        $publicKeyCredentialSource = unserialize($this->decrypt($credentialData['content']));

        $challengeData = $this->userChallengeRepository->find(Base64UrlSafe::encodeUnpadded($publicKeyCredential->response->clientDataJSON->challenge))?->toArray();
        if (empty($challengeData)) {
            throw new InvalidPublicKeyCredentialException();
        }
        $publicKeyCredentialRequestOptions = unserialize($this->decrypt($challengeData['content']));

        $authenticatorAssertionResponseValidator->check(
            publicKeyCredentialSource: $publicKeyCredentialSource,
            authenticatorAssertionResponse: $publicKeyCredential->response,
            publicKeyCredentialRequestOptions: $publicKeyCredentialRequestOptions,
            host: $this->request->getUri()->getHost(),
            userHandle: $userId
        );
    }
}
