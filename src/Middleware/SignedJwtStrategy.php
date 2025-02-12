<?php

declare(strict_types=1);

namespace Jot\HfShield\Middleware;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SignedJwtStrategy implements MiddlewareInterface
{

    public const ATTR_ACCESS_TOKEN_ID = 'oauth_access_token_id';
    public const ATTR_CLIENT_ID = 'oauth_client_id';
    public const ATTR_USER_ID = 'oauth_user_id';
    public const ATTR_SCOPES = 'oauth_scopes';

    public function __construct(
        protected ContainerInterface $container,
        protected array              $resourceScopes = []
    )
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {


        return $handler->handle($request);
    }


}