<?php

declare(strict_types=1);
/**
 * This file is part of the hf_shield module, a package build for Hyperf framework that is responsible for OAuth2 authentication and access control.
 *
 * @author   Joao Zanon <jot@jot.com.br>
 * @link     https://github.com/JotJunior/hf-shield
 * @license  MIT
 */
use function Hyperf\Support\env;

return [
    'hosts' => explode(',', env('ELASTICSEARCH_HOSTS', 'http://localhost:9200')),
    'username' => env('ELASTICSEARCH_USERNAME', 'elastic'),
    'api_id' => env('ELASTICSEARCH_API_ID', ''),
    'api_key' => env('ELASTICSEARCH_API_KEY', ''),
    'password' => env('ELASTICSEARCH_PASSWORD', ''),
    'prefix' => env('ELASTICSEARCH_INDEX_PREFIX', ''),
    'dynamic' => env('ELASTICSEARCH_INDEX_DYNAMIC', 'strict'),

    /*
     * For detailed documentation and best practices regarding:
     * - 'number_of_shards' and 'number_of_replicas': https://www.elastic.co/guide/en/elasticsearch/reference/current/important-settings.html
     * - 'dynamic': https://www.elastic.co/guide/en/elasticsearch/reference/current/dynamic.html
     * - 'analysis' (normalizer, filters, etc.): https://www.elastic.co/guide/en/elasticsearch/reference/current/analysis.html
     *
     * Developers can visit these official documentation URLs to consult or reference the best configurations to use for their indices.
     */
    'settings' => [
        'index' => [
            'number_of_shards' => (int) env('ELASTICSEARCH_SHARDS', 1),
            'number_of_replicas' => (int) env('ELASTICSEARCH_REPLICAS', 1),
        ],
        'analysis' => [
            'normalizer' => [
                'normalizer_ascii_lower' => [
                    'type' => 'custom',
                    'char_filter' => [],
                    'filter' => [
                        'asciifolding',
                        'lowercase',
                    ],
                ],
            ],
        ],
    ],
];
