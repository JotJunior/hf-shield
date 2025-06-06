<?php

declare(strict_types=1);
/**
 * This file is part of the hf_shield module, a package build for Hyperf framework that is responsible for OAuth2 authentication and access control.
 *
 * @author   Joao Zanon <jot@jot.com.br>
 * @link     https://github.com/JotJunior/hf-shield
 * @license  MIT
 */

namespace Jot\HfShield\Middleware;

use Jot\HfShield\Exception\UnauthorizedAccessException;
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
        protected array $resourceScopes = []
    ) {
    }

    /**
     * @todo Implements the JwtSigned strategy
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        throw new UnauthorizedAccessException();
    }
}
