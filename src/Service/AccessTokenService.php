<?php

declare(strict_types=1);
/**
 * This file is part of the hf_shield module, a package build for Hyperf framework that is responsible for OAuth2 authentication and access control.
 *
 * @author   Joao Zanon <jot@jot.com.br>
 * @link     https://github.com/JotJunior/hf-shield
 * @license  MIT
 */

namespace Jot\HfShield\Service;

use Hyperf\Di\Annotation\Inject;
use Jot\HfShield\Entity\AccessToken\AccessToken as Entity;
use Jot\HfShield\Repository\AccessTokenRepository;

class AccessTokenService
{
    #[Inject]
    protected AccessTokenRepository $repository;

    protected Entity $entity;

    public function userLogList(string $userId): array
    {
        return $this->repository->getUserLogList($userId);
    }
}
