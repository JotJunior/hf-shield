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
use Jot\HfShield\Entity\UserChallenge\UserChallenge;
use Jot\HfShield\Exception\UnauthorizedAccessException;
use Jot\HfShield\Repository\UserChallengeRepository;
use Jot\HfShield\Repository\UserCredentialRepository;
use Jot\HfShield\Repository\UserRepository;
use League\OAuth2\Server\CryptTrait;
use ParagonIE\ConstantTime\Base64UrlSafe;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Webauthn\AuthenticationExtensions\AuthenticationExtension;
use Webauthn\PublicKeyCredential;
use Webauthn\PublicKeyCredentialRequestOptions;

use function Hyperf\Support\make;

trait WebauthnLoginCollectCredentialsHandler
{
    use CryptTrait;

    #[Inject]
    protected UserRepository $userRepository;

    #[Inject]
    protected UserChallengeRepository $userChallengeRepository;

    #[Inject]
    protected UserCredentialRepository $userCredentialRepository;

    private function getUser(array $data): array
    {
        if (empty($data['userId'])) {
            return [];
        }
        $userEntity = $this->userRepository->find($data['userId']);
        if (empty($userEntity)) {
            throw new UnauthorizedAccessException();
        }

        return $userEntity->toArray();
    }

    private function getPublicKeyCredentialRequestOptions(array $user): PublicKeyCredentialRequestOptions
    {
        $registeredAuthenticators = [];
        if (! empty($user)) {
            $users = $this->userRepository->search($user['userId'] ?? $user['username'], ['id', 'email'], ['id', 'name']);
            foreach ($users as $user) {
                $credential = $this->userCredentialRepository->first(['user.id' => $user->getId(), '_sort' => 'created_at:desc']);
                if (! empty($credential)) {
                    $credentials[] = $credential->toArray();
                }
            }
            foreach ($credentials as $credential) {
                /** @var PublicKeyCredential $publicKeyCredential */
                $publicKeyCredential = unserialize($this->decrypt($credential['content']));
                $registeredAuthenticators[] = $publicKeyCredential->getPublicKeyCredentialDescriptor();
            }
        }

        $publicKeyCredentialRequestOptions = PublicKeyCredentialRequestOptions::create(
            challenge: random_bytes(32),
            allowCredentials: $registeredAuthenticators,
            userVerification: PublicKeyCredentialRequestOptions::USER_VERIFICATION_REQUIREMENT_REQUIRED,
            extensions: [
                AuthenticationExtension::create('loc', true),
                AuthenticationExtension::create('txAuthSimple', 'Please log in with a registered authenticator'),
            ]
        );

        $this->userChallengeRepository->create(
            make(UserChallenge::class, [
                'data' => [
                    'id' => Base64UrlSafe::encodeUnpadded($publicKeyCredentialRequestOptions->challenge),
                    'content' => $this->encrypt(serialize($publicKeyCredentialRequestOptions)),
                ],
            ])
        );

        return $publicKeyCredentialRequestOptions;
    }

    private function serializeToJson(PublicKeyCredentialRequestOptions $options): string
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
