<?php

declare(strict_types=1);
/**
 * This file is part of the hf_shield module, a package build for Hyperf framework that is responsible for OAuth2 authentication and access control.
 *
 * @author   Joao Zanon <jot@jot.com.br>
 * @link     https://github.com/JotJunior/hf-shield
 * @license  MIT
 */

namespace Jot\HfShield\Dto\Profile\User;

use Hyperf\Swagger\Annotation as SA;
use Jot\HfRepository\Entity;
use Jot\HfShield\Entity\User\Tenant;
use Jot\HfValidator\Annotation as VA;

class UserPasswordDto extends Entity
{
    public const SEARCHABLE = [];

    protected ?string $id;

    #[VA\Required]
    #[VA\Password]
    protected ?string $password = null;

    protected ?string $currentPassword = null;

    #[SA\Property(
        property: 'capacity',
        ref: '#/components/schemas/jot.shield.entity.user.tenant',
        x: ['php_type' => '\Jot\HfShield\Entity\User\Tenant']
    )]
    protected ?Tenant $tenant = null;

    protected ?string $passwordSalt = null;

    protected null|array|string $tags = null;

    public function getPasswordSalt(): ?string
    {
        return $this->passwordSalt;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }
}
