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

    #[SA\Post(
        path: '/webauthn/register/options',
        description: 'Create a new users.',
        summary: 'Create a New User',
        security: [
            ['shieldBearerAuth' => ['oauth:user:create']],
        ],
        requestBody: new SA\RequestBody(
            required: true,
            content: new SA\JsonContent(ref: '#/components/schemas/jot.hf-shield.entity.user.user')
        ),
        tags: ['User'],
        responses: [
            new SA\Response(
                response: 201,
                description: 'User created',
                content: new SA\JsonContent(ref: '#/components/schemas/jot.hf-shield.entity.user.user')
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
    #[RateLimit(create: 1, capacity: 3)]
    public function getRegistrationOptions(): PsrResponseInterface
    {
        $data = $this->request->all();
        return $this->response->json($this->webauthnService->getRegistrationOptions($data));
    }

    #[SA\Post(
        path: '/webauthn/register/verify',
        description: 'Create a new users.',
        summary: 'Create a New User',
        security: [
            ['shieldBearerAuth' => ['oauth:user:create']],
        ],
        requestBody: new SA\RequestBody(
            required: true,
            content: new SA\JsonContent(ref: '#/components/schemas/jot.hf-shield.entity.user.user')
        ),
        tags: ['User'],
        responses: [
            new SA\Response(
                response: 201,
                description: 'User created',
                content: new SA\JsonContent(ref: '#/components/schemas/jot.hf-shield.entity.user.user')
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
    #[RateLimit(create: 1, capacity: 3)]
    public function verifyRegistration()
    {
        $data = $this->request->all();
        return $this->response->json($this->webauthnService->verifyRegistration($data));
    }

    #[SA\Post(
        path: '/webauthn/authenticate/options',
        description: 'Create a new users.',
        summary: 'Create a New User',
        security: [
            ['shieldBearerAuth' => ['oauth:user:create']],
        ],
        requestBody: new SA\RequestBody(
            required: true,
            content: new SA\JsonContent(ref: '#/components/schemas/jot.hf-shield.entity.user.user')
        ),
        tags: ['User'],
        responses: [
            new SA\Response(
                response: 201,
                description: 'User created',
                content: new SA\JsonContent(ref: '#/components/schemas/jot.hf-shield.entity.user.user')
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
    #[RateLimit(create: 1, capacity: 3)]
    public function getAuthenticationOptions(): PsrResponseInterface
    {
        $data = $this->request->all();

        // Return options
        return $this->response->json($this->webauthnService->getAuthenticationOptions($data));
    }

    /**
     * Verify authentication response.
     */
    #[SA\Post(
        path: '/webauthn/authenticate/verify',
        description: 'Create a new users.',
        summary: 'Create a New User',
        security: [
            ['shieldBearerAuth' => ['oauth:user:create']],
        ],
        requestBody: new SA\RequestBody(
            required: true,
            content: new SA\JsonContent(ref: '#/components/schemas/jot.hf-shield.entity.user.user')
        ),
        tags: ['User'],
        responses: [
            new SA\Response(
                response: 201,
                description: 'User created',
                content: new SA\JsonContent(ref: '#/components/schemas/jot.hf-shield.entity.user.user')
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
    #[RateLimit(create: 1, capacity: 3)]
    public function verifyAuthentication(): PsrResponseInterface
    {
        $data = $this->request->all();
        return $this->response->json($this->webauthnService->verifyAuthentication($data));
    }

    #[SA\Get(
        path: '/webauthn/credentials/{id}',
        description: 'Create a new users.',
        summary: 'Create a New User',
        security: [
            ['shieldBearerAuth' => ['oauth:user:create']],
        ],
        requestBody: new SA\RequestBody(
            required: true,
            content: new SA\JsonContent(ref: '#/components/schemas/jot.hf-shield.entity.user.user')
        ),
        tags: ['User'],
        responses: [
            new SA\Response(
                response: 201,
                description: 'User created',
                content: new SA\JsonContent(ref: '#/components/schemas/jot.hf-shield.entity.user.user')
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
    #[Middleware(BearerStrategy::class)]
    #[Scope('webauthn:read')]
    #[RateLimit(create: 1, capacity: 3)]
    public function listCredentials(string $id): PsrResponseInterface
    {
        return $this->response->json($this->webauthnService->listCredentials($id));
    }

    #[SA\Delete(
        path: '/webauthn/credentials/{id}',
        description: 'Create a new users.',
        summary: 'Create a New User',
        security: [
            ['shieldBearerAuth' => ['oauth:user:create']],
        ],
        requestBody: new SA\RequestBody(
            required: true,
            content: new SA\JsonContent(ref: '#/components/schemas/jot.hf-shield.entity.user.user')
        ),
        tags: ['User'],
        responses: [
            new SA\Response(
                response: 201,
                description: 'User created',
                content: new SA\JsonContent(ref: '#/components/schemas/jot.hf-shield.entity.user.user')
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
