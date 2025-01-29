<?php

namespace Jot\HfOAuth2\Controller;

use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\Stringable\Str;
use Jot\HfOAuth2\Entity\ClientEntity;
use Jot\HfOAuth2\Repository\ClientRepository;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use function Hyperf\Support\make;

#[Controller]
class ClientController extends AbstractController
{

    protected string $repository = ClientRepository::class;

    /**
     * Creates a new client, validates the client data, and saves the client if validation passes.
     *
     * @return PsrResponseInterface Returns a response with a status code of 400 and validation errors if the client data is invalid,
     *                               or a response with the saved client details if the process is successful.
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function createClient(): PsrResponseInterface
    {
        [$plainSecret, $client] = $this->createNewClient();

        if (!$client->validate()) {
            $validationErrors = $client->getErrors();
            return $this->response->withStatus(400)->json($validationErrors);
        }

        return $this->saveClient($client, $plainSecret);
    }

    /**
     * Generates a new client secret, creates a client entity with the provided data, and returns the secret and client entity.
     *
     * @return array Returns an array consisting of the newly generated plain secret and the client entity.
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
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

    /**
     * Saves a new client record and returns the client data along with its secret.
     *
     * @param ClientEntity $client The client entity to be saved.
     * @param string $plainSecret The plain text secret associated with the client.
     *
     * @return PsrResponseInterface The response containing the created client data and secret,
     *                              or an error message if an exception occurs.
     */
    private function saveClient(ClientEntity $client, string $plainSecret): PsrResponseInterface
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

    /**
     * Encrypts the provided secret using a secure HMAC-SHA256 algorithm.
     *
     * @param string $secret The secret string to be encrypted.
     * @return string The resulting HMAC-SHA256 encrypted string.
     */
    private function encryptSecret(string $secret): string
    {
        return hash_hmac('sha256', $secret, $this->config['encryption_key']);
    }

}