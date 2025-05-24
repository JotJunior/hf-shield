<?php

declare(strict_types=1);
/**
 * This file is part of the hf_shield module, a package build for Hyperf framework that is responsible for OAuth2 authentication and access control.
 *
 * @author   Joao Zanon <jot@jot.com.br>
 * @link     https://github.com/JotJunior/hf-shield
 * @license  MIT
 */

namespace Jot\HfShield\AuthOption\WhatsApp\Handler;

use DateInterval;
use DateTimeImmutable;
use Hyperf\Di\Annotation\Inject;
use Jot\HfShield\Entity\AccessToken\AccessToken;
use Jot\HfShield\Entity\AccessTokenEntity;
use Jot\HfShield\Entity\ClientEntity;
use Jot\HfShield\Entity\ScopeEntity;
use Jot\HfShield\Repository\AccessTokenRepository;
use League\OAuth2\Server\CryptKey;

use function Hyperf\Support\make;

trait AccessTokenHandler
{
    #[Inject]
    protected AccessTokenRepository $accessTokenRepository;

    private function issueTokenString(array $data)
    {
        $user = $this->userRepository->find($data['user']['id']);

        $clientEntity = new ClientEntity();
        $clientEntity->setIdentifier($data['client_id']);
        $clientEntity->setName('');
        $clientEntity->setRedirectUri('');
        $clientEntity->setTenantId($user->tenant->id);

        $scopes = [];
        $scopeNames = explode(' ', $data['scope']);
        foreach ($scopeNames as $scopeName) {
            $scope = new ScopeEntity();
            $scope->setIdentifier($scopeName);
            $scopes[] = $scope;
        }

        $accessToken = new AccessTokenEntity();
        $accessToken->setClient($clientEntity);
        $accessToken->setUserIdentifier($user->id);
        $accessToken->setIdentifier(bin2hex(random_bytes(40)));

        foreach ($scopes as $scope) {
            $accessToken->addScope($scope);
        }

        $expiryDateTime = new DateTimeImmutable();
        $expiryDateTime = $expiryDateTime->add(new DateInterval('P30D'));
        $accessToken->setExpiryDateTime($expiryDateTime);

        $privateKey = file_get_contents(BASE_PATH . '/storage/keys/private.key');
        $cryptKey = new CryptKey($privateKey, null, false);
        $accessToken->setPrivateKey($cryptKey);

        $jwt = $accessToken->convertToJWT();

        $accessTokenEntity = make(AccessToken::class, [
            'data' => [
                'id' => $accessToken->getIdentifier(),
                'user' => [
                    'id' => $accessToken->getUserIdentifier(),
                ],
                'client' => [
                    'id' => $clientEntity->getIdentifier(),
                ],
                'scopes' => array_map(fn (ScopeEntity $scope) => ['id' => $scope->getIdentifier()], $accessToken->getScopes()),
                'tenant' => [
                    'id' => $clientEntity->getTenantId(),
                ],
                'metadata' => $this->accessTokenRepository->collectMetadata(),
            ],
        ]);
        $this->accessTokenRepository->create($accessTokenEntity);

        return $jwt->toString();
    }
}
