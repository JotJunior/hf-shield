<?php

declare(strict_types=1);

use function Hyperf\Support\env;

return [
    'token_format' => env('OAUTH_TOKEN_FORMAT', 'JWT'),
    'private_key' => env('OAUTH_PRIVATE_KEY', ''),
    'public_key' => env('OAUTH_PUBLIC_KEY', ''),
    'encryption_key' => env('OAUTH_ENCRYPTION_KEY', ''),
    'token_days' => env('OAUTH_TOKEN_DAYS_INTERVAL', 'P1D'),
    'refresh_token_days' => env('OAUTH_REFRESH_TOKEN_DAYS_INTERVAL', 'P1M'),
    'revoke_user_old_tokens' => env('OAUTH_REVOKE_USER_OLD_TOKENS', true),
];