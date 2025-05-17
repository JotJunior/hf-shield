<?php

declare(strict_types=1);
/**
 * This file is part of the hf_shield module, a package build for Hyperf framework that is responsible for OAuth2 authentication and access control.
 *
 * @author   Joao Zanon <jot@jot.com.br>
 * @link     https://github.com/JotJunior/hf-shield
 * @license  MIT
 */

namespace Jot\HfShield\Entity\BasicOption;

use DateTimeInterface;
use Hyperf\Swagger\Annotation as SA;
use Jot\HfRepository\Entity;
use Jot\HfRepository\Entity\Traits\HasLogicRemovalTrait as HasLogicRemoval;
use Jot\HfRepository\Entity\Traits\HasTimestampsTrait as HasTimestamps;
use Jot\HfValidator\Annotation as VA;

#[SA\Schema(schema: 'jot.shield.entity.basic_option.basic_option')]
class BasicOption extends Entity
{
    use HasLogicRemoval;
    use HasTimestamps;

    #[SA\Property(
        property: 'basic_options_id',
        description: 'An alias of basic id',
        type: 'string',
        readOnly: true,
        example: ''
    )]
    protected ?string $basicOptionsId = null;

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
        property: 'description',
        type: 'string',
        example: ''
    )]
    protected ?string $description = null;

    #[SA\Property(
        property: 'domain',
        type: 'string',
        example: ''
    )]
    protected ?string $domain = null;

    #[SA\Property(
        property: 'icon',
        type: 'string',
        example: ''
    )]
    protected ?string $icon = null;

    #[SA\Property(
        property: 'id',
        type: 'string',
        readOnly: true,
        example: ''
    )]
    protected ?string $id = null;

    #[SA\Property(
        property: 'name',
        type: 'string',
        example: ''
    )]
    #[VA\Required]
    protected ?string $name = null;

    #[SA\Property(
        property: 'options',
        type: 'array',
        items: new SA\Items(ref: '#/components/schemas/jot.shield.entity.basic_option.option'),
        x: ['php_type' => '\Jot\HfShield\Entity\BasicOption\Option[]']
    )]
    protected ?array $options = null;

    #[SA\Property(
        property: 'parent_id',
        description: 'An alias of parent id',
        type: 'string',
        readOnly: true,
        example: ''
    )]
    protected ?string $parentId = null;

    #[SA\Property(
        property: 'status',
        type: 'string',
        example: ''
    )]
    #[VA\Required]
    protected ?string $status = null;

    #[SA\Property(
        property: 'updated_at',
        type: 'string',
        format: 'date-time',
        x: ['php_type' => '\DateTime']
    )]
    protected ?DateTimeInterface $updatedAt = null;
}
