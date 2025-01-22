<?php

namespace Jot\HfOAuth2;

use Hyperf\HttpServer\Router\DispatcherFactory as Dispatcher;
use League\OAuth2\Server\AuthorizationServer;

class ConfigProvider
{

    public function __invoke(): array
    {
        return [
            'dependencies' => [
                AuthorizationServer::class => AuthorizationServerFactory::class,
                Dispatcher::class => DispatcherFactory::class,
            ],
            'listeners' => [],
            'commands' => [],
            'annotations' => [],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for hf_oauth2.',
                    'source' => __DIR__ . '/../publish/hf_oauth2.php',
                    'destination' => BASE_PATH . '/config/autoload/hf_oauth2.php',
                ],
                [
                    'id' => 'migrations',
                    'description' => 'The elasticsearch migration files for hf_oauth2.',
                    'source' => __DIR__ . '/../migrations',
                    'destination' => BASE_PATH . '/migrations',
                ],
            ],
        ];
    }

}