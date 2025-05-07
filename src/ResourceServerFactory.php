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
        $publicKey = $this->config->get('hf_shield.public_key', '');
        if (is_file(BASE_PATH . $publicKey)) {
            $key = file_get_contents(BASE_PATH . $publicKey);
        } else {
            $key = str_replace('\n', "\n", $publicKey);
        }

        return make(CryptKey::class, [
            'keyPath' => $key,
            'passPhrase' => null,
            'keyPermissionsCheck' => false,
        ]);
    }
}
