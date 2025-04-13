<?php

declare(strict_types=1);
/**
 * This file is part of the hf_shield module, a package build for Hyperf framework that is responsible for OAuth2 authentication and access control.
 *
 * @author   Joao Zanon <jot@jot.com.br>
 * @link     https://github.com/JotJunior/hf-shield
 * @license  MIT
 */

namespace Jot\HfShield\Command;

use Elasticsearch\Client;
use Hyperf\Command\Annotation\Command;
use Hyperf\Contract\ConfigInterface;
use Jot\HfElastic\ClientBuilder;
use Psr\Container\ContainerInterface;
use stdClass;
use Symfony\Component\Console\Input\InputArgument;
use Throwable;

use function Hyperf\Translation\__;

#[Command]
class SetupLoggerCommand extends AbstractCommand
{
    protected Client $client;

    public function __construct(protected ContainerInterface $container, private ConfigInterface $config)
    {
        parent::__construct('oauth:logger');
        $this->client = $this->container->get(ClientBuilder::class)->build();
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription(__('hf-shield.setup_logger_description'));
        $this->addArgument('setup', InputArgument::REQUIRED, __('hf-shield.data_stream'));
        $this->addUsage('oauth:logger setup');
    }

    public function handle()
    {
        $elasticConfig = $this->config->get('hf_elastic');
        if (empty($elasticConfig['data_stream'])) {
            $this->warning(__('hf-shield.no_data_stream_configured'));
            return;
        }

        $alias = sprintf('%s-hf-shield-logger', $elasticConfig['prefix']);
        $elasticConfig['data_stream']['body']['template']['aliases'][$alias] = new stdClass();
        $elasticConfig['data_stream']['body']['index_patterns'] = [sprintf('%s-hf-shield-logs*', $elasticConfig['prefix'])];

        try {
            $this->client->indices()->putIndexTemplate($elasticConfig['data_stream']);
        } catch (Throwable $th) {
            $this->failed($th->getMessage());
            return;
        }

        $this->success(__('hf-shield.logger_setup_successfully'));
    }
}
