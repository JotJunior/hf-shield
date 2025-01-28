<?php

declare(strict_types=1);

namespace Jot\HfOAuth2\Repository;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use Jot\HfOAuth2\Entity\AccessTokenEntity;
use function Hyperf\Support\make;

class AccessTokenRepository extends AbstractRepository implements AccessTokenRepositoryInterface
{
    protected string $entity = AccessTokenEntity::class;

    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity): void
    {
        $this->create($accessTokenEntity);
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
        return make(AccessTokenEntity::class, [
            'user' => $userIdentifier ? ['id' => $userIdentifier] : null,
            'client' => $clientEntity->toArray(),
            'scopes' => $scopes,
        ]);
    }
}
