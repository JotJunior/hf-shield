<?php

declare(strict_types=1);
/**
 * This file is part of hf-shield.
 *
 * @link     https://github.com/JotJunior/hf-shield
 * @contact  hf-shield@jot.com.br
 * @license  MIT
 */

namespace Jot\HfShield\Controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\RateLimit\Annotation\RateLimit;
use Hyperf\Swagger\Annotation as SA;
use Jot\HfShield\Annotation\Scope;
use Jot\HfShield\Entity\JwtSignature\JwtSignature;
use Jot\HfShield\Middleware\BearerStrategy;
use Jot\HfShield\Repository\JwtSignatureRepository;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

use function Hyperf\Support\make;

#[SA\HyperfServer('http')]
#[SA\Tag(
    name: 'JwtSignature',
    description: 'Endpoints related to jwt_signatures management'
)]
#[Controller(prefix: '/jwt')]
class JwtSignatureController extends AbstractController
{
    #[Inject]
    protected string $repository = JwtSignatureRepository::class;

    #[SA\Get(
        path: '/jwt/signatures',
        description: 'Retrieve a list of jwt_signatures with optional pagination.',
        summary: 'Get JwtSignatures List',
        security: [
            ['shieldBearerAuth' => ['oauth:jwt_signature:list']],
        ],
        tags: ['JwtSignature'],
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
                description: 'JwtSignature details retrieved successfully',
                content: new SA\JsonContent(
                    properties: [
                        new SA\Property(
                            property: 'data',
                            type: 'array',
                            items: new SA\Items(ref: '#/components/schemas/jot.hf-shield.entity.jwt_signature.jwt_signature')
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
    #[Middleware(BearerStrategy::class)]
    #[Scope(allow: 'oauth:jwt_signature:list')]
    public function getJwtSignaturesList(): PsrResponseInterface
    {
        $result = $this->repository->paginate($this->request->query());
        if ($result['result'] === 'error') {
            return $this->response->withStatus(400)->json($result);
        }
        return $this->response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->json($result);
    }

    #[SA\Get(
        path: '/jwt/signatures/{id}',
        description: 'Retrieve the details of a specific jwt_signatures identified by ID.',
        summary: 'Get JwtSignature Data',
        security: [
            ['shieldBearerAuth' => ['oauth:jwt_signature:read']],
        ],
        tags: ['JwtSignature'],
        parameters: [
            new SA\Parameter(
                name: 'id',
                description: 'Unique identifier of the jwt_signatures',
                in: 'path',
                required: true,
                schema: new SA\Schema(type: 'string', example: '12345')
            ),
        ],
        responses: [
            new SA\Response(
                response: 200,
                description: 'JwtSignature details retrieved successfully',
                content: new SA\JsonContent(
                    properties: [
                        new SA\Property(
                            property: 'data',
                            ref: '#/components/schemas/jot.hf-shield.entity.jwt_signature.jwt_signature'
                        ),
                        new SA\Property(
                            property: 'result',
                            type: 'string',
                            example: 'success'
                        ),
                        new SA\Property(
                            property: 'error',
                            type: 'string',
                            example: 'Invalid request parameters',
                            nullable: true
                        ),
                    ],
                    type: 'object'
                )
            ),
            new SA\Response(
                response: 400,
                description: 'Server Error',
                content: new SA\JsonContent(ref: '#/components/schemas/jot.hf-shield.error.response')
            ),
            new SA\Response(
                response: 401,
                description: 'Unauthorized access',
                content: new SA\JsonContent(ref: '#/components/schemas/jot.hf-shield.auth-error.response')
            ),
            new SA\Response(
                response: 404,
                description: 'JwtSignature not Found',
                content: new SA\JsonContent(ref: '#/components/schemas/jot.hf-shield.error.response')
            ),
            new SA\Response(
                response: 500,
                description: 'Application error',
                content: new SA\JsonContent(ref: '#/components/schemas/jot.hf-shield.error.response')
            ),
        ]
    )]
    #[Middleware(BearerStrategy::class)]
    #[Scope(allow: 'oauth:jwt_signature:read')]
    #[RateLimit(create: 1, capacity: 2)]
    public function getJwtSignatureData(string $id): PsrResponseInterface
    {
        $entity = $this->repository->find($id);

        if (empty($entity)) {
            return $this->response->withStatus(404)->json([
                'data' => null,
                'result' => 'not-found',
                'error' => 'Document not found',
            ]);
        }

        return $this->response->json([
            'data' => $entity->toArray(),
            'result' => 'success',
            'error' => null,
        ]);
    }

    #[SA\Post(
        path: '/jwt/signatures',
        description: 'Create a new jwt_signatures.',
        summary: 'Create a New JwtSignature',
        security: [
            ['shieldBearerAuth' => ['oauth:jwt_signature:create']],
        ],
        requestBody: new SA\RequestBody(
            required: true,
            content: new SA\JsonContent(ref: '#/components/schemas/jot.hf-shield.entity.jwt_signature.jwt_signature')
        ),
        tags: ['JwtSignature'],
        responses: [
            new SA\Response(
                response: 201,
                description: 'JwtSignature created',
                content: new SA\JsonContent(ref: '#/components/schemas/jot.hf-shield.entity.jwt_signature.jwt_signature')
            ),
            new SA\Response(
                response: 401,
                description: 'Unauthorized access',
                content: new SA\JsonContent(ref: '#/components/schemas/jot.hf-shield.auth-error.response')
            ),
            new SA\Response(
                response: 400,
                description: 'Bad request',
                content: new SA\JsonContent(ref: '#/components/schemas/jot.hf-shield.error.response')
            ),
            new SA\Response(
                response: 500,
                description: 'Application error',
                content: new SA\JsonContent(ref: '#/components/schemas/jot.hf-shield.error.response')
            ),
        ]
    )]
    #[RateLimit(create: 1, capacity: 2)]
    #[Middleware(BearerStrategy::class)]
    #[Scope(allow: 'oauth:jwt_signature:create')]
    public function createJwtSignature(): PsrResponseInterface
    {
        $entity = make(JwtSignature::class, ['data' => $this->request->all()]);
        $result = $this->repository->create($entity);

        return $this->response->withStatus(201)->json($result->toArray());
    }

    #[SA\Delete(
        path: '/jwt/signatures/{id}',
        description: 'Delete an existing jwt_signatures by its unique identifier.',
        summary: 'Delete an existing JwtSignature',
        security: [
            ['shieldBearerAuth' => ['oauth:jwt_signature:delete']],
        ],
        tags: ['JwtSignature'],
        parameters: [
            new SA\Parameter(
                name: 'id',
                description: 'Unique identifier of the jwt_signatures',
                in: 'path',
                required: true,
                schema: new SA\Schema(type: 'string', example: '12345')
            ),
        ],
        responses: [
            new SA\Response(
                response: 200,
                description: 'JwtSignature Deleted',
                content: new SA\JsonContent(
                    properties: [
                        new SA\Property(
                            property: 'data',
                            type: 'string',
                            nullable: true
                        ),
                        new SA\Property(
                            property: 'result',
                            type: 'string',
                            example: 'success'
                        ),
                        new SA\Property(
                            property: 'error',
                            type: 'string',
                            example: 'JwtSignature not found',
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
                response: 404,
                description: 'JwtSignature Not Found',
                content: new SA\JsonContent(ref: '#/components/schemas/jot.hf-shield.error.response')
            ),
            new SA\Response(
                response: 500,
                description: 'Application error',
                content: new SA\JsonContent(ref: '#/components/schemas/jot.hf-shield.error.response')
            ),
        ]
    )]
    #[Scope(allow: 'oauth:jwt_signature:delete')]
    #[RateLimit(create: 1, capacity: 2)]
    public function deleteJwtSignature(string $id): PsrResponseInterface
    {
        return $this->response->json([
            'data' => null,
            'result' => $this->repository->delete($id) ? 'success' : 'error',
            'error' => null,
        ]);
    }
}
