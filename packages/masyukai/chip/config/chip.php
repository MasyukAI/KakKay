<?php

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
        'timeout' => env('CHIP_COLLECT_TIMEOUT', 30),
        'retry' => [
            'attempts' => env('CHIP_COLLECT_RETRY_ATTEMPTS', 3),
            'delay' => env('CHIP_COLLECT_RETRY_DELAY', 1000),
        ],
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
        'environment' => env('CHIP_SEND_ENVIRONMENT', 'sandbox'),
        'base_url' => [
            'sandbox' => env('CHIP_SEND_SANDBOX_URL', 'https://staging-api.chip-in.asia/api'),
            'production' => env('CHIP_SEND_PRODUCTION_URL', 'https://api.chip-in.asia/api'),
        ],
        'api_key' => env('CHIP_SEND_API_KEY'),
        'api_secret' => env('CHIP_SEND_API_SECRET'),
        'timeout' => env('CHIP_SEND_TIMEOUT', 30),
        'retry' => [
            'attempts' => env('CHIP_SEND_RETRY_ATTEMPTS', 3),
            'delay' => env('CHIP_SEND_RETRY_DELAY', 1000),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Environment Configuration
    |--------------------------------------------------------------------------
    |
    | Determine whether to use sandbox or production endpoints
    |
    */
    'sandbox' => env('CHIP_SANDBOX', true),

    /*
    |--------------------------------------------------------------------------
    | Webhooks Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for webhook handling and verification
    |
    */
    'webhooks' => [
        'public_key' => env('CHIP_WEBHOOK_PUBLIC_KEY'),
        'verify_signature' => env('CHIP_WEBHOOK_VERIFY_SIGNATURE', true),
        'middleware' => ['api'],
        'allowed_events' => [
            'purchase.created',
            'purchase.paid',
            'purchase.cancelled',
            'payment.created',
            'payment.paid',
            'payment.failed',
            // CHIP Send webhook events
            'bank_account_status',
            'budget_allocation_status',
            'send_instruction_status',
        ],
        'event_mapping' => [
            'purchase.created' => \MasyukAI\Chip\Events\PurchaseCreated::class,
            'purchase.paid' => \MasyukAI\Chip\Events\PurchasePaid::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Event Configuration
    |--------------------------------------------------------------------------
    |
    | Configure event dispatching behavior
    |
    */
    'events' => [
        'dispatch_purchase_events' => env('CHIP_DISPATCH_PURCHASE_EVENTS', true),
        'dispatch_payment_events' => env('CHIP_DISPATCH_PAYMENT_EVENTS', true),
        'dispatch_webhook_events' => env('CHIP_DISPATCH_WEBHOOK_EVENTS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Configure logging behavior for CHIP operations
    |
    */
    'logging' => [
        'channel' => env('CHIP_LOG_CHANNEL', 'default'),
        'level' => env('CHIP_LOG_LEVEL', 'info'),
        'log_requests' => env('CHIP_LOG_REQUESTS', false),
        'log_responses' => env('CHIP_LOG_RESPONSES', false),
        'log_webhooks' => env('CHIP_LOG_WEBHOOKS', true),
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
            'webhook_config' => env('CHIP_CACHE_WEBHOOK_CONFIG_TTL', 3600),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Configuration
    |--------------------------------------------------------------------------
    |
    | Configure rate limiting for API requests
    |
    */
    'rate_limiting' => [
        'enabled' => env('CHIP_RATE_LIMITING_ENABLED', true),
        'requests_per_minute' => env('CHIP_RATE_LIMIT_PER_MINUTE', 60),
        'burst_limit' => env('CHIP_RATE_LIMIT_BURST', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation Configuration
    |--------------------------------------------------------------------------
    |
    | Configure validation rules and behavior
    |
    */
    'validation' => [
        'strict_mode' => env('CHIP_VALIDATION_STRICT', false),
        'allow_unknown_events' => env('CHIP_ALLOW_UNKNOWN_EVENTS', true),
        'webhook_signature_tolerance' => env('CHIP_WEBHOOK_SIGNATURE_TOLERANCE', 300),
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
        'creator_agent' => env('CHIP_CREATOR_AGENT', 'Laravel Package'),
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

    /*
    |--------------------------------------------------------------------------
    | Testing Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration specific to testing environments
    |
    */
    'testing' => [
        'mock_responses' => env('CHIP_MOCK_RESPONSES', false),
        'fake_webhooks' => env('CHIP_FAKE_WEBHOOKS', false),
        'skip_verification' => env('CHIP_SKIP_VERIFICATION', false),
    ],
];
