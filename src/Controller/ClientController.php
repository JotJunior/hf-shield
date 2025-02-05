<?php

namespace Jot\HfShield\Controller;

use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\RateLimit\Annotation\RateLimit;
use Hyperf\Stringable\Str;
use Jot\HfShield\Annotation\Scope;
use Jot\HfShield\Entity\ClientEntity;
use Jot\HfShield\Middleware\CheckCredentials;
use Jot\HfShield\Repository\ClientRepository;
use Jot\HfRepository\EntityInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use function Hyperf\Support\make;

#[Controller(prefix: '/oauth')]
class ClientController extends AbstractController
{

    protected string $repository = ClientRepository::class;

    #[PostMapping(path: 'clients')]
    #[RateLimit(create: 1, capacity: 2)]
    #[Scope(allow: 'oauth:client:create')]
    #[Middleware(CheckCredentials::class)]
    public function createClient(): PsrResponseInterface
    {
        [$plainSecret, $client] = $this->createNewClient();

        if (!$client->validate()) {
            $validationErrors = $client->getErrors();
            return $this->response->withStatus(400)->json($validationErrors);
        }

        return $this->saveClient($client, $plainSecret);
    }

    #[GetMapping(path: 'clients')]
    #[RateLimit(create: 1, capacity: 2)]
    #[Scope(allow: 'oauth:client:list')]
    #[Middleware(CheckCredentials::class)]
    public function listClients(): PsrResponseInterface
    {
        $repository = $this->container->get(ClientRepository::class);
        $clients = $repository->paginate([]);
        return $this->response->json($clients);
    }

    private function createNewClient(): array
    {
        $repository = $this->container->get(ClientRepository::class);

        $plainSecret = Str::uuid()->toString();
        $encryptedSecret = $this->encryptSecret($plainSecret);

        $clientData = [
            ...$this->request->all(),
            'secret' => $encryptedSecret,
        ];
        $client = make(ClientEntity::class, $clientData);

        return [$plainSecret, $client];
    }

    private function saveClient(EntityInterface $client, string $plainSecret): PsrResponseInterface
    {
        $repository = $this->container->get(ClientRepository::class);

        try {
            $createdClient = $repository->create($client);
            $responseData = [
                ...$createdClient->toArray(),
                'secret' => $plainSecret,
            ];
            return $this->response->json($responseData);
        } catch (\Throwable $e) {
            $errorResponse = ['error' => $e->getMessage()];
            return $this->response->withStatus(500)->json($errorResponse);
        }
    }

    private function encryptSecret(string $secret): string
    {
        return hash_hmac('sha256', $secret, $this->config['encryption_key']);
    }

}