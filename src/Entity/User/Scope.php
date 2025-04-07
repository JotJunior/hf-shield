<?php

declare(strict_types=1);
/**
 * This file is part of the hf_shield module, a package build for Hyperf framework that is responsible for OAuth2 authentication and access control.
 *
 * @author   Joao Zanon <jot@jot.com.br>
 * @link     https://github.com/JotJunior/hf-shield
 * @license  MIT
 */

namespace Jot\HfShield\Entity\User;

use Hyperf\Swagger\Annotation as SA;
use Jot\HfRepository\Entity;

#[SA\Schema(schema: 'jot.hf-shield.entity.user.scope')]
class Scope extends Entity
{
    #[SA\Property(
        property: 'id',
        type: 'string',
        example: ''
    )]
    protected ?string $id = null;

    #[SA\Property(
        property: 'name',
        type: 'string',
        example: ''
    )]
    protected ?string $name = null;

    #[SA\Property(
        property: 'tenant_identifier',
        description: 'An alias of tenant id',
        type: 'string',
        readOnly: true,
        example: ''
    )]
    protected ?string $tenantIdentifier = null;
}
