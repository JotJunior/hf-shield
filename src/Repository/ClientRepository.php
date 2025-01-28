<?php

declare(strict_types=1);

namespace Jot\HfOAuth2\Repository;

use League\OAuth2\Server\CryptTrait;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use Jot\HfOAuth2\Entity\ClientEntity;

use function Hyperf\Support\make;
use function password_verify;

class ClientRepository extends AbstractRepository implements ClientRepositoryInterface
{

    use CryptTrait;

    protected string $entity = ClientEntity::class;

    public function getClientEntity(string $clientIdentifier): ?ClientEntityInterface
    {
        $data = $this->find($clientIdentifier);
        return make(ClientEntity::class, ['data' => $data->toArray()]);
    }

    public function validateClient($clientIdentifier, $clientSecret, $grantType): bool
    {
        $client = $this->find($clientIdentifier);
        if (empty($client)) {
            return false;
        }

        return hash_equals($client->getSecret(), hash_hmac('sha256', $clientSecret, $this->config['encryption_key']));
    }
}
