<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Voucher System Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how vouchers behave in your application.
    |
    */

    'enabled' => env('VOUCHERS_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Voucher Code Settings
    |--------------------------------------------------------------------------
    */

    'code' => [
        'case_sensitive' => env('VOUCHERS_CASE_SENSITIVE', false),
        'auto_uppercase' => env('VOUCHERS_AUTO_UPPERCASE', true),
        'min_length' => env('VOUCHERS_MIN_LENGTH', 4),
        'max_length' => env('VOUCHERS_MAX_LENGTH', 32),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cart Integration
    |--------------------------------------------------------------------------
    */

    'cart' => [
        'max_vouchers_per_cart' => env('VOUCHERS_MAX_PER_CART', 1),
        'auto_apply_best' => env('VOUCHERS_AUTO_APPLY_BEST', false),
        'condition_order' => env('VOUCHERS_CONDITION_ORDER', 50),
        'allow_stacking' => env('VOUCHERS_ALLOW_STACKING', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    'validation' => [
        'check_user_limit' => env('VOUCHERS_CHECK_USER_LIMIT', true),
        'check_global_limit' => env('VOUCHERS_CHECK_GLOBAL_LIMIT', true),
        'check_date_range' => env('VOUCHERS_CHECK_DATE_RANGE', true),
        'check_min_cart_value' => env('VOUCHERS_CHECK_MIN_CART_VALUE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Usage Tracking
    |--------------------------------------------------------------------------
    */

    'tracking' => [
        'enabled' => env('VOUCHERS_TRACKING_ENABLED', true),
        'store_cart_snapshot' => env('VOUCHERS_STORE_CART_SNAPSHOT', true),
        'cleanup_after_days' => env('VOUCHERS_CLEANUP_AFTER_DAYS', 90),
    ],

    /*
    |--------------------------------------------------------------------------
    | Events
    |--------------------------------------------------------------------------
    */

    'events' => [
        'dispatch' => env('VOUCHERS_DISPATCH_EVENTS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Tables
    |--------------------------------------------------------------------------
    */

    'table_names' => [
        'vouchers' => 'vouchers',
        'voucher_usage' => 'voucher_usage',
    ],
];
