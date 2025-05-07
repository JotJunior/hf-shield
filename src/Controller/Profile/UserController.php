<?php

declare(strict_types=1);
/**
 * This file is part of the hf_shield module, a package build for Hyperf framework that is responsible for OAuth2 authentication and access control.
 *
 * @author   Joao Zanon <jot@jot.com.br>
 * @link     https://github.com/JotJunior/hf-shield
 * @license  MIT
 */

namespace Jot\HfShield\Controller\Profile;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\PutMapping;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\RateLimit\Annotation\RateLimit;
use Jot\HfRepository\Exception\RecordNotFoundException;
use Jot\HfShield\Annotation\Scope;
use Jot\HfShield\Controller\AbstractController;
use Jot\HfShield\Exception\ForbiddenAccessException;
use Jot\HfShield\Middleware\SessionStrategy;
use Jot\HfShield\Service\ProfileService;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

#[Controller(prefix: '/user')]
class UserController extends AbstractController
{
    #[Inject]
    protected ProfileService $service;

    #[RateLimit(create: 1, capacity: 2)]
    #[Middleware(middleware: SessionStrategy::class)]
    #[Scope(allow: 'oauth:user:session')]
    #[GetMapping(path: 'session')]
    public function getUserSessionData(): PsrResponseInterface
    {
        return $this->response->json(
            $this->service->getSessionData(
                $this->request->getAttribute('oauth_user_id')
            )
        );
    }

    #[RateLimit(create: 1, capacity: 2)]
    #[Scope(allow: 'oauth:user:view')]
    #[Middleware(middleware: SessionStrategy::class)]
    #[GetMapping(path: '{id}')]
    public function getUserProfileData(string $id): PsrResponseInterface
    {
        if ($id !== $this->request->getAttribute('oauth_user_id')) {
            throw new ForbiddenAccessException();
        }
        $data = $this->service->getProfileData($id);

        if (empty($data)) {
            throw new RecordNotFoundException();
        }

        return $this->response->json($data);
    }

    #[RateLimit(create: 1, capacity: 2)]
    #[Middleware(middleware: SessionStrategy::class)]
    #[Scope(allow: 'oauth:user:session')]
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
    #[Middleware(middleware: SessionStrategy::class)]
    #[Scope(allow: 'oauth:user:update_settings')]
    #[PutMapping(path: 'me')]
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
    #[Scope(allow: 'oauth:user:update')]
    #[Middleware(middleware: SessionStrategy::class)]
    #[PutMapping(path: '{id}')]
    public function updateProfileUser(string $id): PsrResponseInterface
    {
        $result = $this->service->updateProfile($id, $this->request->all());
        return $this->response->json($result);
    }

    #[RateLimit(create: 1, capacity: 2)]
    #[Scope(allow: 'oauth:user:view')]
    #[Middleware(middleware: SessionStrategy::class)]
    #[RequestMapping(path: '{id}', methods: ['HEAD'])]
    public function verifyProfileUser(string $id): PsrResponseInterface
    {
        $exists = $this->service->exists($id);
        return $this->response->withStatus($exists ? 204 : 404)->raw('');
    }

    #[RequestMapping(path: '[{id}]', methods: ['OPTIONS'])]
    public function requestProfileOptions(): PsrResponseInterface
    {
        return $this->response
            ->json([
                'methods' => ['GET', 'POST', 'PUT', 'HEAD'],
                'rate_limit' => 'Max 2 requests per second.',
            ]);
    }
}
