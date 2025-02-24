<?php

declare(strict_types=1);

namespace Jot\HfShield\Repository;

use Hyperf\Stringable\Str;
use Jot\HfRepository\EntityInterface;
use Jot\HfShield\Entity\Client\Client;
use Jot\HfShield\Entity\ClientEntity;
use League\OAuth2\Server\CryptTrait;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;

class ClientRepository extends AbstractRepository implements ClientRepositoryInterface
{

    private const HASH_ALGORITHM = 'sha256';

    use CryptTrait;

    protected string $entity = Client::class;

    public function getClientEntity(string $clientIdentifier): ?ClientEntityInterface
    {
        /** @var Client $client */
        $client = $this->find($clientIdentifier);

        if (empty($client)) {
            return null;
        }

        $clientData = $client->toArray();

        $clientEntity = new ClientEntity();
        $clientEntity->setIdentifier($clientData['id']);
        $clientEntity->setName($clientData['name']);
        $clientEntity->setRedirectUri($clientData['redirect_uri']);

        return $clientEntity;
    }


    public function validateClient(string $clientIdentifier, ?string $clientSecret, ?string $grantType): bool
    {
        $foundClient = $this->find($clientIdentifier);

        if ($foundClient === null) {
            return false;
        }

        return $this->isClientSecretValid($foundClient->getSecret(), $clientSecret);
    }

    private function isClientSecretValid(?string $storedSecret, string $providedSecret): bool
    {
        if ($storedSecret === null) {
            return false;
        }

        $hashedProvidedSecret = hash_hmac(self::HASH_ALGORITHM, $providedSecret, $this->config['encryption_key']);
        return hash_equals($storedSecret, $hashedProvidedSecret);
    }

    public function createNewClient(EntityInterface $client): array
    {
        $plainSecret = Str::uuid()->toString();
        $secret = $this->createHash($plainSecret, $this->config['encryption_key']);
        $client->setSecret($secret);
        return [$plainSecret, parent::create($client)];

    }

}
