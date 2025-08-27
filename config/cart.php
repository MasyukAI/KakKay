<?php

return [
    /*
     * ---------------------------------------------------------------
     * Formatting
     * ---------------------------------------------------------------
     */
    'format_numbers' => env('LARAVEL_CART_FORMAT_VALUES', false),

    'decimals' => env('LARAVEL_CART_DECIMALS', 0),

    'round_mode' => env('LARAVEL_CART_ROUND_MODE', 'down'),

    /*
     * ---------------------------------------------------------------
     * Storage
     * ---------------------------------------------------------------
     */
    'driver' => 'database',

    'storage' => [
        'session',
        'database' => [
            'model' => \App\Models\Carting::class, // your model here
            'id' => 'session_id',
            'items' => 'items',
            'conditions' => 'conditions',
        ],
    ],
];
