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
            if (method_exists($this->request, 'getParsedBody')) {
                $metadata['body'] = $this->request?->getParsedBody();
            } elseif (method_exists($this->request, 'getBody')) {
                $metadata['body'] = $this->request?->getBody()?->getContents();
                $metadata['body'] = empty($metadata['body']) ? null : json_decode($metadata['body'], true);
            }
            $metadata['scopes'] = $this->request?->getAttribute('oauth_scopes');
        }
        if (method_exists($this, 'getOauthUser') && $this->getOauthUser()) {
            $metadata['user']['id'] = $this->getOauthUser()['id'];
            $metadata['user']['name'] = $this->getOauthUser()['name'];
            $metadata['user']['picture'] = $this->getOauthUser()['picture'] ?? null;
        }
        if (property_exists($this, 'oauthTokenId') && $this->oauthTokenId) {
            $metadata['access']['token'] = $this->oauthTokenId;
        }
        if (method_exists($this, 'getOauthClient') && $this->getOauthClient()) {
            $metadata['access']['client'] = $this->getOauthClient()['id'];
            $metadata['access']['tenant'] = $this->getOauthClient()['tenant']['id'];
        }

        return $metadata;
    }
}
