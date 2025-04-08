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

use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Output\ConsoleOutput;

#[Listener]
class RequiredConfigListener implements ListenerInterface
{
    private const REQUIRED_PACKAGES = [
        'hyperf/etcd' => ['config/autoload/etcd.php'],
        'hyperf/redis' => ['config/autoload/redis.php'],
        'hyperf/rate-limit' => ['config/autoload/rate_limit.php'],
        'jot/hf-elastic' => ['config/autoload/hf_elastic.php'],
        'jot/hf-repository' => ['config/autoload/swagger.php', 'storage/languages/en/hf-repository.php'],
        'jot/hf-validator' => ['storage/languages/en/hf-validator.php'],
    ];

    public function __construct(protected ContainerInterface $container)
    {
    }

    public function listen(): array
    {
        return [
            Event\BeforeServerStart::class,
        ];
    }

    public function process(object $event): void
    {
        $output = new ConsoleOutput();
        $hasMissingRequiredPackages = false;

        foreach (self::REQUIRED_PACKAGES as $package => $files) {
            foreach ($files as $fileName) {
                $hasMissingRequiredPackages = $this->checkAndReportMissingConfiguration($package, $fileName, $output, $hasMissingRequiredPackages);
            }
        }

        if ($hasMissingRequiredPackages) {
            $output->writeln('');
            exit(1);
        }
    }

    private function checkAndReportMissingConfiguration(string $package, string $fileName, ConsoleOutput $output, bool $hasMissingRequiredPackages): bool
    {
        if (! file_exists($fileName)) {
            $output->writeln('');
            $output->writeln(sprintf(
                '<options=bold;fg=red>[ERROR]</> The required packages <options=bold>%s</> are not configured. To proceed, please run the following commands before starting the application:',
                ucfirst($package)
            ));
            $output->writeln('');
            $output->writeln(sprintf('    <options=bold>php bin/hyperf.php vendor:publish %s</>', $package));
            return true;
        }
        return $hasMissingRequiredPackages;
    }
}
