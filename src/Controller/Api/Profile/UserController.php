<?php

declare(strict_types=1);
/**
 * This file is part of the hf_shield module, a package build for Hyperf framework that is responsible for OAuth2 authentication and access control.
 *
 * @author   Joao Zanon <jot@jot.com.br>
 * @link     https://github.com/JotJunior/hf-shield
 * @license  MIT
 */

namespace Jot\HfShield\Controller\Api\Profile;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\PutMapping;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\RateLimit\Annotation\RateLimit;
use Jot\HfShield\Annotation\Scope;
use Jot\HfShield\Middleware\BearerStrategy;
use Jot\HfShield\Service\ProfileService;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

#[Controller(prefix: '/api/user')]
class UserController
{
    #[Inject]
    protected ResponseInterface $response;

    #[Inject]
    protected RequestInterface $request;

    #[Inject]
    protected ProfileService $service;

    #[RateLimit(create: 1, capacity: 2)]
    #[Middleware(middleware: BearerStrategy::class)]
    #[Scope(allow: 'user:profile:profile')]
    #[GetMapping(path: 'me')]
    public function getUserProfileData(): PsrResponseInterface
    {
        return $this->response->json(
            $this->service->getProfileData(
                $this->request->getAttribute('oauth_user_id'),
                $this->request->query('_tenant_id')
            )
        );
    }

    #[RateLimit(create: 1, capacity: 2)]
    #[Middleware(middleware: BearerStrategy::class)]
    #[Scope(allow: 'user:profile:session')]
    #[PutMapping(path: 'password')]
    public function updateUserProfilePassword(): PsrResponseInterface
    {
        return $this->response->json(
            $this->service->updatePassword(
                $this->request->getAttribute('oauth_user_id'),
                $this->request->all()
            )
        );
    }

    #[RateLimit(create: 1, capacity: 2)]
    #[Middleware(middleware: BearerStrategy::class)]
    #[Scope(allow: 'user:profile:update_settings')]
    #[PutMapping(path: 'settings')]
    public function updateUserProfileSettings(): PsrResponseInterface
    {
        return $this->response->json(
            $this->service->updateSettings(
                $this->request->getAttribute('oauth_user_id'),
                $this->request->getParsedBody()['settings']
            )
        );
    }

    #[RateLimit(create: 1, capacity: 2)]
    #[Scope(allow: 'user:profile:update')]
    #[Middleware(middleware: BearerStrategy::class)]
    #[PutMapping(path: 'me')]
    public function updateProfileUser(): PsrResponseInterface
    {
        $id = $this->request->getAttribute('oauth_user_id');
        $result = $this->service->updateProfile($id, $this->request->all());
        return $this->response->json($result);
    }

    #[RateLimit(create: 1, capacity: 2)]
    #[Scope(allow: 'user:profile:view')]
    #[Middleware(middleware: BearerStrategy::class)]
    #[RequestMapping(path: 'me', methods: ['HEAD'])]
    public function verifyProfileUser(string $id): PsrResponseInterface
    {
        $exists = $this->service->exists($id);
        return $this->response->withStatus($exists ? 204 : 404)->raw('');
    }

    #[RequestMapping(path: '[(me|session|password|settings)]', methods: ['OPTIONS'])]
    public function requestProfileOptions(): PsrResponseInterface
    {
        return $this->response
            ->json([
                'methods' => ['GET', 'POST', 'PUT', 'HEAD'],
                'rate_limit' => 'Max 2 requests per second.',
            ]);
    }
}
