<?php

namespace Jot\HfShield\Controller;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ContainerInterface;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Jot\HfRepository\RepositoryInterface;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\CryptTrait;

class AbstractController
{

    use CryptTrait;

    protected string $repository;

    private RepositoryInterface $repositoryInstance;

    protected array $config = [];

    public function __construct(
        protected ContainerInterface     $container,
        protected RequestInterface       $request,
        protected ResponseInterface      $response,
        protected AuthorizationServer    $server,
        protected readonly ConfigInterface $configService
    )
    {
        $this->config = $this->configService->get('hf_oauth2');
        $this->setEncryptionKey($this->config['encryption_key']);
        $this->repositoryInstance = $this->container->get($this->repository);
    }

    protected function repository(): RepositoryInterface
    {
        return $this->repositoryInstance;
    }



}