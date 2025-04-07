<?php

declare(strict_types=1);
/**
 * This file is part of the hf_shield module, a package build for Hyperf framework that is responsible for OAuth2 authentication and access control.
 *
 * @author   Joao Zanon <jot@jot.com.br>
 * @link     https://github.com/JotJunior/hf-shield
 * @license  MIT
 */

namespace Jot\HfShield\Repository;

use Jot\HfShield\Entity\RefreshToken\RefreshToken;
use Jot\HfShield\Entity\RefreshTokenEntity;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;

use function Hyperf\Support\make;

class RefreshTokenRepository extends AbstractRepository implements RefreshTokenRepositoryInterface
{
    protected string $entity = RefreshToken::class;

    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity): void
    {
        $entity = make($this->entity, [
            'data' => [
                'id' => $refreshTokenEntity->getIdentifier(),
                'access_token' => [
                    'id' => $refreshTokenEntity->getAccessToken()->getIdentifier(),
                    'expiry_date_time' => $refreshTokenEntity->getAccessToken()->getExpiryDateTime()->format('Y-m-d\TH:i:s.uP'),
                ],
                'expiry_date_time' => $refreshTokenEntity->getExpiryDateTime()->format('Y-m-d\TH:i:s.uP'),
            ],
        ]);

        $r = parent::create($entity);
    }

    public function revokeRefreshToken($tokenId): void
    {
        $this->delete($tokenId);
    }

    public function isRefreshTokenRevoked($tokenId): bool
    {
        return $this->exists($tokenId);
    }

    public function getNewRefreshToken(): ?RefreshTokenEntityInterface
    {
        return make(RefreshTokenEntity::class, []);
    }
}
