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
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\RateLimit\Annotation\RateLimit;
use Hyperf\Swagger\Annotation as SA;
use Jot\HfShield\Annotation\Scope;
use Jot\HfShield\Entity\User\User;
use Jot\HfShield\Middleware\BearerStrategy;
use Jot\HfShield\Middleware\SessionStrategy;
use Jot\HfShield\Repository\UserRepository;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

use function Hyperf\Support\make;

#[SA\HyperfServer('http')]
#[SA\Tag(
    name: 'User',
    description: 'Endpoints related to users management'
)]
#[Controller(prefix: '/oauth')]
class UserController extends AbstractController
{
    protected string $repository = UserRepository::class;

    #[SA\Post(
        path: '/oauth/users',
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
    #[Scope(allow: 'oauth:user:create')]
    #[RateLimit(create: 1, capacity: 2)]
    #[Middleware(BearerStrategy::class)]
    public function createUser(): PsrResponseInterface
    {
        $userData = $this->request->all();
        $user = make(User::class, ['data' => $userData]);

        return $this->saveUser($user);
    }

    #[GetMapping(path: '/oauth/users/me')]
    #[Scope(allow: 'oauth:user:create')]
    #[Middleware(SessionStrategy::class)]
    #[RateLimit(create: 1, capacity: 10)]
    public function userSessionData(): PsrResponseInterface
    {
        return $this->response->json(
            $this->request->getAttribute('oauth_session_user')
        );
    }

    #[SA\Put(
        path: '/oauth/users/{id}',
        description: 'Update the details of an existing users.',
        summary: 'Update an existing User',
        security: [['shieldBearerAuth' => ['oauth:user:update']]],
        requestBody: new SA\RequestBody(
            required: true,
            content: new SA\JsonContent(ref: '#/components/schemas/jot.hf-shield.entity.user.user')
        ),
        tags: ['User'],
        parameters: [
            new SA\Parameter(
                name: 'id',
                description: 'Unique identifier of the users',
                in: 'path',
                required: true,
                schema: new SA\Schema(type: 'string', example: '12345')
            ),
        ],
        responses: [
            new SA\Response(
                response: 200,
                description: 'User Updated',
                content: new SA\JsonContent(ref: '#/components/schemas/jot.hf-shield.entity.user.user')
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
                description: 'User Not Found',
                content: new SA\JsonContent(ref: '#/components/schemas/jot.hf-shield.error.response')
            ),
            new SA\Response(
                response: 500,
                description: 'Application error',
                content: new SA\JsonContent(ref: '#/components/schemas/jot.hf-shield.error.response')
            ),
        ]
    )]
    #[Scope(allow: 'oauth:user:update')]
    #[RateLimit(create: 1, capacity: 2)]
    #[Middleware(BearerStrategy::class)]
    public function updateUser(string $id): PsrResponseInterface
    {
        $userData = $this->request->all();
        $userData['id'] = $id;
        $user = make(User::class, ['data' => $userData]);
        $user->setEntityState('update');
        if (! $user->validate()) {
            return $this->response->withStatus(400)->json($user->getErrors());
        }
        $updatedUser = $this->repository()->update($user);
        return $this->response->json($updatedUser->toArray());
    }

    private function saveUser(User $user): PsrResponseInterface
    {
        $createdUser = $this->repository()->create($user);
        $userData = $createdUser
            ->hide(['password_salt', 'password'])
            ->toArray();
        return $this->response->json($userData);
    }
}
