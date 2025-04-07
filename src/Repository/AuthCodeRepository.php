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

use Jot\HfShield\Entity\AuthCode\AuthCode;
use Jot\HfShield\Entity\AuthCodeEntity;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;

use function Hyperf\Support\make;

class AuthCodeRepository extends AbstractRepository implements AuthCodeRepositoryInterface
{
    protected string $entity = AuthCode::class;

    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity): void
    {
        $authCode = make(AuthCode::class, [
            'id' => $authCodeEntity->getIdentifier(),
            'redirect_uri' => $authCodeEntity->getRedirectUri(),
            'user' => [
                'id' => $authCodeEntity->getUserIdentifier(),
            ],
            'client' => [
                'id' => $authCodeEntity->getClient()->getIdentifier(),
                'name' => $authCodeEntity->getClient()->getName(),
            ],
            'expiry_date_time' => $authCodeEntity->getExpiryDateTime()->format('Y-m-d\TH:i:s.uP'),
        ]);
        $this->create($authCode);
    }

    public function revokeAuthCode($codeId): void
    {
        $this->delete($codeId);
    }

    public function isAuthCodeRevoked($codeId): bool
    {
        return ! $this->exists($codeId);
    }

    public function getNewAuthCode(): AuthCodeEntityInterface
    {
        return make(AuthCodeEntity::class, []);
    }
}
