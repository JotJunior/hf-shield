<?php

declare(strict_types=1);

namespace Jot\HfOAuth2\Command;

use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

#[Command]
class CreateClientCommand extends HyperfCommand
{
    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('oauth:client');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Create an OAuth client');
        $this->addArgument('name', InputArgument::REQUIRED, 'Client name');
        $this->addArgument('redirect-uri', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'Redirect URI');
        $this->addOption('user-id', 'U', InputOption::VALUE_OPTIONAL, 'User ID');
        $this->addOption('confidential', 'P', InputOption::VALUE_NONE, 'Define if client is personal access client');
        $this->addOption('allowed-grant-type', 'A', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Allowed grant type');
        $this->addOption('allowed-scope', 'A', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Allowed scope');
    }

    public function handle()
    {
        $this->line('Hello Hyperf!', 'info');
    }
}
