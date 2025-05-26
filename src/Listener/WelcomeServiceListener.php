<?php

declare(strict_types=1);
/**
 * This file is part of the hf_shield module, a package build for Hyperf framework that is responsible for OAuth2 authentication and access control.
 *
 * @author   Joao Zanon <jot@jot.com.br>
 * @link     https://github.com/JotJunior/hf-shield
 * @license  MIT
 */

namespace Jot\HfShield\Listener;

use Hyperf\Amqp\Producer;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Jot\HfShield\Amqp\Producer\Welcome;
use Jot\HfShield\Event\OtpEvent;
use Psr\Container\ContainerInterface;

#[Listener]
class WelcomeServiceListener implements ListenerInterface
{
    public function __construct(
        protected ContainerInterface $container,
        protected Producer $producer,
        protected ConfigInterface $config
    ) {
    }

    public function listen(): array
    {
        return [
            OtpEvent::class,
        ];
    }

    public function process(object $event): void
    {
        $params = [
            'recipient' => sprintf('whatsapp:%s', preg_replace('/[^0-9+]/', '', $event->recipient)),
            'variables' => ['1' => $event->name],
            'content_sid' => $this->config->get('twilio.content_sid_welcome'),
        ];

        $this->producer->produce(
            producerMessage: new Welcome($params)
        );
    }
}
