<?php

declare(strict_types=1);
/**
 * This file is part of hf-shield.
 *
 * @link     https://github.com/JotJunior/hf-shield
 * @contact  hf-shield@jot.com.br
 * @license  MIT
 */

namespace Jot\HfShield;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Annotation\Inject;
use Jot\HfShield\Repository\AccessTokenRepository;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\CryptTrait;
use League\OAuth2\Server\ResourceServer;
use Psr\Container\ContainerInterface;

use function Hyperf\Support\make;

class ResourceServerFactory
{
    use CryptTrait;

    #[Inject]
    protected ContainerInterface $container;

    #[Inject]
    protected ConfigInterface $config;

    public function __construct(ContainerInterface $container, ConfigInterface $config)
    {
        $this->container = $container;
        $this->config = $config;
    }

    public function __invoke()
    {
        return new ResourceServer(make(AccessTokenRepository::class), $this->makeCryptKey());
    }

    protected function makeCryptKey(): CryptKey
    {
        $key = str_replace('\n', "\n", $this->config->get('hf_shield.public_key'));
        return make(CryptKey::class, [
            'keyPath' => $key,
            'passPhrase' => null,
            'keyPermissionsCheck' => false,
        ]);
    }
}
