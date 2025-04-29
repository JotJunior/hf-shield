<?php

declare(strict_types=1);
/**
 * This file is part of the hf_shield module, a package build for Hyperf framework that is responsible for OAuth2 authentication and access control.
 *
 * @author   Joao Zanon <jot@jot.com.br>
 * @link     https://github.com/JotJunior/hf-shield
 * @license  MIT
 */

namespace Jot\HfShield\Entity\AccessToken;

use DateTimeInterface;
use Hyperf\Swagger\Annotation as SA;
use Jot\HfRepository\Entity;
use Jot\HfRepository\Entity\Traits\HasLogicRemovalTrait as HasLogicRemoval;
use Jot\HfRepository\Entity\Traits\HasTimestampsTrait as HasTimestamps;

#[SA\Schema(schema: 'jot.shield.entity.access_token.access_token')]
class AccessToken extends Entity
{
    use HasLogicRemoval;
    use HasTimestamps;

    #[SA\Property(
        property: 'access_token_identifier',
        type: 'string',
        readOnly: true,
        example: ''
    )]
    protected ?string $accessTokenIdentifier = null;

    #[SA\Property(
        property: 'client',
        ref: '#/components/schemas/jot.shield.entity.access_token.client',
        x: ['php_type' => '\Jot\HfShield\Entity\AccessToken\Client']
    )]
    protected ?Client $client = null;

    #[SA\Property(
        property: 'confidential',
        type: 'boolean',
        example: true
    )]
    protected ?bool $confidential = null;

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
        property: 'expiry_date_time',
        type: 'string',
        format: 'string',
        x: ['php_type' => '\DateTimeImmutable']
    )]
    protected ?DateTimeInterface $expiryDateTime = null;

    #[SA\Property(
        property: 'id',
        type: 'string',
        readOnly: true,
        example: ''
    )]
    protected ?string $id = null;

    #[SA\Property(
        property: 'scopes',
        type: 'array',
        items: new SA\Items(ref: '#/components/schemas/jot.shield.entity.access_token.scope'),
        x: ['php_type' => '\Jot\HfShield\Entity\AccessToken\Scope[]']
    )]
    protected ?array $scopes = null;

    #[SA\Property(
        property: 'metadata',
        type: 'array',
        items: new SA\Items(ref: '#/components/schemas/jot.shield.entity.access_token.metadata'),
        x: ['php_type' => '\Jot\HfShield\Entity\AccessToken\Metadata[]']
    )]
    protected ?array $metadata = null;

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
        ref: '#/components/schemas/jot.shield.entity.access_token.user',
        x: ['php_type' => '\Jot\HfShield\Entity\AccessToken\User']
    )]
    protected ?User $user = null;

    #[SA\Property(
        property: 'tenant',
        ref: '#/components/schemas/jot.shield.entity.access_token.tenant',
        x: ['php_type' => '\Jot\HfShield\Entity\AccessToken\Tenant']
    )]
    protected ?Tenant $tenant = null;
}
