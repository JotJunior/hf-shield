<?php

namespace Jot\HfOAuth2;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Router\DispatcherFactory;
use League\OAuth2\Server\AuthorizationServer;
use Psr\Container\ContainerInterface;

class ConfigProvider
{

    #[Inject]
    protected ContainerInterface $container;

    public function __invoke(): array
    {
        $this->generateMigrationFiles();
        return [
            'dependencies' => [
                AuthorizationServer::class => AuthorizationServerFactory::class,
                DispatcherFactory::class => DispatcherFactory::class,
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
                    'id' => 'migration',
                    'description' => 'The migration for hf_oauth2.',
                    'source' => __DIR__ . '/../migrations/elasticsearch',
                    'destination' => BASE_PATH . '/migrations/elasticsearch',
                ],
            ],
        ];
    }

    private function generateMigrationFiles(): void
    {
        $config = $this->container->get(ConfigInterface::class)->get('hf_elastic');
        $prefix = ($config['prefix'] ?? null) ? sprintf('%s_', $config['prefix']) : '';

        foreach (glob(__DIR__ . '/../migrations/stubs/*.stub') as $file) {
            $content = file_get_contents($file);
            $migration = str_replace('.stub', '.php', basename($file, '.stub'));
            $filename = sprintf('%s/../migrations/elasticsearch/%s', __DIR__, $migration);
            file_put_contents($filename, str_replace('{{prefix}}', $prefix, $content));
        }
    }
}