<?php

declare(strict_types=1);
/**
 * This file is part of the hf_shield module, a package build for Hyperf framework that is responsible for OAuth2 authentication and access control.
 *
 * @author   Joao Zanon <jot@jot.com.br>
 * @link     https://github.com/JotJunior/hf-shield
 * @license  MIT
 */

namespace Jot\HfShield\Entity\JwtSignature;

use DateTimeInterface;
use Hyperf\Swagger\Annotation as SA;
use Jot\HfRepository\Entity;
use Jot\HfRepository\Entity\Traits\HasLogicRemovalTrait as HasLogicRemoval;
use Jot\HfRepository\Entity\Traits\HasTimestampsTrait as HasTimestamps;
use Jot\HfValidator\Annotation as V;

#[SA\Schema(schema: 'jot.shield.entity.jwt_signature.jwt_signature')]
class JwtSignature extends Entity
{
    use HasLogicRemoval;
    use HasTimestamps;

    #[SA\Property(
        property: 'created_at',
        type: 'string',
        format: 'string',
        readOnly: true,
        x: ['php_type' => '\DateTime']
    )]
    protected ?DateTimeInterface $createdAt = null;

    #[SA\Property(
        property: 'deleted',
        type: 'boolean',
        readOnly: true,
        example: true
    )]
    protected null|bool|int $deleted = null;

    #[SA\Property(
        property: 'hmac',
        type: 'string',
        readOnly: true,
        example: ''
    )]
    protected ?string $hmac = null;

    #[SA\Property(
        property: 'id',
        type: 'string',
        readOnly: true,
        example: ''
    )]
    protected ?string $id = null;

    #[SA\Property(
        property: 'jwt_signature_identifier',
        description: 'An alias of jwt id',
        type: 'string',
        readOnly: true,
        example: ''
    )]
    protected ?string $jwtSignatureIdentifier = null;

    #[SA\Property(
        property: 'name',
        type: 'string',
        example: ''
    )]
    #[V\Required(onCreate: true, onUpdate: false)]
    protected ?string $name = null;

    #[SA\Property(
        property: 'scopes',
        type: 'array',
        items: new SA\Items(ref: '#/components/schemas/jot.shield.entity.jwt_signature.scope'),
        x: ['php_type' => '\Jot\HfShield\Entity\JwtSignature\Scope[]']
    )]
    #[V\Exists(index: 'scopes', field: 'id')]
    protected ?array $scopes = null;

    #[SA\Property(
        property: 'status',
        type: 'string',
        enum: ['active', 'inactive'],
        example: ''
    )]
    #[V\Required(onCreate: true, onUpdate: false)]
    #[V\Enum(values: ['active', 'inactive'])]
    protected ?string $status = null;

    #[SA\Property(
        property: 'tenant',
        ref: '#/components/schemas/jot.shield.entity.jwt_signature.tenant',
        x: ['php_type' => '\Jot\HfShield\Entity\JwtSignature\Tenant']
    )]
    #[V\Required(onCreate: true, onUpdate: false)]
    #[V\Exists(index: 'tenants', field: 'id')]
    protected ?Tenant $tenant = null;

    #[SA\Property(
        property: 'client',
        ref: '#/components/schemas/jot.shield.entity.jwt_signature.client',
        x: ['php_type' => '\Jot\HfShield\Entity\JwtSignature\Client']
    )]
    #[V\Required(onCreate: true, onUpdate: false)]
    #[V\Exists(index: 'clients', field: 'id')]
    protected ?Client $client = null;

    #[SA\Property(
        property: 'tenant_identifier',
        description: 'An alias of tenant id',
        type: 'string',
        readOnly: true,
        example: ''
    )]
    protected ?string $tenantIdentifier = null;

    #[SA\Property(
        property: 'updated_at',
        type: 'string',
        format: 'string',
        readOnly: true,
        x: ['php_type' => '\DateTime']
    )]
    protected ?DateTimeInterface $updatedAt = null;

    #[SA\Property(
        property: 'user',
        ref: '#/components/schemas/jot.shield.entity.jwt_signature.user',
        x: ['php_type' => '\Jot\HfShield\Entity\JwtSignature\User']
    )]
    #[V\Required(onCreate: true, onUpdate: false)]
    #[V\Exists(index: 'users', field: 'id')]
    protected ?User $user = null;

    #[SA\Property(
        property: 'user_identifier',
        description: 'An alias of user id',
        type: 'string',
        readOnly: true,
        example: ''
    )]
    protected ?string $userIdentifier = null;
}
