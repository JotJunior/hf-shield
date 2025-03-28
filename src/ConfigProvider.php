<?php

namespace Jot\HfShield;

use Hyperf\Swagger\HttpServer;
use Jot\HfRepository\Exception\Handler\ControllerExceptionHandler;
use Jot\HfRepository\RequiredConfigListener;
use Jot\HfRepository\Swagger\SwaggerHttpServer;
use Jot\HfShield\Command\OAuthScopeCommand;
use Jot\HfShield\Command\OAuthUserCommand;
use Jot\HfShield\Exception\Handler\AuthExceptionHandler;
use Jot\HfValidator\BootValidatorsListener;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\ResourceServer;

class ConfigProvider
{

    public function __invoke(): array
    {
        return [
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'listeners' => [
                AllowedScopesListener::class,
                BootValidatorsListener::class,
                RequiredConfigListener::class,
            ],
            'dependencies' => [
                HttpServer::class => SwaggerHttpServer::class,
                AuthorizationServer::class => AuthorizationServerFactory::class,
                ResourceServer::class => ResourceServerFactory::class,
            ],
            'commands' => [
                OAuthScopeCommand::class,
                OAuthUserCommand::class,
            ],
            'exceptions' => [
                'handler' => [
                    'http' => [
                        AuthExceptionHandler::class,
                        ControllerExceptionHandler::class
                    ]
                ]
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for hf_shield.',
                    'source' => __DIR__ . '/../publish/hf_shield.php',
                    'destination' => BASE_PATH . '/config/autoload/hf_shield.php',
                ],
                [
                    'id' => 'config',
                    'description' => 'The config for hf_elastic.',
                    'source' => __DIR__ . '/../publish/hf_elastic.php',
                    'destination' => BASE_PATH . '/config/autoload/hf_elastic.php',
                ],
                [
                    'id' => 'config',
                    'description' => 'The config for hf_session.',
                    'source' => __DIR__ . '/../publish/hf_session.php',
                    'destination' => BASE_PATH . '/config/autoload/hf_session.php',
                ],
                [
                    'id' => 'migrations',
                    'description' => 'The elasticsearch migration files for hf_shield.',
                    'source' => __DIR__ . '/../migrations',
                    'destination' => BASE_PATH . '/migrations',
                ],
                [
                    'id' => 'dot-env-example',
                    'description' => 'The example .env file for hf_shield.',
                    'source' => __DIR__ . '/../.env.shield',
                    'destination' => BASE_PATH . '/.env.shield',
                ],
            ],
        ];
    }

}