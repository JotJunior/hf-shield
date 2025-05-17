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

use Hyperf\Swagger\Annotation as SA;
use Jot\HfRepository\Entity;

#[SA\Schema(schema: 'jot.shield.entity.basic_option.option')]
class Option extends Entity
{
    #[SA\Property(
        property: 'extra',
        type: 'string',
        example: ''
    )]
    protected ?string $extra = null;

    #[SA\Property(
        property: 'key',
        type: 'string',
        example: ''
    )]
    protected ?string $key = null;

    #[SA\Property(
        property: 'translation_key',
        type: 'string',
        example: ''
    )]
    protected ?string $translationKey = null;

    #[SA\Property(
        property: 'value',
        type: 'string',
        example: ''
    )]
    protected ?string $value = null;
}
