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
use Jot\HfShield\Exception\UnauthorizedAccessException;
use Jot\HfShield\Repository\AccessTokenRepository;
use League\OAuth2\Server\CryptTrait;
use League\OAuth2\Server\ResourceServer;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class SessionStrategy implements MiddlewareInterface
{
    use BearerTrait;
    use CryptTrait;

    protected array $user = [];

    protected ?string $tokenId = null;

    protected LoggerInterface $logger;

    public function __construct(
        protected ContainerInterface $container,
        protected ResourceServer $server,
        protected AccessTokenRepository $repository,
        protected ServerRequestInterface $request,
        protected LoggerFactory $loggerFactory,
        protected array $resourceScopes = []
    ) {
        $this->setEncryptionKey($this->container->get(ConfigInterface::class)->get('hf_shield.encryption_key'));
        $this->logger = $this->loggerFactory->get('session', 'elastic');
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $token = $request->getCookieParams()['access_token'] ?? null;

        if (! $token) {
            $this->logger->warning('Access token not found in cookie');
            throw new UnauthorizedAccessException();
        }

        $token = $this->decrypt($token);

        $request = $request->withHeader('Authorization', sprintf('Bearer %s', $token));

        $this->validateBearerStrategy($request);

        $this->user = $this->repository->getUserSessionData($this->request->getAttribute(self::ATTR_USER_ID));
        $this->tokenId = $this->request->getAttribute(self::ATTR_ACCESS_TOKEN_ID);

        $metadata = $this->collectMetadata();
        $this->logger->info(message: $metadata['message'], context: $metadata);

        return $handler->handle(
            $this->request->withAttribute(
                'oauth_session_user',
                $this->user
            )
        );
    }
}
