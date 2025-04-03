<?php

declare(strict_types=1);
/**
 * This file is part of hf-shield.
 *
 * @link     https://github.com/JotJunior/hf-shield
 * @contact  hf-shield@jot.com.br
 * @license  MIT
 */

namespace Jot\HfShield\Entity\AuthCode;

use DateTimeInterface;
use Hyperf\Swagger\Annotation as SA;
use Jot\HfRepository\Entity;

#[SA\Schema(schema: 'jot.shield.entity.authcode.access_token')]
class AccessToken extends Entity
{
    #[SA\Property(
        property: 'expiry_date_time',
        type: 'string',
        format: 'string',
        x: ['php_type' => '\\DateTimeImmutable']
    )]
    protected ?DateTimeInterface $expiryDateTime = null;

    #[SA\Property(
        property: 'id',
        type: 'string',
        example: ''
    )]
    protected ?string $id = null;

    #[SA\Property(
        property: 'redirect_uri',
        type: 'string',
        example: ''
    )]
    protected ?string $redirectUri = null;
}
