<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Database
    |--------------------------------------------------------------------------
    */
    'database' => [
        'table_prefix' => env('CHECKOUT_TABLE_PREFIX', env('COMMERCE_TABLE_PREFIX', '')),
        'tables' => [
            'checkout_sessions' => 'checkout_sessions',
        ],
        'json_column_type' => env('CHECKOUT_JSON_COLUMN_TYPE', env('COMMERCE_JSON_COLUMN_TYPE', 'jsonb')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Defaults
    |--------------------------------------------------------------------------
    */
    'defaults' => [
        'currency' => env('CHECKOUT_CURRENCY', 'MYR'),
        'session_ttl' => 60 * 60 * 24, // 24 hours
        'session_query_param' => 'session', // Query param name for session ID
        'shipping_rate' => env('CHECKOUT_DEFAULT_SHIPPING_RATE', 1000), // Fallback shipping rate in cents
    ],

    /*
    |--------------------------------------------------------------------------
    | Models
    |--------------------------------------------------------------------------
    |
    | Customize the model classes used by the checkout package. This allows
    | you to extend the default models with your own implementations.
    |
    */
    'models' => [
        'customer' => AIArmada\Customers\Models\Customer::class,
        'order' => App\Models\Order::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Steps
    |--------------------------------------------------------------------------
    |
    | Configure which steps are enabled and their execution order.
    | Some steps can be disabled if the corresponding package is not installed
    | or the functionality is not needed.
    |
    | NOTE: create_order is disabled because the app uses App\Models\Order
    | instead of AIArmada\Orders\Models\Order. Order creation is handled
    | by the existing CHIP webhook handler (ChipController -> CheckoutService).
    |
    */
    'steps' => [
        'enabled' => [
            'validate_cart' => true,
            'resolve_customer' => true,
            'calculate_pricing' => true,
            'apply_discounts' => true,
            'calculate_shipping' => true,
            'calculate_tax' => false, // Disabled - no tax in Malaysia for most items
            'reserve_inventory' => true, // Reserve stock before payment
            'process_payment' => true,
            'create_order' => true, // Create order after payment success
            'dispatch_documents' => true, // Generate invoice after order creation
        ],
        'order' => [
            'validate_cart',
            'resolve_customer',
            'calculate_pricing',
            'apply_discounts',
            'calculate_shipping',
            // 'calculate_tax', // Uncomment when tax calculation is needed
            'reserve_inventory',
            'process_payment',
            'create_order',
            'dispatch_documents',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Owner / Multi-tenancy
    |--------------------------------------------------------------------------
    */
    'owner' => [
        'enabled' => env('CHECKOUT_OWNER_ENABLED', false),
        'include_global' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Integrations
    |--------------------------------------------------------------------------
    |
    | Enable/disable integration with other packages. Disable integrations
    | the feature is not yet fully configured for this application.
    |
    */
    'integrations' => [
        'inventory' => [
            'enabled' => true, // Validate and reserve stock
            'validate_stock' => true,
            'reserve_before_payment' => false, // Set to false since calculate_tax is disabled
            'release_on_failure' => true,
            'reservation_ttl' => 60 * 15, // 15 minutes
        ],
        'shipping' => [
            'enabled' => true, // Calculate shipping rates
            'require_selection' => true,
            'jnt' => [
                'enabled' => true,
                'auto_detect' => true,
            ],
        ],
        'tax' => [
            'enabled' => false, // Disable - no tax calculation needed for Malaysia
        ],
        'promotions' => [
            'enabled' => true,
            'auto_apply' => true,
        ],
        'vouchers' => [
            'enabled' => true,
            'allow_multiple' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Gateways
    |--------------------------------------------------------------------------
    |
    | Configure payment gateway integration. When using 'chip' or 'jnt', the
    | checkout package references their respective package configs directly
    | to avoid configuration mismatches.
    |
    | Gateway options: 'chip', 'stripe', 'cashier', 'cashier-chip'
    |
    */
    'payment' => [
        'default_gateway' => env('CHECKOUT_DEFAULT_GATEWAY', 'chip'),
        'gateway_priority' => ['chip', 'cashier-chip', 'cashier'],
        'retry_limit' => env('CHECKOUT_PAYMENT_RETRY_LIMIT', 3),

        // Gateway-specific: reference related package config keys
        // These are NOT duplicated - they reference the actual package configs
        'gateways' => [
            'chip' => [
                // Uses config('chip.collect.brand_id'), config('chip.webhooks.verify_signature'), etc.
                'enabled' => env('CHECKOUT_CHIP_ENABLED', true),
                'config_namespace' => 'chip', // Reference to chip package config
            ],
            'stripe' => [
                'enabled' => env('CHECKOUT_STRIPE_ENABLED', false),
                'config_namespace' => 'cashier', // Reference to cashier package config
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Routes
    |--------------------------------------------------------------------------
    |
    | Configure all checkout routes. Callback routes handle user redirects
    | from payment gateways. Webhook routes handle async notifications.
    |
    */
    'routes' => [
        'enabled' => env('CHECKOUT_ROUTES_ENABLED', true),
        'prefix' => env('CHECKOUT_ROUTE_PREFIX', 'checkout'),
        'middleware' => ['web'],

        // Payment callback routes (user redirects from gateway)
        'callbacks' => [
            'success' => 'payment/success',
            'failure' => 'payment/failure',
            'cancel' => 'payment/cancel',
        ],

        // Webhook configuration
        'webhook_prefix' => env('CHECKOUT_WEBHOOK_PREFIX', 'webhooks'),
        'webhook_path' => 'checkout',
        'webhook_middleware' => ['api'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Redirects (After Payment Callback)
    |--------------------------------------------------------------------------
    |
    | Where to redirect users after payment callbacks. Supports placeholders:
    | {order_id}, {session_id}
    |
    */
    'redirects' => [
        'success' => env('CHECKOUT_REDIRECT_SUCCESS', '/checkout/success/{session_id}'),
        'failure' => env('CHECKOUT_REDIRECT_FAILURE', '/checkout/failure/{session_id}'),
        'cancel' => env('CHECKOUT_REDIRECT_CANCEL', '/checkout/cancel/{session_id}'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Response Mode
    |--------------------------------------------------------------------------
    |
    | How the package responds after processing payment callbacks:
    |
    | 'redirect' - Redirect to URLs in 'redirects' config (default)
    |              Use this when you have custom result page controllers.
    |
    | 'view' - Render package views directly
    |          Use this for simpler setups without custom controllers.
    |
    | KakKay uses 'redirect' because it has custom views and controller.
    |
    */
    'response_mode' => env('CHECKOUT_RESPONSE_MODE', 'redirect'),

    /*
    |--------------------------------------------------------------------------
    | Views (Package Built-in)
    |--------------------------------------------------------------------------
    |
    | Configure package views for checkout result pages.
    | Views can be published with: php artisan vendor:publish --tag=checkout-views
    |
    | Only used when response_mode is 'view'.
    | KakKay uses custom views, so this is not used.
    |
    */
    'views' => [
        'enabled' => false,
        'layout' => 'layouts.app',
        'routes' => [
            'success' => 'checkout::success',
            'failure' => 'checkout::failure',
            'cancel' => 'checkout::cancel',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Verification
    |--------------------------------------------------------------------------
    |
    | Webhook signature verification settings. When enabled, webhooks are
    | validated using the source gateway's verification mechanism:
    | - CHIP: Uses config('chip.webhooks.verify_signature') and public key
    | - Stripe: Uses config('cashier.webhook.secret')
    |
    */
    'webhooks' => [
        'verify_signature' => env('CHECKOUT_WEBHOOK_VERIFY_SIGNATURE', true),
        'log_payloads' => env('CHECKOUT_WEBHOOK_LOG_PAYLOADS', false),
        'log_channel' => env('CHECKOUT_WEBHOOK_LOG_CHANNEL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Documents
    |--------------------------------------------------------------------------
    */
    'documents' => [
        'queue' => env('CHECKOUT_DOCUMENTS_QUEUE', 'default'),
        'generate_invoice' => true,
        'generate_receipt' => true,
    ],
];
