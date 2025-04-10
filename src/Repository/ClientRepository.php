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

use Hyperf\Stringable\Str;
use Jot\HfRepository\Entity\EntityInterface;
use Jot\HfRepository\Entity\Traits\HashableTrait;
use Jot\HfShield\Entity\Client\Client;
use Jot\HfShield\Entity\ClientEntity;
use League\OAuth2\Server\CryptTrait;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;

class ClientRepository extends AbstractRepository implements ClientRepositoryInterface
{
    use CryptTrait;
    use HashableTrait;

    private const HASH_ALGORITHM = 'sha256';

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
        $clientEntity->setTenantId($clientData['tenant']['id']);

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

    public function createNewClient(EntityInterface $client): array
    {
        $plainSecret = Str::uuid()->toString();
        $this->createHash('secret', $plainSecret, $this->config['encryption_key']);
        return [$plainSecret, parent::create($client)];
    }

    private function isClientSecretValid(?string $storedSecret, string $providedSecret): bool
    {
        if ($storedSecret === null) {
            return false;
        }

        $hashedProvidedSecret = hash_hmac(self::HASH_ALGORITHM, $providedSecret, $this->config['encryption_key']);
        return hash_equals($storedSecret, $hashedProvidedSecret);
    }
}
