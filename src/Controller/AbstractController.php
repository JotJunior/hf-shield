<?php

declare(strict_types=1);
/**
 * This file is part of the hf_shield module, a package build for Hyperf framework that is responsible for OAuth2 authentication and access control.
 *
 * @author   Joao Zanon <jot@jot.com.br>
 * @link     https://github.com/JotJunior/hf-shield
 * @license  MIT
 */

namespace Jot\HfShield\Controller;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ContainerInterface;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Swagger\Annotation as SA;
use Jot\HfRepository\RepositoryInterface;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\CryptTrait;
use Psr\Log\LoggerAwareTrait;

#[SA\SecurityScheme(
    securityScheme: 'shieldBearerAuth',
    type: 'http',
    in: 'header',
    bearerFormat: 'JWT',
    scheme: 'bearer'
)]
#[SA\Schema(
    schema: 'jot.hf-shield.error.response',
    required: ['result', 'error'],
    properties: [
        new SA\Property(property: 'result', type: 'string', example: 'error'),
        new SA\Property(property: 'error', type: 'string', example: 'Error message'),
        new SA\Property(property: 'data', type: 'string|array', example: null),
    ],
    type: 'object'
)]
#[SA\Schema(
    schema: 'jot.hf-shield.auth-error.response',
    required: ['result', 'error'],
    properties: [
        new SA\Property(property: 'status_code', type: 'integer', example: 401),
        new SA\Property(property: 'error', type: 'string', example: 'The user credentials were incorrect.'),
    ],
    type: 'object'
)]
class AbstractController
{
    use CryptTrait;
    use LoggerAwareTrait;

    protected string $repository;

    protected array $config = [];

    private RepositoryInterface $repositoryInstance;

    public function __construct(
        protected ContainerInterface $container,
        protected RequestInterface $request,
        protected ResponseInterface $response,
        protected AuthorizationServer $server,
        protected readonly ConfigInterface $configService,
        LoggerFactory $loggerFactory
    ) {
        $this->config = $this->configService->get('hf_shield');
        $this->setEncryptionKey($this->config['encryption_key']);
        $this->repositoryInstance = $this->container->get($this->repository);
        $this->setLogger($loggerFactory->get('shield', 'elastic'));
    }

    protected function repository(): RepositoryInterface
    {
        return $this->repositoryInstance;
    }
}
