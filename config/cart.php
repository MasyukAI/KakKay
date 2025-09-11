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
    'storage' => env('CART_STORAGE_DRIVER', 'database'),

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

        // Backup user cart to guest session on logout
        'backup_on_logout' => env('CART_BACKUP_ON_LOGOUT', false),

        // Strategy for handling conflicts when merging carts
        // Options: 'add_quantities', 'keep_highest_quantity', 'keep_user_cart', 'replace_with_guest'
        'merge_strategy' => env('CART_MERGE_STRATEGY', 'add_quantities'),

        // Automatically switch cart instances based on auth status
        'auto_switch_instances' => env('CART_AUTO_SWITCH_INSTANCES', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Money Configuration (Internal Precision)
    |--------------------------------------------------------------------------
    |
    | Configuration for Money objects used internally for precise calculations
    |
    */
    'money' => [
        'default_currency' => env('CART_CURRENCY', 'USD'),
        'default_precision' => env('CART_MONEY_PRECISION', 2),
        'rounding_mode' => env('CART_ROUNDING_MODE', 'ROUND_HALF_UP'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Display Configuration (Public Formatting)
    |--------------------------------------------------------------------------
    |
    | Configuration for how prices are displayed to users
    |
    */
    'display' => [
        // Enable automatic formatting for all price outputs
        'formatting_enabled' => env('CART_AUTO_FORMAT', false),

        // Price transformer class - IntegerPriceTransformer stores prices as cents (integer)
        'transformer' => env('CART_PRICE_TRANSFORMER', \MasyukAI\Cart\PriceTransformers\IntegerPriceTransformer::class),

        // Display locale
        'locale' => env('CART_LOCALE', 'en_US'),

        // Display options
        'show_currency_symbol' => env('CART_SHOW_CURRENCY_SYMBOL', false),
    ],
];
