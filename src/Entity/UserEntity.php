<?php

declare(strict_types=1);
/**
 * This file is part of hf-shield.
 *
 * @link     https://github.com/JotJunior/hf-shield
 * @contact  hf-shield@jot.com.br
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
