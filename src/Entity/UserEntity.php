<?php

declare(strict_types=1);
/**
 * This file is part of the hf_shield module, a package build for Hyperf framework that is responsible for OAuth2 authentication and access control.
 *
 * @author   Joao Zanon <jot@jot.com.br>
 * @link     https://github.com/JotJunior/hf-shield
 * @license  MIT
 */

namespace Jot\HfShield\Entity;

use League\OAuth2\Server\Entities\UserEntityInterface;

class UserEntity implements UserEntityInterface
{
    protected ?string $identifier = null;

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function setIdentifier(?string $identifier): UserEntity
    {
        $this->identifier = $identifier;
        return $this;
    }
}
