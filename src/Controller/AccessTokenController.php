<?php

namespace Jot\HfOAuth2\Controller;

use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\DeleteMapping;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\RateLimit\Annotation\RateLimit;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Jot\HfOAuth2\Repository\AccessTokenRepository;
use Psr\Http\Message\ServerRequestInterface;

#[Controller(prefix: '/oauth')]
class AccessTokenController extends AbstractController
{

    protected string $repository = AccessTokenRepository::class;

    #[RequestMapping(path: 'token', methods: 'POST')]
    #[RateLimit(create: 1, capacity: 2)]
    public function issueToken(RequestInterface $request): PsrResponseInterface
    {
        try {
            return $this->server->respondToAccessTokenRequest($request, $this->response);
        } catch (OAuthServerException $e) {
            return $this->response
                ->withStatus($e->getHttpStatusCode())
                ->json([
                    'error' => $e->getMessage(),
                    'status_code' => $e->getHttpStatusCode(),
                ]);
        } catch (\Throwable $e) {
            return $this->response
                ->withStatus(401)
                ->json([
                    'error' => $e->getMessage(),
                    'class' => get_class($e),
                    'trace' => $e->getTrace(),
                ]);
        }
    }

    #[DeleteMapping(path: 'token/{id}')]
    #[RateLimit(create: 1, capacity: 2)]
    public function revokeToken($id, ServerRequestInterface $request): PsrResponseInterface
    {
        $this->repository()->revokeAccessToken($id);
        return $this->response->withStatus(204)->raw('');
    }


}