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

#[SA\Schema(schema: 'jot.shield.entity.user.customer')]
class Customer extends Entity
{
    public const SEARCHABLE = [];

    #[SA\Property(
        property: 'id',
        type: 'string',
        example: ''
    )]
    protected ?string $id = null;

    #[SA\Property(
        property: 'trade_name',
        type: 'string',
        example: ''
    )]
    protected ?string $tradeName = null;

    #[SA\Property(
        property: 'legal_name',
        type: 'string',
        example: ''
    )]
    protected ?string $legalName = null;
}
