<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Navigation Group
    |--------------------------------------------------------------------------
    |
    | The navigation group where the cart resource will be displayed.
    |
    */
    'navigation_group' => 'E-commerce',

    /*
    |--------------------------------------------------------------------------
    | Navigation Icon
    |--------------------------------------------------------------------------
    |
    | The icon for the cart resource in the navigation.
    |
    */
    'navigation_icon' => 'heroicon-o-shopping-cart',

    /*
    |--------------------------------------------------------------------------
    | Polling Interval
    |--------------------------------------------------------------------------
    |
    | The polling interval in seconds for real-time updates.
    |
    */
    'polling_interval' => 30,

    /*
    |--------------------------------------------------------------------------
    | Per Page Options
    |--------------------------------------------------------------------------
    |
    | The number of records to display per page in the table.
    |
    */
    'per_page_options' => [10, 25, 50, 100],

    /*
    |--------------------------------------------------------------------------
    | Default Cart Instance
    |--------------------------------------------------------------------------
    |
    | The default cart instance to use when creating new carts.
    |
    */
    'default_instance' => 'default',

    /*
    |--------------------------------------------------------------------------
    | Cart Instances
    |--------------------------------------------------------------------------
    |
    | The available cart instances.
    |
    */
    'instances' => [
        'default' => 'Default',
        'wishlist' => 'Wishlist',
        'comparison' => 'Comparison',
        'quote' => 'Quote',
        'bulk' => 'Bulk Order',
        'subscription' => 'Subscription',
    ],

    /*
    |--------------------------------------------------------------------------
    | Normalized Models
    |--------------------------------------------------------------------------
    |
    | Enable normalized cart items and conditions for better search/filter
    | performance. When enabled, cart operations will be automatically
    | synchronized to readonly normalized models via event listeners.
    |
    */
    'enable_normalized_models' => true,

    /*
    |--------------------------------------------------------------------------
    | Event Synchronization
    |--------------------------------------------------------------------------
    |
    | Configure how cart events are synchronized to normalized models.
    |
    */
    'synchronization' => [
        /*
        | Queue synchronization for better performance
        | Note: Requires queue configuration in your Laravel app
        */
        'queue_sync' => false,

        /*
        | Queue connection to use for synchronization jobs
        */
        'queue_connection' => 'default',

        /*
        | Queue name for synchronization jobs
        */
        'queue_name' => 'cart-sync',

        /*
        | Retry failed synchronization jobs
        */
        'retry_failed_jobs' => true,

        /*
        | Maximum retry attempts for failed synchronization
        */
        'max_retry_attempts' => 3,
    ],

    /*
    |--------------------------------------------------------------------------
    | Resource Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the Filament resources for normalized models.
    |
    */
    'resources' => [
        /*
        | Enable cart items resource
        */
        'enable_cart_items' => true,

        /*
        | Enable cart conditions resource
        */
        'enable_cart_conditions' => true,

        /*
        | Navigation sort order for resources
        */
        'navigation_sort' => [
            'carts' => 30,
            'cart_items' => 31,
            'cart_conditions' => 32,
        ],
    ],
];
