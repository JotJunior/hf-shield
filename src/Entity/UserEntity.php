<?php

declare(strict_types=1);

namespace Jot\HfOAuth2\Entity;

use Jot\HfRepository\Entity;
use Jot\HfValidator\Validator;
use League\OAuth2\Server\Entities\UserEntityInterface;

class UserEntity extends Entity implements UserEntityInterface
{

    #[Validator\Required]
    protected ?string $name = null;

    #[Validator\Unique(index: 'users', field: 'phone')]
    #[Validator\Required]
    protected ?string $phone = null;

    #[Validator\Unique(index: 'users', field: 'email')]
    #[Validator\Email(checkDomain: false)]
    #[Validator\Required(skipUpdates: false)]
    protected ?string $email = null;

    #[Validator\Required]
    #[Validator\Password(requireLower: true, requireUpper: true, requireNumber: true, requireSpecial: true, minLength: 8)]
    protected ?string $password = null;
    protected ?string $passwordSalt = null;
    #[Validator\Exists(index: 'clients', field: 'id')]
    protected ?ClientEntity $client = null;

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
