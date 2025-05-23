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
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\RateLimit\Annotation\RateLimit;
use Jot\HfShield\Annotation\Scope;
use Jot\HfShield\Middleware\BearerStrategy;
use Jot\HfShield\Service\AccessTokenService;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

#[Controller(prefix: '/api/user')]
class AccessTokenController
{
    #[Inject]
    protected ResponseInterface $response;

    #[Inject]
    protected RequestInterface $request;

    #[Inject]
    protected AccessTokenService $service;

    #[RateLimit(create: 1, capacity: 10)]
    #[Scope(allow: 'user:token:list')]
    #[Middleware(middleware: BearerStrategy::class)]
    #[RequestMapping(path: 'tokens', methods: ['GET'])]
    public function getUserTokenList(): PsrResponseInterface
    {
        $userId = $this->request->getAttribute('oauth_user_id');
        $result = $this->service->userLogList($userId);

        if ($result['result'] === 'error') {
            return $this->response->withStatus(400)->json($result);
        }

        return $this->response
            ->json($result);
    }

    #[RequestMapping(path: 'tokens', methods: ['OPTIONS'])]
    public function requestOptions(): PsrResponseInterface
    {
        return $this->response
            ->json([
                'methods' => ['GET', 'POST', 'PUT', 'DELETE', 'HEAD'],
                'rate_limit' => 'Max 10 requests per second.',
            ]);
    }
}
