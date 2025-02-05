<?php

declare(strict_types=1);

namespace Jot\HfOAuth2\Repository;

use Jot\HfOAuth2\Entity\Tenant\Tenant;

class TenantRepository extends AbstractRepository
{
    protected string $entity = Tenant::class;

}
