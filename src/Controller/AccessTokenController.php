<?php

namespace Jot\HfOAuth2\Controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Jot\HfOAuth2\Repository\AccessTokenRepository;
use Psr\Http\Message\ServerRequestInterface;

#[Controller]
class AccessTokenController extends AbstractController
{

    #[Inject]
    protected AccessTokenRepository $tokens;

    public function issueToken(ServerRequestInterface $request): PsrResponseInterface
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
                ->json(['error' => $e->getMessage(), 'class' => get_class($e)]);
        }
    }

}