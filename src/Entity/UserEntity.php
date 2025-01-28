<?php

declare(strict_types=1);

namespace Jot\HfOAuth2\Entity;

use Jot\HfRepository\Entity;
use Jot\HfValidator\Validator;
use League\OAuth2\Server\Entities\UserEntityInterface;

class UserEntity extends Entity implements UserEntityInterface
{

    protected ?string $name = null;

    #[Validator\Unique(index: 'users', field: 'phone')]
    protected ?string $phone = null;

    #[Validator\Unique(index: 'users', field: 'email')]
    protected ?string $email = null;

    #[Validator\Password(minLength: 10)]
    protected ?string $password = null;
    protected ?string $passwordSalt = null;

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
