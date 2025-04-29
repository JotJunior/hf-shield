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
            $metadata['query'] = $this->request?->getQueryParams();
            $metadata['headers'] = $this->request?->getHeaders();
            if(method_exists($this->request, 'getParsedBody')) {
                $metadata['body'] = $this->request?->getParsedBody();
            } elseif (method_exists($this->request, 'getBody')) {
                $metadata['body'] = $this->request?->getBody()?->getContents();
                $metadata['body'] = empty($metadata['body']) ? null : json_decode($metadata['body'], true);
            }
            $metadata['scopes'] = $this->request?->getAttribute('oauth_scopes');
        }
        if (property_exists($this, 'oauthUser') && $this->oauthUser) {
            $metadata['user']['id'] = $this->oauthUser['id'];
            $metadata['user']['name'] = $this->oauthUser['name'];
            $metadata['user']['picture'] = $this->oauthUser['picture'] ?? null;
        }
        if (property_exists($this, 'oauthTokenId') && $this->oauthTokenId) {
            $metadata['access']['token'] = $this->oauthTokenId;
        }
        if (property_exists($this, 'oauthClient') && $this->oauthClient) {
            $metadata['access']['client'] = $this->oauthClient['id'];
            $metadata['access']['tenant'] = $this->oauthClient['tenant']['id'];
        }

        return $metadata;
    }
}
