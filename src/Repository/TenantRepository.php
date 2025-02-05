<?php

declare(strict_types=1);

namespace Jot\HfShield\Repository;

use Jot\HfShield\Entity\Tenant\Tenant;

class TenantRepository extends AbstractRepository
{
    protected string $entity = Tenant::class;

}
