<?php

namespace Jot\HfShield\Controller;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ContainerInterface;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Swagger\Annotation as SA;
use Jot\HfRepository\RepositoryInterface;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\CryptTrait;

#[SA\HyperfServer('http')]
#[SA\Info(
    version: "1.0.0",
    title: "HfShield"
)]
#[SA\SecurityScheme(
    securityScheme: "shieldBearerAuth",
    type: "http",
    in: "header",
    bearerFormat: "JWT",
    scheme: "bearer",
    flows: [
        new SA\Flow(
            tokenUrl: "/oauth/token",
            flow: "bearer",
            scopes: [
                'oauth:client:create',
                'oauth:client:list',
                'oauth:user:create',
                'oauth:user:update',
                'oauth:jwt_signature:list',
                'oauth:jwt_signature:read',
                'oauth:jwt_signature:create',
                'oauth:jwt_signature:delete',
            ]
        )
    ]
)]
#[SA\Schema(schema: "jot.hf-shield.error.response", required: ["result", "error"],
    properties: [
        new SA\Property(property: "result", type: "string", example: "error"),
        new SA\Property(property: "error", type: "string", example: "Error message"),
        new SA\Property(property: "data", type: "string|array", example: null),
    ],
    type: "object"
)]
#[SA\Schema(schema: "jot.hf-shield.auth-error.response", required: ["result", "error"],
    properties: [
        new SA\Property(property: "status_code", type: "integer", example: 401),
        new SA\Property(property: "error", type: "string", example: "The user credentials were incorrect."),
    ],
    type: "object"
)]
class AbstractController
{

    use CryptTrait;

    protected string $repository;
    protected array $config = [];
    private RepositoryInterface $repositoryInstance;

    public function __construct(
        protected ContainerInterface       $container,
        protected RequestInterface         $request,
        protected ResponseInterface        $response,
        protected AuthorizationServer      $server,
        protected readonly ConfigInterface $configService
    )
    {
        $this->config = $this->configService->get('hf_shield');
        $this->setEncryptionKey($this->config['encryption_key']);
        $this->repositoryInstance = $this->container->get($this->repository);
    }

    protected function repository(): RepositoryInterface
    {
        return $this->repositoryInstance;
    }


}