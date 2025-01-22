<?php

namespace Jot\HfOAuth2\Controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Jot\HfOAuth2\Repository\AccessTokenRepository;
use League\OAuth2\Server\AuthorizationServer;
use Psr\Http\Message\ServerRequestInterface;

#[Controller]
class AccessTokenController
{

    protected AccessTokenRepository $tokens;

    protected AuthorizationServer $server;

    protected RequestInterface $request;

    protected ResponseInterface $response;

    public function __construct(ContainerInterface $container)
    {
        $this->server = $container->get(AuthorizationServer::class);
        $this->tokens = $container->get(AccessTokenRepository::class);
        $this->request = $container->get(RequestInterface::class);
        $this->response = $container->get(ResponseInterface::class);
    }


    public function issueToken(ServerRequestInterface $request): PsrResponseInterface
    {
        try {
            return $this->server->respondToAccessTokenRequest($request, $this->response);
        } catch (OAuthServerException $e) {
            return $this->response->withStatus($e->getHttpStatusCode())->json([
                'error' => $e->getMessage(),
                'status_code' => $e->getHttpStatusCode(),
            ]);
        } catch (\Throwable $e) {
            return $this->response->withStatus(401)->json(['error' => $e->getMessage(), 'class' => get_class($e)]);
        }
    }

}