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
    'sid' => env('TWILIO_ACCOUNT_SID', ''),
    'token' => env('TWILIO_AUTH_TOKEN', ''),
    'from' => env('TWILIO_PHONE_NUMBER', ''),
    'messaging_service' => env('TWILIO_MESSAGING_SERVICE_SID', ''),
    'content_sid_otp' => env('TWILIO_CONTENT_SID_OTP', ''),
    'content_sid_welcome' => env('TWILIO_CONTENT_SID_WELCOME', ''),
];
