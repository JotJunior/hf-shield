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
use Jot\HfShield\Amqp\Producer\Otp;
use Jot\HfShield\Event\OtpEvent;
use Psr\Container\ContainerInterface;

#[Listener]
class OtpServiceListener implements ListenerInterface
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
            'variables' => ['1' => $event->code],
            'content_sid' => $this->config->get('twilio.content_sid_otp'),
        ];

        $this->producer->produce(
            producerMessage: new Otp($params)
        );
    }
}
