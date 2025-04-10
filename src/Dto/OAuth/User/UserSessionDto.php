<?php

declare(strict_types=1);
/**
 * This file is part of the hf_shield module, a package build for Hyperf framework that is responsible for OAuth2 authentication and access control.
 *
 * @author   Joao Zanon <jot@jot.com.br>
 * @link     https://github.com/JotJunior/hf-shield
 * @license  MIT
 */

namespace Jot\HfShield\Dto\OAuth\User;

use Jot\HfRepository\Entity;

class UserSessionDto extends Entity
{
    protected ?string $email = null;

    protected ?string $federalDocument = null;

    protected ?string $id = null;

    protected ?string $name = null;

    protected ?string $phone = null;

    protected ?string $picture = null;

    protected ?array $scopes = null;

    protected ?string $status = null;
}
