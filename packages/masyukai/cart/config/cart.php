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
        'key' => env('CART_SESSION_KEY', 'masyukai_cart'),
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
        'connection' => env('CART_DB_CONNECTION', null),
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
        'store' => env('CART_CACHE_STORE', null),
        'prefix' => env('CART_CACHE_PREFIX', 'cart'),
        'ttl' => env('CART_CACHE_TTL', 86400), // 24 hours
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Instance
    |--------------------------------------------------------------------------
    |
    | Default cart instance name
    |
    */
    'default_instance' => env('CART_DEFAULT_INSTANCE', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Number Formatting
    |--------------------------------------------------------------------------
    |
    | Number formatting configuration
    |
    */
    'format_numbers' => env('CART_FORMAT_NUMBERS', false),
    'decimals' => env('CART_DECIMALS', 2),
    'decimal_point' => env('CART_DECIMAL_POINT', '.'),
    'thousands_separator' => env('CART_THOUSANDS_SEPARATOR', ','),

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
    | Tax Configuration
    |--------------------------------------------------------------------------
    |
    | Default tax configuration
    |
    */
    'tax' => [
        'enabled' => env('CART_TAX_ENABLED', false),
        'rate' => env('CART_TAX_RATE', 0.1), // 10%
        'inclusive' => env('CART_TAX_INCLUSIVE', false),
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
        'code' => env('CART_CURRENCY_CODE', 'USD'),
        'symbol' => env('CART_CURRENCY_SYMBOL', '$'),
        'position' => env('CART_CURRENCY_POSITION', 'before'), // before, after
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
    'auto_destroy_empty' => env('CART_AUTO_DESTROY_EMPTY', null),

    /*
    |--------------------------------------------------------------------------
    | Maximum Cart Size
    |--------------------------------------------------------------------------
    |
    | Maximum number of items allowed in a cart
    | Set to null for unlimited
    |
    */
    'max_items' => env('CART_MAX_ITEMS', null),

    /*
    |--------------------------------------------------------------------------
    | Persist Cart on User Login
    |--------------------------------------------------------------------------
    |
    | Automatically associate anonymous cart with authenticated user
    |
    */
    'persist_on_login' => env('CART_PERSIST_ON_LOGIN', true),

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
        'guest_instance_prefix' => env('CART_GUEST_PREFIX', 'guest'),
        'user_instance_prefix' => env('CART_USER_PREFIX', 'user'),
        
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
        'enabled' => env('CART_DEMO_ENABLED', app()->environment(['local', 'testing'])),
        
        // Demo route prefix
        'prefix' => env('CART_DEMO_PREFIX', 'cart-demo'),
        
        // Demo route middleware
        'middleware' => ['web'],
    ],
];
