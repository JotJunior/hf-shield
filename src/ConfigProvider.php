<?php

namespace Jot\HfOAuth2;

use Hyperf\HttpServer\Router\DispatcherFactory as Dispatcher;
use League\OAuth2\Server\AuthorizationServer;

class ConfigProvider
{

    public function __invoke(): array
    {
        $this->generateMigrationFiles();
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
            ],
        ];
    }

    private function generateMigrationFiles(): void
    {
        if (!file_exists(BASE_PATH . '/config/autoload/hf_elastic.php')) {
            return;
        }
        $config = include BASE_PATH . '/config/autoload/hf_elastic.php';
        $prefix = ($config['prefix'] ?? null) ? sprintf('%s_', $config['prefix']) : '';
        if (!is_dir(BASE_PATH . '/migrations/elasticsearch')) {
            mkdir(BASE_PATH . '/migrations/elasticsearch', 0755, true);
        }
        foreach (glob(__DIR__ . '/../migrations/stubs/*.stub') as $file) {
            $content = file_get_contents($file);
            $migration = str_replace('.stub', '.php', basename($file));
            $filename = sprintf('%s/migrations/elasticsearch/20250120160000-create-%s', BASE_PATH, $migration);
            file_put_contents($filename, str_replace('{{prefix}}', $prefix, $content));
        }
    }
}