# CHIP Laravel Package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/masyukai/chip.svg?style=flat-square)](https://packagist.org/packages/masyukai/chip)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/masyukai/chip/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/masyukai/chip/actions?query=workflow%3Atests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/masyukai/chip/pint.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/masyukai/chip/actions?query=workflow%3Apint+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/masyukai/chip.svg?style=flat-square)](https://packagist.org/packages/masyukai/chip)

A modern, feature-complete Laravel integration for CHIP payment gateway. This package provides seamless integration with both CHIP Collect (payment collection) and CHIP Send (money transfer) APIs.

## Features

- 🚀 **Modern Laravel 12 Support** - Built for PHP 8.4 and Laravel 12
- 💳 **Complete CHIP Collect API** - Payment links, subscriptions, pre-auth, refunds
- 💸 **CHIP Send Integration** - Money transfers and bank account management
- 🔐 **Webhook Security** - Automatic signature verification
- 🧪 **Comprehensive Tests** - Full test suite with PestPHP 4
- 📝 **Type Safety** - Full PHPStan level 8 compliance
- 🎨 **Laravel Standards** - Follows Laravel coding conventions
- 🔄 **Queue Support** - Background processing for webhooks and events
- 📊 **Events & Listeners** - Laravel event system integration

## Installation

You can install the package via composer:

```bash
composer require masyukai/chip
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag="chip-config"
```

Add your CHIP credentials to your `.env` file:

```env
CHIP_API_KEY=your_api_key_here
CHIP_BRAND_ID=your_brand_id_here
CHIP_ENVIRONMENT=sandbox # or production
CHIP_SEND_API_KEY=your_send_api_key_here
CHIP_SEND_API_SECRET=your_send_api_secret_here
```

Optionally, publish and run the migrations:

```bash
php artisan vendor:publish --tag="chip-migrations"
php artisan migrate
```

## Quick Start

### Creating a Payment

```php
use MasyukAI\Chip\Facades\Chip;

$purchase = Chip::collect()->createPurchase([
    'client' => [
        'email' => 'customer@example.com',
        'full_name' => 'John Doe',
    ],
    'purchase' => [
        'products' => [
            [
                'name' => 'Premium Subscription',
                'price' => 2990, // RM 29.90 in cents
            ],
        ],
    ],
]);

// Redirect customer to payment page
return redirect($purchase->checkout_url);
```

### Handling Webhooks

```php
// In your webhook controller
use MasyukAI\Chip\Http\Requests\WebhookRequest;

public function handle(WebhookRequest $request)
{
    $payload = $request->getWebhookPayload();
    
    if ($payload->event_type === 'purchase.paid') {
        // Handle successful payment
        $purchase = $payload->purchase;
        // Update your database, send emails, etc.
    }
    
    return response('OK');
}
```

### Subscriptions

```php
// Create subscription with free trial
$subscription = Chip::collect()->createSubscription([
    'client_email' => 'customer@example.com',
    'trial_days' => 7,
    'amount' => 1990, // RM 19.90
    'interval' => 'monthly',
]);
```

### Money Transfers (CHIP Send)

```php
use MasyukAI\Chip\Facades\ChipSend;

$transfer = ChipSend::createTransfer([
    'amount' => 10000, // RM 100.00
    'recipient' => [
        'bank_account' => '1234567890',
        'bank_code' => 'MBBEMYKL',
        'name' => 'Jane Doe',
    ],
    'reference' => 'Salary Payment #123',
]);
```

## Configuration

The configuration file allows you to customize:

- API endpoints for different environments
- Default payment settings
- Webhook security settings
- Queue configuration
- Event listeners

```php
return [
    'collect' => [
        'api_key' => env('CHIP_API_KEY'),
        'brand_id' => env('CHIP_BRAND_ID'),
        'environment' => env('CHIP_ENVIRONMENT', 'sandbox'),
    ],
    
    'send' => [
        'api_key' => env('CHIP_SEND_API_KEY'),
        'api_secret' => env('CHIP_SEND_API_SECRET'),
        'environment' => env('CHIP_ENVIRONMENT', 'sandbox'),
    ],
    
    'webhooks' => [
        'verify_signature' => true,
        'tolerance' => 300, // 5 minutes
    ],
];
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review our security policy on how to report security vulnerabilities.

## Credits

- [MasyukAI](https://github.com/masyukai)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
