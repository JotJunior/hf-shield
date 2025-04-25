<?php

namespace Jot\HfShield;

use Psr\Log\LoggerAwareTrait;

trait LoggerContextCollector
{
    use LoggerAwareTrait;


    protected function log(?string $message = null, string $level = 'info'): void
    {
        $context = $this->collectMetadata();
        $this->logger?->{$level}($message, $context);
    }

    protected function collectMetadata(): array
    {
        $metadata = [];
        if (property_exists($this, 'request')) {
            $metadata['server_params'] = $this->request?->getServerParams();
            $metadata['query'] = $this->request?->getServerParams();
            $metadata['headers'] = $this->request?->getHeaders();
            $metadata['body'] = $this->request?->getBody()?->getContents();
            $metadata['body'] = empty($metadata['body']) ? null : json_decode($metadata['body'], true);
            $metadata['scopes'] = $this->request?->getAttribute('oauth_scopes');
        }
        if (property_exists($this, 'oauthUser')) {
            $metadata['user']['id'] = $this->oauthUser['id'];
            $metadata['user']['name'] = $this->oauthUser['name'];
            $metadata['user']['picture'] = $this->oauthUser['picture'];
        }
        if (property_exists($this, 'oauthTokenId')) {
            $metadata['access']['token'] = $this->oauthTokenId;
        }
        if (property_exists($this, 'oauthClient')) {
            $metadata['access']['client'] = $this->oauthClient['id'];
            $metadata['access']['tenant'] = $this->oauthClient['tenant']['id'];
        }

        return $metadata;
    }
}