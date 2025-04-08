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

use Hyperf\Contract\ConfigInterface;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Output\ConsoleOutput;

#[Listener]
class RequiredConfigListener implements ListenerInterface
{
    private const REQUIRED_PACKAGES = [
        'hyperf/etcd',
        'hyperf/redis',
        'hyperf/rate-limit',
        'jot/hf-elastic',
        'jot/hf-repository',
        'jot/hf-validator',
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

        foreach (self::REQUIRED_PACKAGES as $package) {
            $hasMissingRequiredPackages = $this->checkAndReportMissingConfiguration($package, $output, $hasMissingRequiredPackages);
        }

        if ($hasMissingRequiredPackages) {
            $output->writeln('');
            exit(1);
        }
    }

    private function checkAndReportMissingConfiguration(string $package, ConsoleOutput $output, bool $hasMissingRequiredPackages): bool
    {
        $configService = $this->container->get(ConfigInterface::class);
        $configName = str_replace('-', '_', explode('/', $package)[1]);

        if (! $configService->get($configName)) {
            if (! $hasMissingRequiredPackages) {
                $output->writeln('');
                $output->writeln(sprintf(
                    '<options=bold;fg=red>[ERROR]</> The required packages <options=bold>%s</> are not configured. To proceed, please run the following commands before starting the application:',
                    ucfirst($package)
                ));
                $output->writeln('');
            }
            $output->writeln(sprintf('    <options=bold>php bin/hyperf.php vendor:publish %s</>', $package));
            return true;
        }

        return $hasMissingRequiredPackages;
    }
}
