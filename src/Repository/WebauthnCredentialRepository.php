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

use Jot\HfRepository\Entity\EntityInterface;
use Jot\HfShield\Entity\UserWebauthnCredential\UserWebauthnCredential;

class WebauthnCredentialRepository extends AbstractRepository
{
    protected string $index = 'user_webauthn_credentials';

    protected string $entity = UserWebauthnCredential::class;

    public function findByUserId(string $userId): array
    {
        return parent::first(['user_id' => $userId, 'status' => 'active'])->hide(['password', 'password_salt'])->toArray();
    }

    public function findById(string $id): ?EntityInterface
    {
        return $this->find($id);
    }
}
