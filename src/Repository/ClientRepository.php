<?php

/**
 * @author      Alex Bilbie <hello@alexbilbie.com>
 * @copyright   Copyright (c) Alex Bilbie
 * @license     http://mit-license.org/
 *
 * @link        https://github.com/thephpleague/oauth2-server
 */

declare(strict_types=1);

namespace Jot\HfOAuth2\Repository;

use Jot\HfRepository\Repository;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use Jot\HfOAuth2\Entity\ClientEntity;

use function array_key_exists;
use function password_hash;
use function password_verify;

class ClientRepository extends Repository implements ClientRepositoryInterface
{

    protected string $entity = ClientEntity::class;

    /**
     * {@inheritdoc}
     */
    public function getClientEntity(string $clientIdentifier): ?ClientEntityInterface
    {
        $data = $this->find($clientIdentifier);
        return new ClientEntity($data->toArray());
    }

    /**
     * {@inheritdoc}
     */
    public function validateClient($clientIdentifier, $clientSecret, $grantType): bool
    {
        $client = $this->find($clientIdentifier);
        if (empty($client)) {
            return false;
        }

        return password_verify($clientSecret, $client->getSecret());
    }
}
