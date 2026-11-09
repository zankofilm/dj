<?php

return [
    /*
    | log: local development only; http: HTTPS JSON + Bearer token;
    | kavenegar: direct Iranian SMS API integration.
    */
    'driver' => env('SMS_DRIVER', 'kavenegar'),
    'endpoint' => env('SMS_HTTP_ENDPOINT'),
    'token' => env('SMS_HTTP_TOKEN'),
    'sender' => env('SMS_SENDER'),
    'timeout' => (int) env('SMS_TIMEOUT', 10),
    'kavenegar' => [
        'api_key' => env('KAVENEGAR_API_KEY'),
        'sender' => env('KAVENEGAR_SENDER'),
    ],
    'otp' => [
        'length' => 6,
        'ttl_seconds' => 300,
        'resend_cooldown_seconds' => 60,
        'max_attempts' => 5,
        'max_sends_per_hour' => 5,
    ],
];
