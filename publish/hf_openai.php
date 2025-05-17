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
    'base_uri' => env('OPENAI_BASE_URI', 'api.openai.com/v1'),
    'api_key' => env('OPENAI_API_KEY', ''),
    'organization' => env('OPENAI_ORGANIZATION'),
    'request_timeout' => (int) env('OPENAI_REQUEST_TIMEOUT', 30),
];
