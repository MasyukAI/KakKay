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
    | Database Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for database-based storage
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
        'connection' => env('CART_DB_CONNECTION', 'null'),   // cant find this
        'table' => env('CART_DB_TABLE', 'cart_storage'),
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
        'store' => env('CART_CACHE_STORE', 'database'),  // Cant find this
        'prefix' => env('CART_CACHE_PREFIX', 'cart'),
        'ttl' => env('CART_CACHE_TTL', 86400),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Instance
    |--------------------------------------------------------------------------
    |
    | Default cart instance name
    |
    */
    'default_instance' => env('CART_DEFAULT_INSTANCE', 'default'),   // Cant find this

    /*
    |--------------------------------------------------------------------------
    | Number Formatting
    |--------------------------------------------------------------------------
    |
    | Number formatting configuration
    |
    */
    'format_numbers' => env('CART_FORMAT_NUMBERS', false),// Cant find this
    'decimals' => env('CART_DECIMALS', 2),// Cant find this
    'decimal_point' => env('CART_DECIMAL_POINT', '.'),// Cant find this
    'thousands_separator' => env('CART_THOUSANDS_SEPARATOR', ','),// Cant find this

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
    'strict_validation' => env('CART_STRICT_VALIDATION', true), // For testing

    /*
    |--------------------------------------------------------------------------
    | Tax Configuration
    |--------------------------------------------------------------------------
    |
    | Default tax configuration
    |
    */
    'tax' => [
        'enabled' => env('CART_TAX_ENABLED', false), // Cant find this
        'rate' => env('CART_TAX_RATE', 0.1), // 10%  Cant find this
        'inclusive' => env('CART_TAX_INCLUSIVE', false), // Cant find this
    ],

    /*
    |--------------------------------------------------------------------------
    | Currency Configuration
    |--------------------------------------------------------------------------
    |
    | Default currency settings
    |
    */
    'currency' => [
        'code' => env('CART_CURRENCY_CODE', 'MYR'),// Cant find this
        'symbol' => env('CART_CURRENCY_SYMBOL', 'RM'),// Cant find this
        'position' => env('CART_CURRENCY_POSITION', 'before'), // before, after Cant find this
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto-destroy Empty Carts
    |--------------------------------------------------------------------------
    |
    | Automatically destroy empty carts after specified time (in minutes)
    | Set to null to disable
    |
    */
    'auto_destroy_empty' => env('CART_AUTO_DESTROY_EMPTY', 60),  // Cant find this

    /*
    |--------------------------------------------------------------------------
    | Maximum Cart Size
    |--------------------------------------------------------------------------
    |
    | Maximum number of items allowed in a cart
    | Set to null for unlimited
    |
    */
    'max_items' => env('CART_MAX_ITEMS', null),  // Cant find this

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

        // Instance name prefixes
        'guest_instance_prefix' => env('CART_GUEST_PREFIX', 'guest'),  // Cant find this
        'user_instance_prefix' => env('CART_USER_PREFIX', 'user'),   // Cant find this

        // Automatically switch cart instances based on auth status
        'auto_switch_instances' => env('CART_AUTO_SWITCH_INSTANCES', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Demo Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the demo routes and functionality
    |
    */
    'demo' => [
        // Enable demo routes (automatically enabled in local and testing environments)
        'enabled' => env('CART_DEMO_ENABLED', false),

        // Demo route prefix
        'prefix' => env('CART_DEMO_PREFIX', 'cart-demo'),    // Cant find this

        // Demo route middleware
        'middleware' => ['web'],    // Cant find this
    ],
];
