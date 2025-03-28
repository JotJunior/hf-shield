<?php

declare(strict_types=1);

use function Hyperf\Support\env;

return [
    'redirect_error' => env('HF_SESSION_REDIRECT_ERROR'),
    'redirect_uri' => env('HF_SESSION_REDIRECT_URI'),
    'auth_settings' => [
        'client_id' => env('HF_SESSION_CLIENT_ID'),
        'client_secret' => env('HF_SESSION_CLIENT_SECRET'),
        'grant_type' => env('HF_SESSION_GRANT_TYPE', 'password'),
        'scopes' => env('HF_SESSION_SCOPES')
    ]
];