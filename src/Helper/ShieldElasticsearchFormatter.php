<?php

declare(strict_types=1);
/**
 * This file is part of the hf_shield module, a package build for Hyperf framework that is responsible for OAuth2 authentication and access control.
 *
 * @author   Joao Zanon <jot@jot.com.br>
 * @link     https://github.com/JotJunior/hf-shield
 * @license  MIT
 */

namespace Jot\HfShield\Helper;

use Monolog\Formatter\ElasticsearchFormatter;

class ShieldElasticsearchFormatter extends ElasticsearchFormatter
{
    protected function getDocument(array $record): array
    {
        $record['@timestamp'] = $record['datetime'];
        $record['server_params'] = $record['context']['server_params'];
        $record['user'] = $record['context']['user'];
        unset($record['context']['server_params'], $record['context']['user'], $record['context']['message']);

        return parent::getDocument($record);
    }
}
