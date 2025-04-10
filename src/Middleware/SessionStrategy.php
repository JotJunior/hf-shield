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

use Hyperf\Contract\ConfigInterface;
use Jot\HfShield\Exception\UnauthorizedAccessException;
use Jot\HfShield\Repository\AccessTokenRepository;
use League\OAuth2\Server\CryptTrait;
use League\OAuth2\Server\ResourceServer;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SessionStrategy implements MiddlewareInterface
{
    use BearerTrait;
    use CryptTrait;

    public function __construct(
        protected ContainerInterface $container,
        protected ResourceServer $server,
        protected AccessTokenRepository $repository,
        protected ServerRequestInterface $request,
        protected array $resourceScopes = []
    ) {
        $this->setEncryptionKey($this->container->get(ConfigInterface::class)->get('hf_shield.encryption_key'));
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $token = $request->getCookieParams()['access_token'] ?? null;


        if (! $token) {
            throw new UnauthorizedAccessException();
        }

        $token = $this->decrypt($token);

        $request = $request->withHeader('Authorization', sprintf('Bearer %s', $token));

        $this->validateBearerStrategy($request);

        $userId = $this->request->getAttribute(self::ATTR_USER_ID);
        $userSession = $this->repository->getUserSessionData($userId);

        return $handler->handle(
            $this->request->withAttribute(
                'oauth_session_user',
                $userSession
            )
        );
    }
}
