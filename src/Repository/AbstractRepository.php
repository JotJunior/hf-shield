<?php

declare(strict_types=1);
/**
 * This file is part of the hf_shield module, a package build for Hyperf framework that is responsible for OAuth2 authentication and access control.
 *
 * @author   Joao Zanon <jot@jot.com.br>
 * @link     https://github.com/JotJunior/hf-shield
 * @license  MIT
 */

namespace Jot\HfShield\Repository;

use Hyperf\Contract\ConfigInterface;
use Jot\HfRepository\Repository;
use League\OAuth2\Server\CryptTrait;

class AbstractRepository extends Repository
{
    use CryptTrait;

    protected array $config = [];

    public function __construct(ConfigInterface $config)
    {
        parent::__construct();
        $this->config = $config->get('hf_shield', []);
        $this->setEncryptionKey($this->config['encryption_key'] ?? null);
    }

    /**
     * Retrieves a list of tenants from the database with specific attributes.
     * @return array returns an array containing the selected tenant data
     */
    public function retrieveTenantList(): array
    {
        return $this->queryBuilder
            ->select(['id', 'name'])
            ->from('tenants')
            ->orderBy('name')
            ->limit(1000)
            ->execute();
    }

    /**
     * Retrieves a list of clients associated with a specified tenant.
     * @param string $tenantId the unique identifier of the tenant whose clients are to be fetched
     * @return array returns an array containing the selected client data
     */
    public function retrieveClientList(string $tenantId): array
    {
        return $this->queryBuilder
            ->select(['id', 'name'])
            ->from('clients')
            ->where('tenant.id', $tenantId)
            ->orderBy('name')
            ->limit(1000)
            ->execute();
    }

    /**
     * Retrieves a list of scopes from the database with specific attributes.
     * @return array returns an array containing the selected scope data
     */
    public function retrieveScopeList(): array
    {
        return $this->queryBuilder
            ->select(['id', 'name'])
            ->from('scopes')
            ->orderBy('name')
            ->limit(1000)
            ->execute();
    }
}
