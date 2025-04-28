<?php

declare(strict_types=1);
/**
 * This file is part of the hf_shield module, a package build for Hyperf framework that is responsible for OAuth2 authentication and access control.
 *
 * @author   Joao Zanon <jot@jot.com.br>
 * @link     https://github.com/JotJunior/hf-shield
 * @license  MIT
 */

namespace Jot\HfShield\Entity\UserChallenge;

use DateTimeInterface;
use Hyperf\Swagger\Annotation as SA;
use Jot\HfRepository\Entity;
use Jot\HfRepository\Entity\Traits\HasLogicRemovalTrait as HasLogicRemoval;
use Jot\HfRepository\Entity\Traits\HasTimestampsTrait as HasTimestamps;

#[SA\Schema(schema: 'jot.shield.entity.user_challenge.user_challenge')]
class UserChallenge extends Entity
{
    use HasLogicRemoval;
    use HasTimestamps;

    public const SEARCHABLE = [];

    #[SA\Property(
        property: 'challenge',
        type: 'string',
        example: ''
    )]
    protected ?string $challenge = null;

    #[SA\Property(
        property: 'created_at',
        type: 'string',
        format: 'date-time',
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
        property: 'id',
        type: 'string',
        readOnly: true,
        example: ''
    )]
    protected ?string $id = null;

    #[SA\Property(
        property: 'status',
        type: 'string',
        example: ''
    )]
    protected ?string $status = null;

    #[SA\Property(
        property: 'content',
        type: 'string',
        example: ''
    )]
    protected ?string $content = null;

    #[SA\Property(
        property: 'updated_at',
        type: 'string',
        format: 'date-time',
        x: ['php_type' => '\DateTime']
    )]
    protected ?DateTimeInterface $updatedAt = null;

    #[SA\Property(
        property: 'user',
        ref: '#/components/schemas/jot.shield.entity.user_challenge.user',
        x: ['php_type' => '\Jot\HfShield\Entity\UserChallenge\User']
    )]
    protected ?User $user = null;

    #[SA\Property(
        property: 'user_auth_webauthn_challenge_id',
        description: 'An alias of user id',
        type: 'string',
        readOnly: true,
        example: ''
    )]
    protected ?string $userAuthWebauthnChallengeId = null;

    #[SA\Property(
        property: 'user_id',
        description: 'An alias of user id',
        type: 'string',
        readOnly: true,
        example: ''
    )]
    protected ?string $userId = null;
}
