<?php

declare(strict_types=1);
/**
 * This file is part of the hf_shield module, a package build for Hyperf framework that is responsible for OAuth2 authentication and access control.
 *
 * @author   Joao Zanon <jot@jot.com.br>
 * @link     https://github.com/JotJunior/hf-shield
 * @license  MIT
 */

namespace Jot\HfShield\Controller;

use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\RateLimit\Annotation\RateLimit;
use Hyperf\Swagger\Annotation as SA;
use Jot\HfShield\Annotation\Scope;
use Jot\HfShield\Entity\Client\Client;
use Jot\HfShield\Middleware\BearerStrategy;
use Jot\HfShield\Repository\ClientRepository;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

use function Hyperf\Support\make;

#[SA\HyperfServer('http')]
#[SA\Tag(
    name: 'Client',
    description: 'Endpoints related to clients management'
)]
#[Controller(prefix: '/oauth')]
class ClientController extends AbstractController
{
    protected string $repository = ClientRepository::class;

    #[SA\Post(
        path: '/oauth/clients',
        description: 'Create a new client.',
        summary: 'Create a New Client',
        security: [
            ['shieldBearerAuth' => ['oauth:client:create']],
        ],
        requestBody: new SA\RequestBody(
            required: true,
            content: new SA\JsonContent(ref: '#/components/schemas/jot.shield.entity.client.client')
        ),
        tags: ['Client'],
        responses: [
            new SA\Response(
                response: 201,
                description: 'Client created',
                content: new SA\JsonContent(ref: '#/components/schemas/jot.shield.entity.client.client')
            ),
            new SA\Response(
                response: 400,
                description: 'Bad request',
                content: new SA\JsonContent(ref: '#/components/schemas/jot.hf-shield.error.response')
            ),
            new SA\Response(
                response: 401,
                description: 'Unauthorized access',
                content: new SA\JsonContent(ref: '#/components/schemas/jot.hf-shield.auth-error.response')
            ),
            new SA\Response(
                response: 500,
                description: 'Application error',
                content: new SA\JsonContent(ref: '#/components/schemas/jot.hf-shield.error.response')
            ),
        ]
    )]
    #[RateLimit(create: 1, capacity: 2)]
    #[Scope(allow: 'oauth:client:update')]
    #[Middleware(BearerStrategy::class)]
    public function createClient(): PsrResponseInterface
    {
        $userData = $this->request->all();
        $client = make(Client::class, ['data' => $userData]);
        return $this->saveClient($client);
    }

    #[SA\Get(
        path: '/oauth/clients',
        description: 'Retrieve a list of clients with optional pagination.',
        summary: 'Get Clients List',
        security: [
            ['shieldBearerAuth' => ['oauth:client:list']],
        ],
        tags: ['Client'],
        parameters: [
            new SA\Parameter(
                name: '_page',
                description: 'Page number for pagination',
                in: 'query',
                required: false,
                schema: new SA\Schema(type: 'integer', example: 1)
            ),
            new SA\Parameter(
                name: '_per_page',
                description: 'Number of results per page',
                in: 'query',
                required: false,
                schema: new SA\Schema(type: 'integer', example: 10)
            ),
            new SA\Parameter(
                name: '_sort',
                description: 'Sort results by a specific fields',
                in: 'query',
                required: false,
                schema: new SA\Schema(type: 'string', example: 'created_at:desc,updated_at:desc')
            ),
            new SA\Parameter(
                name: '_fields',
                description: 'Fields to include in the response',
                in: 'query',
                required: false,
                schema: new SA\Schema(type: 'string', example: 'id,created_at,updated_at')
            ),
        ],
        responses: [
            new SA\Response(
                response: 200,
                description: 'Client details retrieved successfully',
                content: new SA\JsonContent(
                    properties: [
                        new SA\Property(
                            property: 'data',
                            type: 'array',
                            items: new SA\Items(ref: '#/components/schemas/jot.shield.entity.client.client')
                        ),
                        new SA\Property(
                            property: 'result',
                            type: 'string',
                            example: 'success'
                        ),
                        new SA\Property(
                            property: 'error',
                            type: 'string',
                            example: null,
                            nullable: true
                        ),
                    ],
                    type: 'object'
                )
            ),
            new SA\Response(
                response: 400,
                description: 'Bad Request',
                content: new SA\JsonContent(ref: '#/components/schemas/jot.hf-shield.error.response')
            ),
            new SA\Response(
                response: 401,
                description: 'Unauthorized access',
                content: new SA\JsonContent(ref: '#/components/schemas/jot.hf-shield.auth-error.response')
            ),
            new SA\Response(
                response: 500,
                description: 'Application error',
                content: new SA\JsonContent(ref: '#/components/schemas/jot.hf-shield.error.response')
            ),
        ]
    )]
    #[RateLimit(create: 1, capacity: 2)]
    #[Scope(allow: 'oauth:client:list')]
    #[Middleware(BearerStrategy::class)]
    public function listClients(): PsrResponseInterface
    {
        $repository = $this->container->get(ClientRepository::class);
        $clients = $repository->paginate([]);
        return $this->response->json($clients);
    }

    private function saveClient(Client $client): PsrResponseInterface
    {
        [$plainSecret, $result] = $this->repository()->createNewClient($client);
        $clientData = $result
            ->hide(['secret'])
            ->toArray();
        $clientData['secret'] = $plainSecret;
        return $this->response->json($clientData);
    }
}
