<?php

declare(strict_types=1);
/**
 * This file is part of the hf_shield module, a package build for Hyperf framework that is responsible for OAuth2 authentication and access control.
 *
 * @author   Joao Zanon <jot@jot.com.br>
 * @link     https://github.com/JotJunior/hf-shield
 * @license  MIT
 */

namespace Jot\HfShield;

use Hyperf\Swagger\HttpServer;
use Jot\HfRepository\Exception\Handler\ControllerExceptionHandler;
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
                        ControllerExceptionHandler::class,
                    ],
                ],
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
                [
                    'id' => 'translations-en',
                    'description' => 'The english translation files for hf_shield.',
                    'source' => __DIR__ . '/../storage/languages/en/hf-shield.php',
                    'destination' => BASE_PATH . '/storage/languages/en/hf-shield.php',
                ],
                [
                    'id' => 'translations-pt-br',
                    'description' => 'The portuguese translation files for hf_shield.',
                    'source' => __DIR__ . '/../storage/languages/pt_BR/hf-shield.php',
                    'destination' => BASE_PATH . '/storage/languages/pt_BR/hf-shield.php',
                ],
            ],
        ];
    }
}
