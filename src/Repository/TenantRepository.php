<?php

declare(strict_types=1);
/**
 * This file is part of hf-shield.
 *
 * @link     https://github.com/JotJunior/hf-shield
 * @contact  hf-shield@jot.com.br
 * @license  MIT
 */

namespace Jot\HfShield\Repository;

use Jot\HfShield\Entity\Tenant\Tenant;

class TenantRepository extends AbstractRepository
{
    protected string $entity = Tenant::class;
}
