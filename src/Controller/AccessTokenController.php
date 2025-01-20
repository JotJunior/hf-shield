<?php

namespace Jot\HfOAuth2\Controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Jot\HfOAuth2\Repository\AccessTokenRepository;
use League\OAuth2\Server\AuthorizationServer;
use Psr\Http\Message\ServerRequestInterface;

#[Controller]
class AccessTokenController
{

    #[Inject]
    protected AccessTokenRepository $tokens;

    #[Inject]
    protected AuthorizationServer $server;

    #[Inject]
    protected ContainerInterface $container;

    #[Inject]
    protected RequestInterface $request;

    #[Inject]
    protected ResponseInterface $response;


    public function issueToken(ServerRequestInterface $request): PsrResponseInterface
    {
        return $this->server->respondToAccessTokenRequest($request, $this->response);
    }

}