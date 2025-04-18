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
    'redirect_login' => env('HF_SESSION_REDIRECT_LOGIN', '/sign-in'),
    'redirect_error' => env('HF_SESSION_REDIRECT_ERROR'),
    'redirect_uri' => env('HF_SESSION_REDIRECT_URI'),
    'auth_settings' => [
        'client_id' => env('HF_SESSION_CLIENT_ID'),
        'client_secret' => env('HF_SESSION_CLIENT_SECRET'),
        'grant_type' => env('HF_SESSION_GRANT_TYPE', 'password'),
        'scopes' => env('HF_SESSION_SCOPES'),
    ],
];
