<?php

declare(strict_types=1);
/**
 * This file is part of the hf_shield module, a package build for Hyperf framework that is responsible for OAuth2 authentication and access control.
 *
 * @author   Joao Zanon <jot@jot.com.br>
 * @link     https://github.com/JotJunior/hf-shield
 * @license  MIT
 */

namespace Jot\HfShield\Controller\Recover;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\RateLimit\Annotation\RateLimit;
use Jot\HfShield\Service\OtpService;
use Jot\HfShield\Service\ProfileService;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

#[Controller(prefix: '/user')]
class PasswordController
{
    #[Inject]
    protected ResponseInterface $response;

    #[Inject]
    protected RequestInterface $request;

    #[Inject]
    protected ProfileService $service;

    #[Inject]
    protected OtpService $otpService;

    #[RateLimit(create: 1, capacity: 2)]
    #[PostMapping(path: 'recover-password')]
    public function getUserSessionData(): PsrResponseInterface
    {
        return $this->response->json(
            $this->otpService->create($this->request->all())
        );
    }
}
