<?php

namespace Jot\HfShield\Service;

use Hyperf\Contract\ConfigInterface;
use Psr\Log\LoggerAwareTrait;
use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Client;

class WhatsappService
{

    use LoggerAwareTrait;

    protected Client $sender;

    private array $credentials;

    public function __construct(
        private readonly ConfigInterface $config
    )
    {
        $credentials = $this->config->get('twilio', []);
        $this->credentials = $credentials;
        $this->sender = new Client($credentials['sid'], $credentials['token']);
    }

    public function send(string $to, array $parameters): void
    {
        try {
            $result = $this->sender->messages->create(
                to: $to,
                options: [
                    'from' => empty($parameters['from']) ? $this->credentials['from'] : $parameters['from'],
                    'contentVariables' => json_encode($parameters['variables']),
                    'contentSid' => $parameters['content_sid'],
                    'messagingServiceSid' => empty($parameters['messaging_service']) ? $this->credentials['messaging_service'] : $parameters['messaging_service'],
                ]
            );
            $this->logger?->info(sprintf('OTP sent to %s', $to), $result->toArray());
        } catch (TwilioException $th) {
            echo $th->getMessage();
            $this->logger?->error($th->getMessage());
        }
    }

}