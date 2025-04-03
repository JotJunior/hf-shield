<?php

declare(strict_types=1);
/**
 * This file is part of hf-shield.
 *
 * @link     https://github.com/JotJunior/hf-shield
 * @contact  hf-shield@jot.com.br
 * @license  MIT
 */

namespace Jot\HfShield\Middleware;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\SessionInterface;
use Hyperf\Session\Middleware\SessionMiddleware;
use Hyperf\Session\SessionManager;
use Jot\HfShield\Exception\UnauthorizedAccessException;
use Jot\HfShield\Repository\AccessTokenRepository;
use League\OAuth2\Server\ResourceServer;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SessionStrategy extends SessionMiddleware implements MiddlewareInterface
{
    use BearerTrait;

    public function __construct(
        protected ContainerInterface $container,
        protected ResourceServer $server,
        protected AccessTokenRepository $repository,
        protected ServerRequestInterface $request,
        protected SessionInterface $session,
        protected array $resourceScopes = []
    ) {
        parent::__construct(
            $container->get(SessionManager::class),
            $container->get(ConfigInterface::class)
        );
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = parent::process($request, $handler);

        $token = $request->getCookieParams()['access_token'] ?? null;
        if (! $token) {
            throw new UnauthorizedAccessException();
        }

        $request = $request->withAddedHeader('Authorization', sprintf('Bearer %s', $token));
        $this->validateBearerStrategy($request, $handler);

        return $response;
    }
}
