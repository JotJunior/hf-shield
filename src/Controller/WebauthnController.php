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

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\RateLimit\Annotation\RateLimit;
use Hyperf\Swagger\Annotation as SA;
use Jot\HfShield\Annotation\Scope;
use Jot\HfShield\Middleware\BearerStrategy;
use Jot\HfShield\Repository\UserRepository;
use Jot\HfShield\Service\WebauthnService;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

#[SA\Schema(
    schema: 'webauthn.registration.options.request',
    required: ['user_id', 'user_name'],
    properties: [
        new SA\Property(property: 'user_id', description: 'ID do usuário', type: 'string'),
        new SA\Property(property: 'user_name', description: 'Nome do usuário', type: 'string'),
    ],
    type: 'object'
)]

#[SA\Schema(
    schema: 'webauthn.registration.options.response',
    properties: [
        new SA\Property(property: 'id', type: 'string'),
        new SA\Property(property: 'user', type: 'object'),
        new SA\Property(property: 'challenge', type: 'string'),
        new SA\Property(property: 'status', type: 'string'),
        new SA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new SA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ],
    type: 'object'
)]

#[SA\Schema(
    schema: 'webauthn.registration.verify.request',
    required: ['user_id', 'user_name', 'credential'],
    properties: [
        new SA\Property(property: 'user_id', description: 'ID do usuário', type: 'string'),
        new SA\Property(property: 'user_name', description: 'Nome do usuário', type: 'string'),
        new SA\Property(property: 'credential', description: 'Credencial WebAuthn', type: 'object'),
    ],
    type: 'object'
)]

#[SA\Schema(
    schema: 'webauthn.registration.verify.response',
    properties: [
        new SA\Property(property: 'result', type: 'string', enum: ['success', 'error']),
        new SA\Property(property: 'data', type: 'object'),
        new SA\Property(property: 'error', type: 'string'),
    ],
    type: 'object'
)]

#[SA\Schema(
    schema: 'webauthn.authentication.options.request',
    required: ['user_id'],
    properties: [
        new SA\Property(property: 'user_id', description: 'ID do usuário', type: 'string'),
    ],
    type: 'object'
)]

#[SA\Schema(
    schema: 'webauthn.authentication.options.response',
    properties: [
        new SA\Property(property: 'result', type: 'string', enum: ['success', 'error']),
        new SA\Property(property: 'data', type: 'object'),
        new SA\Property(property: 'challenge', type: 'object'),
        new SA\Property(property: 'error', type: 'string'),
    ],
    type: 'object'
)]

#[SA\Schema(
    schema: 'webauthn.authentication.verify.request',
    required: ['user_id', 'credential'],
    properties: [
        new SA\Property(property: 'user_id', description: 'ID do usuário', type: 'string'),
        new SA\Property(property: 'credential', description: 'Credencial WebAuthn', type: 'object'),
    ],
    type: 'object'
)]

#[SA\Schema(
    schema: 'webauthn.authentication.verify.response',
    properties: [
        new SA\Property(property: 'result', type: 'string', enum: ['success', 'error']),
        new SA\Property(property: 'data', type: 'object'),
        new SA\Property(property: 'error', type: 'string'),
    ],
    type: 'object'
)]

#[SA\Schema(
    schema: 'webauthn.credentials.list.response',
    type: 'array',
    items: new SA\Items(properties: [
        new SA\Property(property: 'id', type: 'string'),
        new SA\Property(property: 'user_id', type: 'string'),
        new SA\Property(property: 'name', type: 'string'),
        new SA\Property(property: 'created_at', type: 'string', format: 'date-time'),
    ], type: 'object')
)]

#[SA\Schema(
    schema: 'webauthn.credentials.delete.response',
    properties: [
        new SA\Property(property: 'result', type: 'string', enum: ['success', 'error']),
        new SA\Property(property: 'error', type: 'string'),
    ],
    type: 'object'
)]

#[SA\HyperfServer('http')]
#[SA\Tag(
    name: 'Webauthn',
    description: 'Endpoints para autenticação Webauthn'
)]
#[Controller(prefix: '/oauth')]
class WebauthnController extends AbstractController
{
    protected string $repository = UserRepository::class;

    #[Inject]
    protected WebauthnService $webauthnService;

    /**
     * Obter opções para registro de credencial WebAuthn
     */
    #[SA\Post(
        path: '/webauthn/register/options',
        description: 'Obter opções para registro de credencial WebAuthn',
        summary: 'Obter opções para registro WebAuthn',
        security: [
            ['shieldBearerAuth' => ['webauthn:register']],
        ],
        requestBody: new SA\RequestBody(
            required: true,
            content: new SA\JsonContent(ref: '#/components/schemas/webauthn.registration.options.request')
        ),
        tags: ['Webauthn'],
        responses: [
            new SA\Response(
                response: 200,
                description: 'Opções para registro',
                content: new SA\JsonContent(ref: '#/components/schemas/webauthn.registration.options.response')
            ),
            new SA\Response(
                response: 401,
                description: 'Acesso não autorizado',
                content: new SA\JsonContent(ref: '#/components/schemas/jot.hf-shield.auth-error.response')
            ),
            new SA\Response(
                response: 400,
                description: 'Requisição inválida',
                content: new SA\JsonContent(ref: '#/components/schemas/jot.hf-shield.error.response')
            ),
            new SA\Response(
                response: 500,
                description: 'Erro interno',
                content: new SA\JsonContent(ref: '#/components/schemas/jot.hf-shield.error.response')
            ),
        ]
    )]
    #[RateLimit(create: 1, capacity: 3)]
    public function getRegistrationOptions(): PsrResponseInterface
    {
        $data = $this->request->all();
        return $this->response->json($this->webauthnService->getRegistrationOptions($data));
    }

    /**
     * Verificar resposta de registro WebAuthn
     */
    #[SA\Post(
        path: '/webauthn/register/verify',
        description: 'Verificar resposta de registro WebAuthn',
        summary: 'Verificar registro WebAuthn',
        security: [
            ['shieldBearerAuth' => ['webauthn:register']],
        ],
        requestBody: new SA\RequestBody(
            required: true,
            content: new SA\JsonContent(ref: '#/components/schemas/webauthn.registration.verify.request')
        ),
        tags: ['Webauthn'],
        responses: [
            new SA\Response(
                response: 200,
                description: 'Registro verificado com sucesso',
                content: new SA\JsonContent(ref: '#/components/schemas/webauthn.registration.verify.response')
            ),
            new SA\Response(
                response: 401,
                description: 'Acesso não autorizado',
                content: new SA\JsonContent(ref: '#/components/schemas/jot.hf-shield.auth-error.response')
            ),
            new SA\Response(
                response: 400,
                description: 'Requisição inválida',
                content: new SA\JsonContent(ref: '#/components/schemas/jot.hf-shield.error.response')
            ),
            new SA\Response(
                response: 500,
                description: 'Erro interno',
                content: new SA\JsonContent(ref: '#/components/schemas/jot.hf-shield.error.response')
            ),
        ]
    )]
    #[RateLimit(create: 1, capacity: 3)]
    public function verifyRegistration(): PsrResponseInterface
    {
        $data = $this->request->all();
        return $this->response->json($this->webauthnService->verifyRegistration($data));
    }

    /**
     * Obter opções para autenticação WebAuthn
     */
    #[SA\Post(
        path: '/webauthn/authenticate/options',
        description: 'Obter opções para autenticação WebAuthn',
        summary: 'Obter opções para autenticação WebAuthn',
        security: [
            ['shieldBearerAuth' => ['webauthn:authenticate']],
        ],
        requestBody: new SA\RequestBody(
            required: true,
            content: new SA\JsonContent(ref: '#/components/schemas/webauthn.authentication.options.request')
        ),
        tags: ['Webauthn'],
        responses: [
            new SA\Response(
                response: 200,
                description: 'Opções para autenticação',
                content: new SA\JsonContent(ref: '#/components/schemas/webauthn.authentication.options.response')
            ),
            new SA\Response(
                response: 401,
                description: 'Acesso não autorizado',
                content: new SA\JsonContent(ref: '#/components/schemas/jot.hf-shield.auth-error.response')
            ),
            new SA\Response(
                response: 400,
                description: 'Requisição inválida',
                content: new SA\JsonContent(ref: '#/components/schemas/jot.hf-shield.error.response')
            ),
            new SA\Response(
                response: 500,
                description: 'Erro interno',
                content: new SA\JsonContent(ref: '#/components/schemas/jot.hf-shield.error.response')
            ),
        ]
    )]
    #[RateLimit(create: 1, capacity: 3)]
    public function getAuthenticationOptions(): PsrResponseInterface
    {
        $data = $this->request->all();

        // Return options
        return $this->response->json($this->webauthnService->getAuthenticationOptions($data));
    }

    /**
     * Verificar resposta de autenticação WebAuthn
     */
    #[SA\Post(
        path: '/webauthn/authenticate/verify',
        description: 'Verificar resposta de autenticação WebAuthn',
        summary: 'Verificar autenticação WebAuthn',
        security: [
            ['shieldBearerAuth' => ['webauthn:authenticate']],
        ],
        requestBody: new SA\RequestBody(
            required: true,
            content: new SA\JsonContent(ref: '#/components/schemas/webauthn.authentication.verify.request')
        ),
        tags: ['Webauthn'],
        responses: [
            new SA\Response(
                response: 200,
                description: 'Autenticação verificada com sucesso',
                content: new SA\JsonContent(ref: '#/components/schemas/webauthn.authentication.verify.response')
            ),
            new SA\Response(
                response: 401,
                description: 'Acesso não autorizado',
                content: new SA\JsonContent(ref: '#/components/schemas/jot.hf-shield.auth-error.response')
            ),
            new SA\Response(
                response: 400,
                description: 'Requisição inválida',
                content: new SA\JsonContent(ref: '#/components/schemas/jot.hf-shield.error.response')
            ),
            new SA\Response(
                response: 500,
                description: 'Erro interno',
                content: new SA\JsonContent(ref: '#/components/schemas/jot.hf-shield.error.response')
            ),
        ]
    )]
    #[RateLimit(create: 1, capacity: 3)]
    public function verifyAuthentication(): PsrResponseInterface
    {
        $data = $this->request->all();
        return $this->response->json($this->webauthnService->verifyAuthentication($data));
    }

    /**
     * Listar credenciais WebAuthn de um usuário
     */
    #[SA\Get(
        path: '/webauthn/credentials/{id}',
        description: 'Listar credenciais WebAuthn de um usuário',
        summary: 'Listar credenciais WebAuthn',
        security: [
            ['shieldBearerAuth' => ['webauthn:read']],
        ],
        tags: ['Webauthn'],
        parameters: [
            new SA\Parameter(
                name: 'id',
                description: 'ID do usuário',
                in: 'path',
                required: true,
                schema: new SA\Schema(type: 'string')
            )
        ],
        responses: [
            new SA\Response(
                response: 200,
                description: 'Lista de credenciais',
                content: new SA\JsonContent(ref: '#/components/schemas/webauthn.credentials.list.response')
            ),
            new SA\Response(
                response: 401,
                description: 'Acesso não autorizado',
                content: new SA\JsonContent(ref: '#/components/schemas/jot.hf-shield.auth-error.response')
            ),
            new SA\Response(
                response: 400,
                description: 'Requisição inválida',
                content: new SA\JsonContent(ref: '#/components/schemas/jot.hf-shield.error.response')
            ),
            new SA\Response(
                response: 500,
                description: 'Erro interno',
                content: new SA\JsonContent(ref: '#/components/schemas/jot.hf-shield.error.response')
            ),
        ]
    )]
    #[Middleware(BearerStrategy::class)]
    #[Scope('webauthn:read')]
    #[RateLimit(create: 1, capacity: 3)]
    public function listCredentials(string $id): PsrResponseInterface
    {
        return $this->response->json($this->webauthnService->listCredentials($id));
    }

    /**
     * Excluir uma credencial WebAuthn
     */
    #[SA\Delete(
        path: '/webauthn/credentials/{id}',
        description: 'Excluir uma credencial WebAuthn',
        summary: 'Excluir credencial WebAuthn',
        security: [
            ['shieldBearerAuth' => ['webauthn:delete']],
        ],
        tags: ['Webauthn'],
        parameters: [
            new SA\Parameter(
                name: 'id',
                description: 'ID da credencial',
                in: 'path',
                required: true,
                schema: new SA\Schema(type: 'string')
            )
        ],
        responses: [
            new SA\Response(
                response: 200,
                description: 'Credencial excluída com sucesso',
                content: new SA\JsonContent(ref: '#/components/schemas/webauthn.credentials.delete.response')
            ),
            new SA\Response(
                response: 401,
                description: 'Acesso não autorizado',
                content: new SA\JsonContent(ref: '#/components/schemas/jot.hf-shield.auth-error.response')
            ),
            new SA\Response(
                response: 404,
                description: 'Credencial não encontrada',
                content: new SA\JsonContent(ref: '#/components/schemas/jot.hf-shield.error.response')
            ),
            new SA\Response(
                response: 500,
                description: 'Erro interno',
                content: new SA\JsonContent(ref: '#/components/schemas/jot.hf-shield.error.response')
            ),
        ]
    )]
    #[Middleware(BearerStrategy::class)]
    #[Scope('webauthn:delete')]
    #[RateLimit(create: 1, capacity: 3)]
    public function deleteCredential(string $id): PsrResponseInterface
    {
        $success = $this->webauthnService->delete($id);

        if (!$success) {
            return $this->response->json([
                'result' => 'error',
                'error' => 'Credential not found',
            ])->withStatus(404);
        }

        return $this->response->json([
            'result' => 'success',
        ]);
    }
}
