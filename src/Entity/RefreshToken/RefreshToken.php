<?php

declare(strict_types=1);
/**
 * This file is part of the hf_shield module, a package build for Hyperf framework that is responsible for OAuth2 authentication and access control.
 *
 * @author   Joao Zanon <jot@jot.com.br>
 * @link     https://github.com/JotJunior/hf-shield
 * @license  MIT
 */

namespace Jot\HfShield\Entity\RefreshToken;

use DateTimeInterface;
use Hyperf\Swagger\Annotation as SA;
use Jot\HfRepository\Entity;
use Jot\HfRepository\Entity\Traits\HasLogicRemovalTrait as HasLogicRemoval;
use Jot\HfRepository\Entity\Traits\HasTimestampsTrait as HasTimestamps;

#[SA\Schema(schema: 'jot.shield.entity.refreshtoken.refresh_token')]
class RefreshToken extends Entity
{
    use HasLogicRemoval;
    use HasTimestamps;

    #[SA\Property(
        property: 'access_token',
        ref: '#/components/schemas/jot.shield.entity.refreshtoken.accesstoken',
        x: ['php_type' => '\Jot\HfShield\Entity\RefreshToken\AccessToken']
    )]
    protected ?AccessToken $accessToken = null;

    #[SA\Property(
        property: 'access_token_identifier',
        type: 'string',
        readOnly: true,
        example: ''
    )]
    protected ?string $accessTokenIdentifier = null;

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
        property: 'refresh_token_identifier',
        type: 'string',
        readOnly: true,
        example: ''
    )]
    protected ?string $refreshTokenIdentifier = null;

    #[SA\Property(
        property: 'updated_at',
        type: 'string',
        format: 'string',
        readOnly: true,
        x: ['php_type' => '\DateTime']
    )]
    protected ?DateTimeInterface $updatedAt = null;
}
