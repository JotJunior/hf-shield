<?php

declare(strict_types=1);

namespace Jot\HfOAuth2\Entity;

use Jot\HfRepository\Entity;
use League\OAuth2\Server\Entities\UserEntityInterface;

class UserEntity implements UserEntityInterface
{

    protected ?string $identifier = null;

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function setIdentifier(?string $identifier): UserEntity
    {
        $this->identifier = $identifier;
        return $this;
    }


}
