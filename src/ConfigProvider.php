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

use Aws\S3\S3Client;
use Aws\S3\S3ClientInterface;
use Hyperf\Cache\AnnotationManager;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Swagger\HttpServer;
use Jot\HfElastic\ClientBuilder;
use Jot\HfRepository\Exception\Handler\ControllerExceptionHandler;
use Jot\HfRepository\Swagger\SwaggerHttpServer;
use Jot\HfShield\Command\OAuthKeyPairsCommand;
use Jot\HfShield\Command\OAuthScopeCommand;
use Jot\HfShield\Command\OAuthUserCommand;
use Jot\HfShield\Command\SetupLoggerCommand;
use Jot\HfShield\Exception\Handler\AuthExceptionHandler;
use Jot\HfShield\Helper\CacheAnnotationManagerWrapper;
use Jot\HfShield\Helper\ShieldElasticsearchFormatter;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\ResourceServer;
use Monolog\Formatter\ElasticsearchFormatter;
use Monolog\Handler\ElasticsearchHandler;
use Monolog\Level;
use stdClass;
use Symfony\Component\Serializer\SerializerInterface;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\AttestationStatement\NoneAttestationStatementSupport;
use Webauthn\Denormalizer\WebauthnSerializerFactory;
use function Hyperf\Support\env;
use function Hyperf\Support\make;

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
            'swagger' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'listeners' => [],
            'dependencies' => [
                HttpServer::class => SwaggerHttpServer::class,
                AuthorizationServer::class => AuthorizationServerFactory::class,
                ResourceServer::class => ResourceServerFactory::class,
                AnnotationManager::class => CacheAnnotationManagerWrapper::class,
                ElasticsearchHandler::class => function (ContainerInterface $container) {
                    $client = $container->get(ClientBuilder::class)->build();
                    return new ElasticsearchHandler(
                        client: $client,
                        options: [
                            'op_type' => 'create',
                        ],
                        level: Level::Info,
                        bubble: true,
                    );
                },
                ElasticsearchFormatter::class => function (ContainerInterface $container) {
                    $indexPrefix = $container->get(ConfigInterface::class)->get('hf_elastic.prefix');
                    return new ShieldElasticsearchFormatter(
                        index: sprintf('%s-hf-shield-logs', $indexPrefix),
                        type: '_doc',
                        encryptionKey: $container->get(ConfigInterface::class)->get('hf_shield.encryption_key', ''),
                    );
                },
                SerializerInterface::class => function () {
                    $attestationStatementSupportManager = AttestationStatementSupportManager::create();
                    $attestationStatementSupportManager->add(NoneAttestationStatementSupport::create());

                    $factory = make(WebauthnSerializerFactory::class, [
                        'attestationStatementSupportManager' => $attestationStatementSupportManager,
                    ]);

                    return $factory->create();
                },
                S3ClientInterface::class => function (ContainerInterface $container) {
                    $config = $container->get(ConfigInterface::class)->get('hf_shield', []);
                    return new S3Client([
                        'version' => 'latest',
                        'region' => $config['s3_bucket_region'],
                        'endpoint' => $config['s3_bucket_url'],
                        'credentials' => [
                            'key' => $config['s3_bucket_access_key'],
                            'secret' => $config['s3_bucket_secret_key'],
                        ],
                        'use_path_style_endpoint' => true,
                    ]);
                },
            ],
            'commands' => [
                OAuthKeyPairsCommand::class,
                OAuthScopeCommand::class,
                OAuthUserCommand::class,
                SetupLoggerCommand::class,
            ],
            'exceptions' => [
                'handler' => [
                    'http' => [
                        AuthExceptionHandler::class,
                        ControllerExceptionHandler::class,
                    ],
                ],
            ],
            'logger' => [
                'elastic' => [
                    'handler' => [
                        'class' => ElasticsearchHandler::class,
                        'constructor' => [],
                    ],
                    'formatter' => [
                        'class' => ElasticsearchFormatter::class,
                        'constructor' => [],
                    ],
                ],
            ],
            'hf_elastic' => [
                'data_stream' => [
                    'name' => 'hf-shield-log-template',
                    'body' => [
                        'index_patterns' => ['hf-shield-logs*'],
                        'data_stream' => new stdClass(),
                        'template' => [
                            'settings' => [
                                'number_of_shards' => env('ELASTICSEARCH_SHARDS', 3),
                                'number_of_replicas' => env('ELASTICSEARCH_REPLICAS', 1),
                                'mode' => 'logsdb',
                            ],
                            'mappings' => [
                                'properties' => [
                                    '@timestamp' => ['type' => 'date_nanos'],
                                    'datetime' => ['type' => 'date', 'format' => 'strict_date_optional_time||epoch_millis'],
                                    'channel' => ['type' => 'keyword'],
                                    'level' => ['type' => 'integer'],
                                    'level_name' => ['type' => 'keyword'],
                                    'message' => ['type' => 'text'],
                                    'context' => ['type' => 'text'],
                                    'extra' => ['type' => 'object', 'dynamic' => true,
                                    ],
                                    'server_params' => [
                                        'properties' => [
                                            'query_string' => ['type' => 'keyword'],
                                            'request_method' => ['type' => 'keyword'],
                                            'request_uri' => ['type' => 'keyword'],
                                            'path_info' => ['type' => 'keyword'],
                                            'request_time' => ['type' => 'long'],
                                            'request_time_float' => ['type' => 'double'],
                                            'server_protocol' => ['type' => 'keyword'],
                                            'server_port' => ['type' => 'long'],
                                            'remote_port' => ['type' => 'long'],
                                            'remote_addr' => ['type' => 'ip'],
                                            'server_addr' => ['type' => 'ip'],
                                            'server_name' => ['type' => 'keyword'],
                                            'server_software' => ['type' => 'keyword'],
                                        ],
                                    ],
                                    'request' => [
                                        'properties' => [
                                            'request_method' => ['type' => 'keyword'],
                                            'request_uri' => ['type' => 'keyword'],
                                            'path_info' => ['type' => 'keyword'],
                                            'request_time' => ['type' => 'long'],
                                            'request_time_float' => ['type' => 'double'],
                                            'server_protocol' => ['type' => 'keyword'],
                                            'server_port' => ['type' => 'long'],
                                            'remote_port' => ['type' => 'long'],
                                            'remote_addr' => ['type' => 'ip'],
                                            'master_time' => ['type' => 'long'],
                                        ],
                                    ],
                                    'user' => [
                                        'properties' => [
                                            'id' => ['type' => 'keyword'],
                                            'name' => ['type' => 'keyword'],
                                            'picture' => ['type' => 'keyword'],
                                        ],
                                    ],
                                    'access' => [
                                        'properties' => [
                                            'token_id' => ['type' => 'keyword'],
                                            'scope' => ['type' => 'keyword'],
                                            'client' => ['type' => 'keyword'],
                                            'tenant' => ['type' => 'keyword'],
                                        ],
                                    ],
                                ],
                            ],
                            'lifecycle' => [
                                'enabled' => env('ELASTICSEARCH_LOGGER_LIFECYCLE', true),
                                'data_retention' => env('ELASTICSEARCH_LOGGER_RETENTION', '7d'),
                            ],
                        ],
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
                    'id' => 'session',
                    'description' => 'The config for hf_session.',
                    'source' => __DIR__ . '/../publish/hf_session.php',
                    'destination' => BASE_PATH . '/config/autoload/hf_session.php',
                ],
                [
                    'id' => 's3_bucket',
                    'description' => 'The config for s3_bucket.',
                    'source' => __DIR__ . '/../publish/hf_s3_bucket.php',
                    'destination' => BASE_PATH . '/config/autoload/hf_s3_bucket.php',
                ],
                [
                    'id' => 'webauthn',
                    'description' => 'The config for webauthn.',
                    'source' => __DIR__ . '/../publish/hf_webauthn.php',
                    'destination' => BASE_PATH . '/config/autoload/hf_webauthn.php',
                ],
                [
                    'id' => 'openai',
                    'description' => 'The config for openai.',
                    'source' => __DIR__ . '/../publish/hf_openai.php',
                    'destination' => BASE_PATH . '/config/autoload/hf_openai.php',
                ],
                [
                    'id' => 'twilio',
                    'description' => 'The config for twilio.',
                    'source' => __DIR__ . '/../publish/twilio.php',
                    'destination' => BASE_PATH . '/config/autoload/twilio.php',
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
                    'id' => 'translations-es',
                    'description' => 'The spanish translation files for hf_shield.',
                    'source' => __DIR__ . '/../storage/languages/es/hf-shield.php',
                    'destination' => BASE_PATH . '/storage/languages/es/hf-shield.php',
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
