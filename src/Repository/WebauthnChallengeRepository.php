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
use Jot\HfShield\Entity\UserWebauthnChallenge\UserWebauthnChallenge;

class WebauthnChallengeRepository extends AbstractRepository
{
    protected string $index = 'user_webauthn_challenges';

    protected string $entity = UserWebauthnChallenge::class;

    public function findActiveByUserId(string $userId): ?array
    {
        return $this->first([
            'user.id' => $userId,
            'status' => 'pending',
        ])->toArray();
    }

    public function complete(string $id): bool
    {
        $challenge = $this->findById($id);
        if (! $challenge) {
            return false;
        }
        $challenge->hydrate(['status' => 'completed']);
        return $this->update($challenge) instanceof EntityInterface;
    }

    public function findById(string $id): ?EntityInterface
    {
        return $this->find($id);
    }
}
