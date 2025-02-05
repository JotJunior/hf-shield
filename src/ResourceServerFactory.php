<?php

declare(strict_types=1);

namespace Jot\HfShield;

use Hyperf\Di\Annotation\Inject;
use Hyperf\Contract\ConfigInterface;
use Jot\HfShield\Repository\AccessTokenRepository;
use League\OAuth2\Server\CryptTrait;
use Psr\Container\ContainerInterface;
use League\OAuth2\Server\ResourceServer;
use League\OAuth2\Server\CryptKey;
use function Hyperf\Support\make;

class ResourceServerFactory
{

    use CryptTrait;

    #[Inject]
    protected ContainerInterface $container;

    #[Inject]
    protected ConfigInterface $config;

    /**
     * @param ContainerInterface $container
     * @param ConfigInterface $config
     * @return void
     */
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
        $key = str_replace('\\n', "\n", $this->config->get('hf_shield.public_key'));
        return make(CryptKey::class, [
            'keyPath' => $key,
            'passPhrase' => null,
            'keyPermissionsCheck' => false
        ]);
    }


}