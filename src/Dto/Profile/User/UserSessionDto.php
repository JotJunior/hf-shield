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

class UserSessionDto extends Entity
{
    protected ?string $id = null;

    protected ?string $role = 'admin';

    protected ?string $displayName = null;

    protected ?string $photoUrl = null;

    protected ?string $email = null;

    protected ?array $shortcuts = [];

    protected ?object $settings = null;

    protected ?string $loginRedirectUrl = '/';

    protected ?string $status = null;

    protected null|array|string $tags = null;

    #[SA\Property(
        property: 'capacity',
        ref: '#/components/schemas/jot.shield.entity.user.tenant',
        x: ['php_type' => '\Jot\HfShield\Entity\User\Tenant']
    )]
    protected ?Tenant $tenant = null;
}
