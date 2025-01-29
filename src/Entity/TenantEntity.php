<?php

declare(strict_types=1);

namespace Jot\HfOAuth2\Entity;

use Jot\HfRepository\Entity;
use Jot\HfValidator\Validator;
use League\OAuth2\Server\Entities\UserEntityInterface;

class TenantEntity extends Entity implements UserEntityInterface
{

    #[Validator\Unique(index: 'tenants', field: 'name')]
    protected ?string $name = null;
    #[Validator\Unique(index: 'tenants', field: 'domains')]
    protected ?array $domains = null;
    protected ?array $ips = null;

    public function getIdentifier(): string
    {
        return $this->id;
    }

}
