<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Essential Credentials
    |--------------------------------------------------------------------------
    |
    | Only these are required. Everything else has sensible defaults.
    |
    */
    'environment' => env('JNT_ENVIRONMENT', 'testing'),
    'api_account' => env('JNT_API_ACCOUNT'),
    'private_key' => env('JNT_PRIVATE_KEY'),
    'customer_code' => env('JNT_CUSTOMER_CODE'),
    'password' => env('JNT_PASSWORD'),

    /*
    |--------------------------------------------------------------------------
    | Advanced Configuration (Optional)
    |--------------------------------------------------------------------------
    |
    | Override defaults only if needed. Most users can ignore this section.
    |
    */
    'base_urls' => [
        'testing' => env('JNT_BASE_URL_TESTING', 'https://demoopenapi.jtexpress.my/webopenplatformapi'),
        'production' => env('JNT_BASE_URL_PRODUCTION', 'https://ylopenapi.jtexpress.my/webopenplatformapi'),
    ],

    'http' => [
        'timeout' => env('JNT_HTTP_TIMEOUT', 30),
        'connect_timeout' => env('JNT_HTTP_CONNECT_TIMEOUT', 10),
        'retry_times' => env('JNT_HTTP_RETRY_TIMES', 3),
        'retry_sleep' => env('JNT_HTTP_RETRY_SLEEP', 1000), // milliseconds
    ],

    'logging' => [
        'enabled' => env('JNT_LOGGING_ENABLED', true),
        'channel' => env('JNT_LOGGING_CHANNEL', 'stack'),
    ],

    'webhook' => [
        'enabled' => env('JNT_WEBHOOK_ENABLED', true),
        'verify_signature' => env('JNT_WEBHOOK_VERIFY_SIGNATURE', true),
        'route_path' => env('JNT_WEBHOOK_ROUTE_PATH', '/api/jnt/webhook'),
    ],
];
