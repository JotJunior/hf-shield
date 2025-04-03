<?php

declare(strict_types=1);
/**
 * This file is part of hf-shield.
 *
 * @link     https://github.com/JotJunior/hf-shield
 * @contact  hf-shield@jot.com.br
 * @license  MIT
 */

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
            'scopes' => array_map(fn ($scope) => ['id' => $scope->getIdentifier()], $accessTokenEntity->getScopes()),
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
        return ! $this->exists($tokenId);
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

    public function isUserValid(string $userId, array|string $scope): bool
    {
        $query = $this->queryBuilder
            ->from('users')
            ->where('id', $userId)
            ->where('status', 'active');

        $this->addScopeConditions($query, $scope);

        return $query->count() === 1;
    }

    public function isClientValid(string $clientId): bool
    {
        return $this->queryBuilder
            ->from('clients')
            ->where('id', $clientId)
            ->where('status', 'active')
            ->count() === 1;
    }

    private function addScopeConditions($query, array|string $scope): void
    {
        $isArrayScope = is_array($scope);

        if ($isArrayScope) {
            foreach ($scope as $item) {
                $query->whereNested('scopes', fn ($query) => $query->where('scopes.id', $item));
            }
        } else {
            $query->whereNested('scopes', fn ($query) => $query->where('scopes.id', $scope));
        }
    }
}
