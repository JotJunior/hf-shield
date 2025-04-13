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

#[SA\Schema(schema: 'jot.shield.entity.user.tenant')]
class Tenant extends Entity
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
        property: 'scopes',
        type: 'array',
        items: new SA\Items(ref: '#/components/schemas/jot.shield.entity.user.scope'),
        x: ['php_type' => '\Jot\HfShield\Entity\User\Scope[]']
    )]
    protected ?array $scopes = null;

    #[SA\Property(
        property: 'groups',
        type: 'array',
        items: new SA\Items(ref: '#/components/schemas/jot.shield.entity.user.group'),
        x: ['php_type' => '\Jot\HfShield\Entity\User\Group[]']
    )]
    protected ?array $groups = null;
}
