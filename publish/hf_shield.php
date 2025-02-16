<?php

declare(strict_types=1);

use function Hyperf\Support\env;

return [
    /*
     * Enables the OAuth endpoints in the project that imported this package.
     * Set this to false if the service is not responsible for user authentication.
     * The repositories, validations, and middlewares will still be available for credential checks.
     */
    'enable_oauth_endpoints' => true,

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
];