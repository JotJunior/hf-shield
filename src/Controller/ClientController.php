<?php

namespace Jot\HfOAuth2\Controller;

use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\Stringable\Str;
use Jot\HfOAuth2\Entity\ClientEntity;
use Jot\HfOAuth2\Repository\ClientRepository;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

#[Controller]
class ClientController extends AbstractController
{

    public function createClient(): PsrResponseInterface
    {
        $repository = $this->container->get(ClientRepository::class);
        $plainSecret = Str::uuid()->toString();
        $cryptSecret = hash_hmac('sha256', $plainSecret, $this->config['encryption_key']);

        $client = new ClientEntity([
            ...$this->request->all(),
            'secret' => $cryptSecret,
        ]);

        $result = [
            ...$repository->create($client)->toArray(),
            'secret' => $plainSecret,
        ];

        return $this->response->json($result);
    }

}