<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Shipping Provider
    |--------------------------------------------------------------------------
    |
    | This option controls the default shipping provider that will be used
    | when no specific provider is specified.
    |
    */

    'default' => env('SHIPPING_PROVIDER', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Shipping Providers
    |--------------------------------------------------------------------------
    |
    | Here you may configure shipping providers for your application.
    | Each provider can have its own configuration options.
    |
    */

    'providers' => [
        'local' => [
            'driver' => 'local',
            'name' => 'Local Shipping',
            'methods' => [
                'standard' => [
                    'name' => 'Standard Shipping',
                    'description' => 'Standard shipping (3-5 business days)',
                    'price' => 500, // in cents
                    'estimated_days' => '3-5',
                ],
                'fast' => [
                    'name' => 'Fast Shipping',
                    'description' => 'Next day delivery',
                    'price' => 1500,
                    'estimated_days' => '1',
                ],
                'express' => [
                    'name' => 'Express Shipping',
                    'description' => 'Same day delivery',
                    'price' => 4900,
                    'estimated_days' => '0',
                ],
                'pickup' => [
                    'name' => 'Store Pickup',
                    'description' => 'Pick up from our store',
                    'price' => 0,
                    'estimated_days' => '0',
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Tracking Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for shipment tracking functionality.
    |
    */

    'tracking' => [
        'enabled' => env('SHIPPING_TRACKING_ENABLED', true),
        'update_interval' => env('SHIPPING_TRACKING_UPDATE_INTERVAL', 60), // minutes
        'max_attempts' => env('SHIPPING_TRACKING_MAX_ATTEMPTS', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    |
    | Configuration for shipping notifications.
    |
    */

    'notifications' => [
        'enabled' => env('SHIPPING_NOTIFICATIONS_ENABLED', true),
        'channels' => ['mail', 'database'],
        'events' => [
            'shipment_created' => true,
            'shipment_dispatched' => true,
            'shipment_in_transit' => true,
            'shipment_delivered' => true,
            'shipment_failed' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Weight Calculations
    |--------------------------------------------------------------------------
    |
    | Configuration for weight-based shipping calculations.
    |
    */

    'weight' => [
        'unit' => 'grams',
        'threshold' => 2000, // 2kg in grams
        'surcharge_per_kg' => 500, // in cents
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Tables
    |--------------------------------------------------------------------------
    |
    | The database table names used by the shipping package.
    |
    */

    'database' => [
        'shipments_table' => 'shipments',
        'tracking_events_table' => 'shipment_tracking_events',
    ],
];