<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Webhook Public Keys Processing
|--------------------------------------------------------------------------
|
| Process the CHIP_WEBHOOK_PUBLIC_KEYS environment variable which should
| contain a JSON-encoded array of webhook public keys for signature verification.
| This allows multiple webhook endpoints to have their own public keys for
| verifying incoming webhook signatures from CHIP.
|
| Expected format: CHIP_WEBHOOK_PUBLIC_KEYS='{"webhook1":"key1","webhook2":"key2"}'
*/

$webhookKeysEnv = env('CHIP_WEBHOOK_PUBLIC_KEYS');
$webhookKeys = [];

if ($webhookKeysEnv !== null) {
    // Decode the JSON string from environment variable
    $decodedWebhookKeys = json_decode($webhookKeysEnv, true);

    // Only use the decoded keys if JSON parsing was successful and resulted in an array
    if (is_array($decodedWebhookKeys)) {
        $webhookKeys = $decodedWebhookKeys;
    }
}

return [
    /*
    |--------------------------------------------------------------------------
    | CHIP Collect Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for CHIP Collect API integration
    |
    */
    'collect' => [
        'base_url' => env('CHIP_COLLECT_BASE_URL', 'https://gate.chip-in.asia/api/v1/'),
        'api_key' => env('CHIP_COLLECT_API_KEY'),
        'brand_id' => env('CHIP_COLLECT_BRAND_ID'),
    ],

    /*
    |--------------------------------------------------------------------------
    | CHIP Send Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for CHIP Send API integration
    |
    */
    'send' => [
        'base_url' => [
            'sandbox' => env('CHIP_SEND_SANDBOX_URL', 'https://staging-api.chip-in.asia/api'),
            'production' => env('CHIP_SEND_PRODUCTION_URL', 'https://api.chip-in.asia/api'),
        ],
        'api_key' => env('CHIP_SEND_API_KEY'),
        'api_secret' => env('CHIP_SEND_API_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Environment Configuration
    |--------------------------------------------------------------------------
    |
    | Shared environment setting for both Collect and Send APIs
    |
    */
    'environment' => env('CHIP_ENVIRONMENT', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Configuration
    |--------------------------------------------------------------------------
    |
    | Shared HTTP client configuration for all CHIP API services
    |
    */
    'http' => [
        'timeout' => env('CHIP_HTTP_TIMEOUT', 30),
        'retry' => [
            'attempts' => env('CHIP_HTTP_RETRY_ATTEMPTS', 3),
            'delay' => env('CHIP_HTTP_RETRY_DELAY', 1000),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhooks Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for webhook handling and verification
    |
    */
    'webhooks' => [
        'company_public_key' => env('CHIP_COMPANY_PUBLIC_KEY', env('CHIP_WEBHOOK_PUBLIC_KEY')),
        'public_key' => env('CHIP_WEBHOOK_PUBLIC_KEY'),
        'webhook_keys' => $webhookKeys,
        'verify_signature' => env('CHIP_WEBHOOK_VERIFY_SIGNATURE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configure caching behavior for API responses and data
    |
    */
    'cache' => [
        'prefix' => env('CHIP_CACHE_PREFIX', 'chip:'),
        'default_ttl' => env('CHIP_CACHE_TTL', 3600),
        'ttl' => [
            'public_key' => env('CHIP_CACHE_PUBLIC_KEY_TTL', 86400),
            'payment_methods' => env('CHIP_CACHE_PAYMENT_METHODS_TTL', 3600),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Configure request/response logging for debugging and monitoring
    |
    */
    'logging' => [
        'enabled' => env('CHIP_LOGGING_ENABLED', env('APP_DEBUG', false)),
        'channel' => env('CHIP_LOGGING_CHANNEL', 'stack'),
        'mask_sensitive_data' => env('CHIP_LOGGING_MASK_SENSITIVE', true),
        'log_requests' => env('CHIP_LOG_REQUESTS', true),
        'log_responses' => env('CHIP_LOG_RESPONSES', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Values
    |--------------------------------------------------------------------------
    |
    | Default values for various CHIP operations
    |
    */
    'defaults' => [
        'currency' => env('CHIP_DEFAULT_CURRENCY', 'MYR'),
        'creator_agent' => env('CHIP_CREATOR_AGENT', 'KakKay'),
        'platform' => env('CHIP_PLATFORM', 'api'),
        'payment_method_whitelist' => env('CHIP_PAYMENT_METHOD_WHITELIST', ''),
        'success_redirect' => env('CHIP_SUCCESS_REDIRECT'),
        'failure_redirect' => env('CHIP_FAILURE_REDIRECT'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for database table prefixes and naming
    |
    */
    'database' => [
        'table_prefix' => env('CHIP_TABLE_PREFIX', 'chip_'),
    ],
];
