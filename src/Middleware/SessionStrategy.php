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
use Hyperf\Logger\LoggerFactory;
use Jot\HfShield\Exception\MissingAccessTokenException;
use Jot\HfShield\Repository\AccessTokenRepository;
use League\OAuth2\Server\CryptTrait;
use League\OAuth2\Server\ResourceServer;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerAwareTrait;

class SessionStrategy implements MiddlewareInterface
{
    use BearerTrait;
    use CryptTrait;
    use LoggerAwareTrait;

    public function __construct(
        protected ContainerInterface $container,
        protected ResourceServer $server,
        protected AccessTokenRepository $repository,
        protected ServerRequestInterface $request,
        LoggerFactory $loggerFactory,
        protected array $resourceScopes = []
    ) {
        $this->setEncryptionKey($this->container->get(ConfigInterface::class)->get('hf_shield.encryption_key', ''));
        $this->setLogger($loggerFactory->get('session', 'elastic'));
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $token = $request->getCookieParams()['access_token'] ?? null;

        if (! $token) {
            $this->logger->warning('Access token not found in cookie', $this->collectMetadata());
            throw new MissingAccessTokenException();
        }

        $token = $this->decrypt($token);

        $request = $request->withHeader('Authorization', sprintf('Bearer %s', $token));

        $this->validateBearerStrategy($request);

        $this->logRequest();

        return $handler->handle(
            $this->request
                ->withAttribute('oauth_session_user', $this->getOauthUser())
                ->withQueryParams([...$this->request->getQueryParams(), '_tenant_id' => $this->getOauthUser()['tenant']['id'], '_user_id' => $this->getOauthUser()['id']])
        );
    }
}
