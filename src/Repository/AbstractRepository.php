<?php

namespace Jot\HfShield\Repository;

use Hyperf\Contract\ConfigInterface;
use Jot\HfElastic\Contracts\QueryBuilderInterface;
use Jot\HfRepository\Entity\EntityFactoryInterface;
use Jot\HfRepository\Query\QueryParserInterface;
use Jot\HfRepository\Repository;
use League\OAuth2\Server\CryptTrait;
use Psr\Container\ContainerInterface;

class AbstractRepository extends Repository
{
    use CryptTrait;

    protected array $config = [];


    public function __construct(
        protected ContainerInterface     $container,
        protected QueryBuilderInterface  $queryBuilder,
        protected QueryParserInterface   $queryParser,
        protected EntityFactoryInterface $entityFactory
    )
    {
        parent::__construct($queryBuilder, $queryParser, $entityFactory);
        $this->config = $container->get(ConfigInterface::class)->get('hf_shield', []);
        $this->setEncryptionKey($this->config['encryption_key'] ?? null);
    }

    /**
     * Retrieves a list of tenants from the database with specific attributes.
     * @return array Returns an array containing the selected tenant data.
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
     * @param string $tenantId The unique identifier of the tenant whose clients are to be fetched.
     * @return array Returns an array containing the selected client data.
     */
    public function retrieveClientList(string $tenantId): array
    {
        return $this->queryBuilder
            ->select(['id', 'name'])
            ->from('clients')
            ->where('tenant.id', '=', $tenantId)
            ->orderBy('name')
            ->limit(1000)
            ->execute();
    }

    /**
     * Retrieves a list of scopes from the database with specific attributes.
     * @return array Returns an array containing the selected scope data.
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
