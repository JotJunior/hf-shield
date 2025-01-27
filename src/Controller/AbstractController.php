<?php

namespace Jot\HfOAuth2\Controller;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ContainerInterface;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use League\OAuth2\Server\AuthorizationServer;

class AbstractController
{

    protected array $config = [];

    protected AuthorizationServer $server;

    public function __construct(protected ContainerInterface $container, protected RequestInterface $request, protected ResponseInterface $response)
    {
        $this->config = $container->get(ConfigInterface::class)->get('hf_oauth2', []);
        $this->server = $container->get(AuthorizationServer::class);
    }

}