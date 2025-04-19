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
    'rp_name' => env('WEBAUTHN_RP_NAME', 'Jot Application'),
    'rp_id' => env('WEBAUTHN_RP_ID', null), // Default to the hostname of the origin
    'rp_icon' => env('WEBAUTHN_RP_ICON', null),
    'origin' => env('WEBAUTHN_ORIGIN', 'https://example.com'),
];
