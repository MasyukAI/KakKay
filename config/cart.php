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
    'storage' => 'session',

    /*
    |--------------------------------------------------------------------------
    | Session Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for session-based storage
    |
    */
    'session' => [
        'key' => 'masyukai_cart',
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
        'connection' => null,
        'table' => 'cart_storage',
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
        'store' => null,
        'prefix' => 'cart',
        'ttl' => 86400, // 24 hours
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Instance
    |--------------------------------------------------------------------------
    |
    | Default cart instance name
    |
    */
    'default_instance' => 'default',

    /*
    |--------------------------------------------------------------------------
    | Number Formatting
    |--------------------------------------------------------------------------
    |
    | Number formatting configuration
    |
    */
    'format_numbers' => false,
    'decimals' => 2,
    'decimal_point' => '.',
    'thousands_separator' => ',',

    /*
    |--------------------------------------------------------------------------
    | Events
    |--------------------------------------------------------------------------
    |
    | Enable or disable cart events
    |
    */
    'events' => true,

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    |
    | Enable strict validation for cart operations
    |
    */
    'strict_validation' => true,

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
        // Enable demo routes (set CART_DEMO_ENABLED in .env to true/false)
        'enabled' => env('CART_DEMO_ENABLED', true),
        
        // Demo route prefix
        'prefix' => env('CART_DEMO_PREFIX', 'cart-demo'),
        
        // Demo route middleware
        'middleware' => ['web'],
    ],
];
