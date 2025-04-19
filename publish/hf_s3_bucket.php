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
    's3_bucket_url' => env('S3_BUCKET_URL', ''),
    's3_bucket_name' => env('S3_BUCKET_NAME', ''),
    's3_bucket_region' => env('S3_BUCKET_REGION', ''),
    's3_bucket_access_key' => env('S3_BUCKET_ACCESS_KEY', ''),
    's3_bucket_secret_key' => env('S3_BUCKET_SECRET_KEY', ''),
];
