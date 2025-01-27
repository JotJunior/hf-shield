<?php

/**
 * @author      Alex Bilbie <hello@alexbilbie.com>
 * @copyright   Copyright (c) Alex Bilbie
 * @license     http://mit-license.org/
 *
 * @link        https://github.com/thephpleague/oauth2-server
 */

declare(strict_types=1);

namespace Jot\HfOAuth2\Entity;

use Jot\HfRepository\Entity;
use League\OAuth2\Server\Entities\UserEntityInterface;

class UserEntity extends Entity implements UserEntityInterface
{

    private ?string $password = null;
    private ?string $passwordSalt = null;

    public function getIdentifier(): string
    {
        return $this->id;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getPasswordSalt(): ?string
    {
        return $this->passwordSalt;
    }


}
