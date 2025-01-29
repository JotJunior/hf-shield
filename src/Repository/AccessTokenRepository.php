<?php

declare(strict_types=1);

namespace Jot\HfOAuth2\Repository;

use Jot\HfOAuth2\Entity\AccessToken\AccessToken;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use Jot\HfOAuth2\Entity\AccessTokenEntity;
use function Hyperf\Support\make;

class AccessTokenRepository extends AbstractRepository implements AccessTokenRepositoryInterface
{
    protected string $entity = AccessToken::class;

    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity): void
    {
        $entity = make($this->entity, ['data' => [
            'id' => $accessTokenEntity->getIdentifier(),
            'client' => [
                'id' => $accessTokenEntity->getClient()->getIdentifier(),
                'name' => $accessTokenEntity->getClient()->getName(),
                'redirect_uri' => $accessTokenEntity->getClient()->getRedirectUri(),
            ],
            'user' => [
                'id' => $accessTokenEntity->getUserIdentifier(),
            ],
            'scopes' => array_map(fn($scope) => ['id' => $scope->getIdentifier()], $accessTokenEntity->getScopes()),
            'expiry_date_time' => $accessTokenEntity->getExpiryDateTime(),
        ]]);
        $this->create($entity);
    }

    public function revokeAccessToken($tokenId): void
    {
        $this->delete($tokenId);
    }

    public function isAccessTokenRevoked($tokenId): bool
    {
        return !$this->exists($tokenId);
    }

    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null): AccessTokenEntityInterface
    {
        $newToken = new AccessTokenEntity();
        $newToken->setClient($clientEntity);
        $newToken->setUserIdentifier($userIdentifier);
        foreach ($scopes as $scope) {
            $newToken->addScope($scope);
        }
        return $newToken;
    }

}
