<?php

declare(strict_types=1);
/**
 * This file is part of the hf_shield module, a package build for Hyperf framework that is responsible for OAuth2 authentication and access control.
 *
 * @author   Joao Zanon <jot@jot.com.br>
 * @link     https://github.com/JotJunior/hf-shield
 * @license  MIT
 */

namespace Jot\HfShield\Dto\OAuth\User;

use Hyperf\Swagger\Annotation as SA;
use Jot\HfRepository\Entity;
use Jot\HfShield\Entity\User\Tenant;

class UserSessionDto extends Entity
{
    protected ?string $id = null;

    protected ?string $name = null;

    protected ?string $email = null;

    protected ?string $federalDocument = null;

    protected ?string $phone = null;

    protected ?string $picture = null;

    protected ?array $scopes = null;

    protected ?string $status = null;

    protected mixed $customSettings = null;

    #[SA\Property(
        property: 'tenant',
        ref: '#/components/schemas/jot.shield.entity.access_token.tenant',
        description: 'The user main tenant',
        x: ['php_type' => '\Jot\HfShield\Entity\User\Tenant']
    )]
    protected ?Tenant $tenant = null;
}
