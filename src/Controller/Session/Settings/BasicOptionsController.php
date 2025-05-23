<?php

declare(strict_types=1);
/**
 * This file is part of the hf_shield module, a package build for Hyperf framework that is responsible for OAuth2 authentication and access control.
 *
 * @author   Joao Zanon <jot@jot.com.br>
 * @link     https://github.com/JotJunior/hf-shield
 * @license  MIT
 */

namespace Jot\HfShield\Controller\Session\Settings;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\RateLimit\Annotation\RateLimit;
use Hyperf\Swagger\Annotation as SA;
use Jot\HfRepository\Exception\RecordNotFoundException;
use Jot\HfShield\Annotation\Scope;
use Jot\HfShield\Controller\AbstractController;
use Jot\HfShield\Middleware\SessionStrategy;
use Jot\HfShield\Service\BasicOptionService;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

#[SA\HyperfServer('http')]
#[SA\Tag(
    name: 'BasicOption',
    description: 'Endpoints related to basic_options management'
)]
#[SA\Schema(
    schema: 'settings.error.response',
    required: ['result', 'error'],
    properties: [
        new SA\Property(property: 'result', description: self::DESCRIPTION_RESPONSE_ERROR_RESULT, type: 'string', example: 'error'),
        new SA\Property(property: 'error', description: self::DESCRIPTION_RESPONSE_ERROR_MESSAGE, type: 'string', example: 'Error message'),
        new SA\Property(property: 'data', description: self::DESCRIPTION_RESPONSE_ERROR_JSON, type: 'string|array', example: null),
    ],
    type: 'object'
)]
#[Controller(prefix: '/v2')]
class BasicOptionsController extends AbstractController
{
    private const REQUEST_PATH = '/settings/options';

    private const REQUEST_PATH_ID = '/settings/options/{id}';

    private const RESPONSE_SCHEMA_CONTENT = '#/components/schemas/jot.shield.entity.basic_option.basic_option';

    private const RESPONSE_SCHEMA_ERROR = '#/components/schemas/settings.error.response';

    #[Inject]
    protected BasicOptionService $service;

    #[SA\Get(
        path: self::REQUEST_PATH,
        description: 'Retrieve a list of basic_options with optional pagination and filters.',
        summary: 'Get BasicOptions List',
        security: [['shieldBearerAuth' => ['settings:basic_option:list']]],
        tags: ['BasicOption'],
        parameters: [
            new SA\Parameter(
                name: self::QUERY_PAGE_NUMBER,
                description: self::DESCRIPTION_PAGE_NUMBER,
                in: 'query',
                required: false,
                schema: new SA\Schema(type: 'integer', example: 1)
            ),
            new SA\Parameter(
                name: self::QUERY_RESULTS_PER_PAGE,
                description: self::DESCRIPTION_PAGE_RESULTS_PER_PAGE,
                in: 'query',
                required: false,
                schema: new SA\Schema(type: 'integer', example: 10)
            ),
            new SA\Parameter(
                name: self::QUERY_SORT,
                description: self::DESCRIPTION_PAGE_RESULTS_SORT,
                in: 'query',
                required: false,
                schema: new SA\Schema(type: 'string', example: 'created_at:desc,updated_at:desc')
            ),
            new SA\Parameter(
                name: self::QUERY_RESULT_FIELDS,
                description: self::DESCRIPTION_PAGE_RESULTS_FIELDS,
                in: 'query',
                required: false,
                schema: new SA\Schema(type: 'string', example: 'id,created_at,updated_at')
            ),
        ],
        responses: [
            new SA\Response(
                response: 200,
                description: 'BasicOption details retrieved successfully',
                content: new SA\JsonContent(
                    properties: [
                        new SA\Property(
                            property: 'data',
                            type: 'array',
                            items: new SA\Items(ref: self::RESPONSE_SCHEMA_CONTENT)
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
                description: self::DESCRIPTION_BAD_REQUEST,
                content: new SA\JsonContent(ref: self::RESPONSE_SCHEMA_ERROR)
            ),
            new SA\Response(
                response: 401,
                description: self::DESCRIPTION_UNAUTHORIZED_ACCESS,
                content: new SA\JsonContent(ref: self::RESPONSE_SCHEMA_ERROR)
            ),
            new SA\Response(
                response: 403,
                description: self::DESCRIPTION_FORBIDDEN_ACCESS,
                content: new SA\JsonContent(ref: self::RESPONSE_SCHEMA_ERROR)
            ),
            new SA\Response(
                response: 500,
                description: self::DESCRIPTION_APPLICATION_ERROR,
                content: new SA\JsonContent(ref: self::RESPONSE_SCHEMA_ERROR)
            ),
        ]
    )]
    #[RateLimit(create: 1, capacity: 10)]
    #[Scope(allow: 'settings:basic_option:list')]
    #[Middleware(middleware: SessionStrategy::class)]
    public function getBasicOptionList(): PsrResponseInterface
    {
        $result = $this->service->paginate($this->request->query());
        if ($result['result'] === 'error') {
            return $this->response->withStatus(400)->json($result);
        }

        return $this->response
            ->json($result);
    }

    #[RateLimit(create: 1, capacity: 50)]
    #[Scope(allow: 'settings:basic_option:list')]
    #[Middleware(middleware: SessionStrategy::class)]
    #[GetMapping(path: self::REQUEST_PATH . '/autocomplete')]
    public function getBasicOptionAutocomplete(): PsrResponseInterface
    {
        return $this->response
            ->json(
                $this->service
                    ->autocomplete($this->request->query('search', ''))
            );
    }

    #[SA\Get(
        path: self::REQUEST_PATH_ID,
        description: 'Retrieve the details of a specific basic_options identified by ID.',
        summary: 'Get BasicOption Data',
        security: [['shieldBearerAuth' => ['settings:basic_option:view']]],
        tags: ['BasicOption'],
        parameters: [
            new SA\Parameter(
                name: 'id',
                description: self::DESCRIPTION_PARAMETER_ID,
                in: 'path',
                required: true,
                schema: new SA\Schema(type: 'string', example: self::EXAMPLE_PARAMETER_ID)
            ),
        ],
        responses: [
            new SA\Response(
                response: 200,
                description: 'BasicOption details retrieved successfully',
                content: new SA\JsonContent(
                    properties: [
                        new SA\Property(
                            property: 'data',
                            ref: self::RESPONSE_SCHEMA_CONTENT
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
                description: self::DESCRIPTION_BAD_REQUEST,
                content: new SA\JsonContent(ref: self::RESPONSE_SCHEMA_ERROR)
            ),
            new SA\Response(
                response: 401,
                description: self::DESCRIPTION_UNAUTHORIZED_ACCESS,
                content: new SA\JsonContent(ref: self::RESPONSE_SCHEMA_ERROR)
            ),
            new SA\Response(
                response: 403,
                description: self::DESCRIPTION_FORBIDDEN_ACCESS,
                content: new SA\JsonContent(ref: self::RESPONSE_SCHEMA_ERROR)
            ),
            new SA\Response(
                response: 404,
                description: 'BasicOption not Found',
                content: new SA\JsonContent(ref: self::RESPONSE_SCHEMA_ERROR)
            ),
            new SA\Response(
                response: 500,
                description: self::DESCRIPTION_APPLICATION_ERROR,
                content: new SA\JsonContent(ref: self::RESPONSE_SCHEMA_ERROR)
            ),
        ]
    )]
    #[RateLimit(create: 1, capacity: 10)]
    #[Scope(allow: 'settings:basic_option:view')]
    #[Middleware(middleware: SessionStrategy::class)]
    public function getBasicOptionData(string $id): PsrResponseInterface
    {
        $data = $this->service->getData($id);

        if (empty($data)) {
            throw new RecordNotFoundException();
        }

        return $this->response->json($data);
    }

    #[SA\Post(
        path: self::REQUEST_PATH,
        description: 'Create a new basic option.',
        summary: 'Create a New BasicOption',
        security: [['shieldBearerAuth' => ['settings:basic_option:create']]],
        requestBody: new SA\RequestBody(
            required: true,
            content: new SA\JsonContent(ref: self::RESPONSE_SCHEMA_CONTENT)
        ),
        tags: ['BasicOption'],
        responses: [
            new SA\Response(
                response: 201,
                description: 'BasicOption created',
                content: new SA\JsonContent(ref: self::RESPONSE_SCHEMA_CONTENT)
            ),
            new SA\Response(
                response: 400,
                description: self::DESCRIPTION_BAD_REQUEST,
                content: new SA\JsonContent(ref: self::RESPONSE_SCHEMA_ERROR)
            ),
            new SA\Response(
                response: 401,
                description: self::DESCRIPTION_UNAUTHORIZED_ACCESS,
                content: new SA\JsonContent(ref: self::RESPONSE_SCHEMA_ERROR)
            ),
            new SA\Response(
                response: 403,
                description: self::DESCRIPTION_FORBIDDEN_ACCESS,
                content: new SA\JsonContent(ref: self::RESPONSE_SCHEMA_ERROR)
            ),
            new SA\Response(
                response: 500,
                description: self::DESCRIPTION_APPLICATION_ERROR,
                content: new SA\JsonContent(ref: self::RESPONSE_SCHEMA_ERROR)
            ),
        ]
    )]
    #[RateLimit(create: 1, capacity: 5)]
    #[Scope(allow: 'settings:basic_option:create')]
    #[Middleware(middleware: SessionStrategy::class)]
    public function createBasicOption(): PsrResponseInterface
    {
        $result = $this->service->create($this->request->all());
        return $this->response->withStatus(201)->json($result);
    }

    #[SA\Put(
        path: self::REQUEST_PATH_ID,
        description: 'Update the details of an existing basic_options.',
        summary: 'Update an existing BasicOption',
        security: [['shieldBearerAuth' => ['settings:basic_option:update']]],
        requestBody: new SA\RequestBody(
            required: true,
            content: new SA\JsonContent(ref: self::RESPONSE_SCHEMA_CONTENT)
        ),
        tags: ['BasicOption'],
        parameters: [
            new SA\Parameter(
                name: 'id',
                description: self::DESCRIPTION_PARAMETER_ID,
                in: 'path',
                required: true,
                schema: new SA\Schema(type: 'string', example: '12345')
            ),
        ],
        responses: [
            new SA\Response(
                response: 200,
                description: 'BasicOption Updated',
                content: new SA\JsonContent(ref: self::RESPONSE_SCHEMA_CONTENT)
            ),
            new SA\Response(
                response: 400,
                description: self::DESCRIPTION_BAD_REQUEST,
                content: new SA\JsonContent(ref: self::RESPONSE_SCHEMA_ERROR)
            ),
            new SA\Response(
                response: 401,
                description: self::DESCRIPTION_UNAUTHORIZED_ACCESS,
                content: new SA\JsonContent(ref: self::RESPONSE_SCHEMA_ERROR)
            ),
            new SA\Response(
                response: 403,
                description: self::DESCRIPTION_FORBIDDEN_ACCESS,
                content: new SA\JsonContent(ref: self::RESPONSE_SCHEMA_ERROR)
            ),
            new SA\Response(
                response: 404,
                description: 'BasicOption Not Found',
                content: new SA\JsonContent(ref: self::RESPONSE_SCHEMA_ERROR)
            ),
            new SA\Response(
                response: 500,
                description: self::DESCRIPTION_APPLICATION_ERROR,
                content: new SA\JsonContent(ref: self::RESPONSE_SCHEMA_ERROR)
            ),
        ]
    )]
    #[RateLimit(create: 1, capacity: 2)]
    #[Scope(allow: 'settings:basic_option:update')]
    #[Middleware(middleware: SessionStrategy::class)]
    public function updateBasicOption(string $id): PsrResponseInterface
    {
        $result = $this->service->update($id, $this->request->all());
        return $this->response->json($result);
    }

    #[SA\Delete(
        path: self::REQUEST_PATH_ID,
        description: 'Delete an existing basic_options by its unique identifier.',
        summary: 'Delete an existing BasicOption',
        security: [['shieldBearerAuth' => ['settings:basic_option:delete']]],
        tags: ['BasicOption'],
        parameters: [
            new SA\Parameter(
                name: 'id',
                description: self::DESCRIPTION_PARAMETER_ID,
                in: 'path',
                required: true,
                schema: new SA\Schema(type: 'string', example: '12345')
            ),
        ],
        responses: [
            new SA\Response(
                response: 200,
                description: 'BasicOption Deleted',
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
                            example: 'BasicOption not found',
                            nullable: true
                        ),
                    ],
                    type: 'object'
                )
            ),
            new SA\Response(
                response: 400,
                description: self::DESCRIPTION_BAD_REQUEST,
                content: new SA\JsonContent(ref: self::RESPONSE_SCHEMA_ERROR)
            ),
            new SA\Response(
                response: 401,
                description: self::DESCRIPTION_UNAUTHORIZED_ACCESS,
                content: new SA\JsonContent(ref: self::RESPONSE_SCHEMA_ERROR)
            ),
            new SA\Response(
                response: 403,
                description: self::DESCRIPTION_FORBIDDEN_ACCESS,
                content: new SA\JsonContent(ref: self::RESPONSE_SCHEMA_ERROR)
            ),
            new SA\Response(
                response: 404,
                description: 'BasicOption Not Found',
                content: new SA\JsonContent(ref: self::RESPONSE_SCHEMA_ERROR)
            ),
            new SA\Response(
                response: 500,
                description: self::DESCRIPTION_APPLICATION_ERROR,
                content: new SA\JsonContent(ref: self::RESPONSE_SCHEMA_ERROR)
            ),
        ]
    )]
    #[RateLimit(create: 1, capacity: 2)]
    #[Scope(allow: 'settings:basic_option:delete')]
    #[Middleware(middleware: SessionStrategy::class)]
    public function deleteBasicOption(string $id): PsrResponseInterface
    {
        $result = $this->service->delete($id);
        return $this->response->json($result);
    }

    #[SA\Head(
        path: self::REQUEST_PATH_ID,
        description: 'Check if a valid basic_options exists by its unique identifier.',
        summary: 'Check basic_options',
        security: [['shieldBearerAuth' => ['settings:basic_option:view']]],
        tags: ['BasicOption'],
        parameters: [
            new SA\Parameter(
                name: 'id',
                description: 'Unique identifier of the basic_options',
                in: 'path',
                required: true,
                schema: new SA\Schema(type: 'string', example: 'abc1234')
            ),
        ],
        responses: [
            new SA\Response(
                response: 204,
                description: 'BasicOption found',
                content: null
            ),
            new SA\Response(
                response: 400,
                description: self::DESCRIPTION_BAD_REQUEST,
                content: new SA\JsonContent(ref: self::RESPONSE_SCHEMA_ERROR)
            ),
            new SA\Response(
                response: 401,
                description: self::DESCRIPTION_UNAUTHORIZED_ACCESS,
                content: new SA\JsonContent(ref: self::RESPONSE_SCHEMA_ERROR)
            ),
            new SA\Response(
                response: 403,
                description: self::DESCRIPTION_FORBIDDEN_ACCESS,
                content: new SA\JsonContent(ref: self::RESPONSE_SCHEMA_ERROR)
            ),
            new SA\Response(
                response: 404,
                description: 'BasicOption not Found',
                content: null
            ),
            new SA\Response(
                response: 500,
                description: self::DESCRIPTION_APPLICATION_ERROR,
                content: new SA\JsonContent(ref: self::RESPONSE_SCHEMA_ERROR)
            ),
        ]
    )]
    #[RateLimit(create: 1, capacity: 5)]
    #[Scope(allow: 'settings:basic_option:view')]
    #[Middleware(middleware: SessionStrategy::class)]
    public function verifyBasicOption(string $id): PsrResponseInterface
    {
        $exists = $this->service->exists($id);
        return $this->response->withStatus($exists ? 204 : 404)->raw('');
    }

    #[RequestMapping(path: '/settings/options[/[{id}]]', methods: ['OPTIONS'])]
    public function requestOptions(): PsrResponseInterface
    {
        return $this->response
            ->json([
                'methods' => ['GET', 'POST', 'PUT', 'DELETE', 'HEAD'],
                'rate_limit' => 'Max 10 requests per second.',
            ]);
    }
}
