<?php

declare(strict_types=1);

namespace Jot\HfShield\Repository;

use Jot\HfShield\Entity\AccessToken\AccessToken;
use Jot\HfShield\Entity\AccessTokenEntity;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
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

    public function isUserValid(string $userId, string|array $scope): bool
    {
        $query = $this->queryBuilder
            ->from('users')
            ->where('id', $userId)
            ->where('status', '=', 'active');

        $this->addScopeConditions($query, $scope);

        return $query->count() === 1;
    }

    private function addScopeConditions($query, string|array $scope): void
    {
        $isArrayScope = is_array($scope);

        if ($isArrayScope) {
            foreach ($scope as $item) {
                $query->whereNested('scopes', fn($query) => $query->where('scopes.id', $item));
            }
        } else {
            $query->whereNested('scopes', fn($query) => $query->where('scopes.id', $scope));
        }
    }

    public function isClientValid(string $clientId): bool
    {
        return $this->queryBuilder
                ->from('clients')
                ->where('id', $clientId)
                ->where('status', '=', 'active')
                ->count() === 1;
    }

}
