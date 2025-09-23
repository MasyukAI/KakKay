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
];
