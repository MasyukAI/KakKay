<?php

declare(strict_types=1);

$webhookKeysEnv = env('CHIP_WEBHOOK_PUBLIC_KEYS');
$webhookKeys = [];

if ($webhookKeysEnv !== null) {
    $decodedWebhookKeys = json_decode($webhookKeysEnv, true);

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
        'environment' => env('CHIP_COLLECT_ENVIRONMENT', 'sandbox'),
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
        'middleware' => ['api'],
        'allowed_events' => [
            // Purchase events
            'purchase.created',
            'purchase.paid',
            'purchase.payment_failure',
            'purchase.pending_execute',
            'purchase.pending_charge',
            'purchase.cancelled',
            'purchase.hold',
            'purchase.captured',
            'purchase.pending_capture',
            'purchase.released',
            'purchase.pending_release',
            'purchase.preauthorized',
            'purchase.pending_recurring_token_delete',
            'purchase.recurring_token_deleted',
            'purchase.subscription_charge_failure',
            'purchase.pending_refund',
            // Payment events
            'payment.created',
            'payment.paid',
            'payment.failed',
            'payment.refunded',
            // Billing template events
            'billing_template_client.subscription_billing_cancelled',
            // Payout events
            'payout.pending',
            'payout.failed',
            'payout.success',
            // CHIP Send webhook events
            'bank_account_status',
            'budget_allocation_status',
            'send_instruction_status',
        ],
        'event_mapping' => [
            'purchase.created' => MasyukAI\Chip\Events\PurchaseCreated::class,
            'purchase.paid' => MasyukAI\Chip\Events\PurchasePaid::class,
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
        'dispatch_webhook_events' => env('CHIP_DISPATCH_WEBHOOK_EVENTS', true),
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
        'log_webhooks' => env('CHIP_LOG_WEBHOOKS', true),
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
        'creator_agent' => env('CHIP_CREATOR_AGENT', 'Masyuk AI Cart'),
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
