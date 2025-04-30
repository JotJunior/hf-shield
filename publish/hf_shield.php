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
    /*
     * Enables the OAuth endpoints in the project that imported this package.
     * Set this to false if the service is not responsible for user authentication.
     * The repositories, validations, and middlewares will still be available for credential checks.
     */
    'enable_oauth_endpoints' => env('OAUTH_ENABLE_ENDPOINTS', false),

    /*
     * The endpoint api version
     */
    'api_version' => env('API_VERSION', 'v1'),

    /*
     * API description for swagger home documentation
     */
    'api_description' => env('API_DESCRIPTION', ''),

    /*
     * The middleware strategy for request validation (bearer|session|signed_jwt|public)
     */
    'middleware_strategy' => env('OAUTH_MIDDLEWARE_STRATEGY', 'bearer'),

    /*
     * The module name included on top of scope structure:  module:resource:action
     */
    'module_name' => env('OAUTH_SCOPE_MODULE_NAME', 'api'),

    /*
     * Specifies the format of the token used for authentication.
     * The default value is 'JWT'.
     */
    'token_format' => env('OAUTH_TOKEN_FORMAT', 'JWT'),

    /*
     * The private key used for signing JWT tokens.
     * This key must be kept secret to ensure the security of the token signatures.
     */
    'private_key' => env('OAUTH_PRIVATE_KEY', ''),

    /*
     * The public key corresponding to the private key.
     * This key is used to verify the signature of JWT tokens and can be shared publicly.
     */
    'public_key' => env('OAUTH_PUBLIC_KEY', ''),

    /*
     * A key used to encrypt and decrypt data that depend on this functionality.
     * It is also used as an additional key, along with the password salt, for encrypting one version of the users’ passwords.
     */
    'encryption_key' => env('OAUTH_ENCRYPTION_KEY', ''),

    /*
     * The JWT token expiration time in DateInterval format.
     * The default value is 1 day (P1D).
     */
    'token_days' => env('OAUTH_TOKEN_DAYS_INTERVAL', 'P1D'),

    /*
     * The refresh token expiration time in DateInterval format.
     * The default value is 1 month (P1M).
     */
    'refresh_token_days' => env('OAUTH_REFRESH_TOKEN_DAYS_INTERVAL', 'P1M'),

    /*
     * Revokes all active tokens of the user that were created under the same client ID.
     */
    'revoke_user_old_tokens' => env('OAUTH_REVOKE_USER_OLD_TOKENS', true),

    /*
     * The list of basic scopes that the user will have access to.
     */
    'basic_scopes' => [
        'oauth:user:view',
        'oauth:user:session',
        'oauth:user:update_settings',
        'gk_settings:basic_option:view',
        'gk_settings:basic_option:list',
        'gk_profile:token:list',
        'gk_profile:user:update',
        'gk_profile:user:view',
    ],

    /*
     * S3 bucket configuration
     */
    's3_bucket_url' => env('S3_BUCKET_URL', ''),
    's3_bucket_name' => env('S3_BUCKET_NAME', ''),
    's3_bucket_region' => env('S3_BUCKET_REGION', ''),
    's3_bucket_access_key' => env('S3_BUCKET_ACCESS_KEY', ''),
    's3_bucket_secret_key' => env('S3_BUCKET_SECRET_KEY', ''),
];
