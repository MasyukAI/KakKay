<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Storage Driver
    |--------------------------------------------------------------------------
    |
    | This option controls the default storage driver that will be used
    | for storing cart data. Supported drivers: "session", "database", "cache"
    |
    */
    'storage' => env('CART_STORAGE_DRIVER', 'session'),

    /*
    |--------------------------------------------------------------------------
    | Session Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for session-based storage
    |
    */
    'session' => [
        'key' => env('CART_SESSION_KEY', 'cart'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for database-based storage
    |
    */
    'database' => [
        'table' => env('CART_DB_TABLE', 'carts'),
        'lock_for_update' => env('CART_DB_LOCK_FOR_UPDATE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for cache-based storage
    |
    */
    'cache' => [
        'prefix' => env('CART_CACHE_PREFIX', 'cart'),
        'ttl' => env('CART_CACHE_TTL', 86400),
    ],

    /*
    |--------------------------------------------------------------------------
    | Money & Currency Configuration
    |--------------------------------------------------------------------------
    |
    | Laravel Money is used internally for all calculations.
    | Only currency configuration is needed - Laravel Money handles precision.
    |
    */
    'money' => [
        // Default currency for all Money objects
        'default_currency' => env('CART_DEFAULT_CURRENCY', 'MYR'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Events
    |--------------------------------------------------------------------------
    |
    | Enable or disable cart events
    |
    */
    'events' => env('CART_EVENTS_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    |
    | Enable strict validation for cart operations
    |
    */
    'strict_validation' => env('CART_STRICT_VALIDATION', true),

    /*
    |--------------------------------------------------------------------------
    | Cart Migration Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for guest-to-user cart migration
    |
    */
    'migration' => [
        // Automatically migrate guest cart to user cart on login
        'auto_migrate_on_login' => env('CART_AUTO_MIGRATE_ON_LOGIN', true),

        // Strategy for handling conflicts when merging carts
        // Options: 'add_quantities', 'keep_highest_quantity', 'keep_user_cart', 'replace_with_guest'
        'merge_strategy' => env('CART_MERGE_STRATEGY', 'add_quantities'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Data Limits & Security
    |--------------------------------------------------------------------------
    |
    | Limits to prevent DoS attacks and ensure reasonable data sizes
    |
    */
    'limits' => [
        // Maximum number of items in a cart
        'max_items' => env('CART_MAX_ITEMS', 1000),

        // Maximum size of cart data in bytes (items, conditions, metadata)
        'max_data_size_bytes' => env('CART_MAX_DATA_SIZE_BYTES', 1024 * 1024), // 1MB

        // Maximum quantity per item
        'max_item_quantity' => env('CART_MAX_ITEM_QUANTITY', 10000),

        // Maximum string length for item names/attributes
        'max_string_length' => env('CART_MAX_STRING_LENGTH', 255),
    ],

    /*
    |--------------------------------------------------------------------------
    | Metrics & Observability
    |--------------------------------------------------------------------------
    |
    | Configuration for cart metrics collection and monitoring
    |
    */
    'metrics' => [
        // Enable metrics collection
        'enabled' => env('CART_METRICS_ENABLED', true),

        // Record performance metrics for operations slower than this threshold (seconds)
        'slow_operation_threshold' => env('CART_SLOW_OPERATION_THRESHOLD', 1.0),

        // Enable conflict tracking
        'track_conflicts' => env('CART_TRACK_CONFLICTS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Retry Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for automatic retry on cart conflicts
    |
    */
    'retry' => [
        // Enable automatic retries
        'enabled' => env('CART_RETRY_ENABLED', true),

        // Maximum number of retry attempts
        'max_attempts' => env('CART_RETRY_MAX_ATTEMPTS', 3),

        // Base delay between retries (milliseconds)
        'base_delay' => env('CART_RETRY_BASE_DELAY', 100),

        // Maximum delay between retries (milliseconds)
        'max_delay' => env('CART_RETRY_MAX_DELAY', 1000),

        // Use exponential backoff
        'exponential_backoff' => env('CART_RETRY_EXPONENTIAL_BACKOFF', true),

        // Add jitter to prevent thundering herd
        'jitter' => env('CART_RETRY_JITTER', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cart Cleanup
    |--------------------------------------------------------------------------
    |
    | Configuration for automatic cart cleanup
    |
    */
    'cleanup' => [
        // Days after which carts are considered abandoned
        'abandoned_after_days' => env('CART_ABANDONED_AFTER_DAYS', 7),

        // Enable automatic cleanup of abandoned carts
        'auto_cleanup' => env('CART_AUTO_CLEANUP', false),

        // Batch size for cleanup operations
        'cleanup_batch_size' => env('CART_CLEANUP_BATCH_SIZE', 1000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Laravel Octane Compatibility
    |--------------------------------------------------------------------------
    |
    | Configuration for Laravel Octane compatibility
    |
    */
    'octane' => [
        // Automatically register Octane listeners to reset state
        'auto_register_listeners' => env('CART_OCTANE_AUTO_LISTENERS', true),

        // Use cache storage by default in Octane environment for better performance
        'prefer_cache_storage' => env('CART_OCTANE_PREFER_CACHE', true),

        // Queue cart events to avoid blocking requests
        'queue_events' => env('CART_OCTANE_QUEUE_EVENTS', true),

        // Reset static state between requests
        'reset_static_state' => env('CART_OCTANE_RESET_STATE', true),
    ],
];
