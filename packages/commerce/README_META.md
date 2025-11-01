# AIArmada Commerce

> A comprehensive commerce ecosystem for Laravel 12 with cart, payments, vouchers, shipping, inventory, and Filament admin interfaces.

<p align="center">
  <a href="https://packagist.org/packages/aiarmada/commerce"><img src="https://img.shields.io/packagist/v/aiarmada/commerce.svg?style=flat-square" alt="Packagist"></a>
  <a href="https://packagist.org/packages/aiarmada/commerce"><img src="https://img.shields.io/packagist/dt/aiarmada/commerce.svg?style=flat-square" alt="Downloads"></a>
  <a href="https://github.com/aiarmada/commerce/actions"><img src="https://img.shields.io/github/actions/workflow/status/aiarmada/commerce/tests.yml?branch=main&label=tests&style=flat-square" alt="Tests"></a>
  <a href="LICENSE"><img src="https://img.shields.io/badge/License-MIT-4c1?style=flat-square" alt="MIT"></a>
</p>

<p align="center">
  <a href="https://php.net"><img src="https://img.shields.io/badge/PHP-8.4-777BB4?style=flat-square&logo=php" alt="PHP 8.4"></a>
  <a href="https://laravel.com"><img src="https://img.shields.io/badge/Laravel-12-ff2d20?style=flat-square&logo=laravel" alt="Laravel 12"></a>
  <a href="https://filamentphp.com"><img src="https://img.shields.io/badge/Filament-4-f59e0b?style=flat-square" alt="Filament 4"></a>
  <a href="https://pestphp.com"><img src="https://img.shields.io/badge/Tests-Pest%20v4-34d399?style=flat-square&logo=pest" alt="Pest v4"></a>
</p>

---

## What is AIArmada Commerce?

AIArmada Commerce is a **production-ready, modular commerce ecosystem** for Laravel applications. It provides everything you need to build modern e-commerce experiences: shopping carts, payment processing, voucher systems, shipping integration, inventory management, and beautiful Filament admin interfaces.

**Why teams choose AIArmada Commerce:**

| Feature | Benefit |
|---------|---------|
| 🎯 **Modular Architecture** | Install only what you need—use cart without payments, payments without inventory |
| ⚡ **Performance First** | Optimistic locking, event-driven sync, normalized data for 100× faster queries |
| 🔐 **Production Ready** | Comprehensive test suites, PHPStan level 6, PHP 8.4 types everywhere |
| 🎨 **Filament Native** | Beautiful admin interfaces that feel like first-party Filament resources |
| 📦 **Batteries Included** | Dynamic pricing, multi-currency, webhooks, QR codes, and more out of the box |
| 🔄 **Event Driven** | Rich event system for auditing, analytics, and custom workflows |

---

## Package Ecosystem

AIArmada Commerce is composed of **10 focused packages** that work seamlessly together:

### Core Packages

| Package | Description | Version |
|---------|-------------|---------|
| [**aiarmada/cart**](packages/cart) | Multi-instance shopping cart with dynamic pricing | [![Packagist](https://img.shields.io/packagist/v/aiarmada/cart.svg?style=flat-square)](https://packagist.org/packages/aiarmada/cart) |
| [**aiarmada/chip**](packages/chip) | CHIP payment gateway integration (Collect & Send) | [![Packagist](https://img.shields.io/packagist/v/aiarmada/chip.svg?style=flat-square)](https://packagist.org/packages/aiarmada/chip) |
| [**aiarmada/vouchers**](packages/vouchers) | Flexible voucher and coupon system | [![Packagist](https://img.shields.io/packagist/v/aiarmada/vouchers.svg?style=flat-square)](https://packagist.org/packages/aiarmada/vouchers) |
| [**aiarmada/jnt**](packages/jnt) | J&T Express Malaysia shipping integration | [![Packagist](https://img.shields.io/packagist/v/aiarmada/jnt.svg?style=flat-square)](https://packagist.org/packages/aiarmada/jnt) |
| [**aiarmada/stock**](packages/stock) | Inventory and stock management system | [![Packagist](https://img.shields.io/packagist/v/aiarmada/stock.svg?style=flat-square)](https://packagist.org/packages/aiarmada/stock) |
| [**aiarmada/docs**](packages/docs) | Document generation with PDF support | [![Packagist](https://img.shields.io/packagist/v/aiarmada/docs.svg?style=flat-square)](https://packagist.org/packages/aiarmada/docs) |

### Filament Admin Plugins

| Package | Description | Version |
|---------|-------------|---------|
| [**aiarmada/filament-cart**](packages/filament-cart) | Filament admin interface for cart management | [![Packagist](https://img.shields.io/packagist/v/aiarmada/filament-cart.svg?style=flat-square)](https://packagist.org/packages/aiarmada/filament-cart) |
| [**aiarmada/filament-chip**](packages/filament-chip) | Filament admin interface for CHIP payments | [![Packagist](https://img.shields.io/packagist/v/aiarmada/filament-chip.svg?style=flat-square)](https://packagist.org/packages/aiarmada/filament-chip) |
| [**aiarmada/filament-vouchers**](packages/filament-vouchers) | Filament admin interface for voucher management | [![Packagist](https://img.shields.io/packagist/v/aiarmada/filament-vouchers.svg?style=flat-square)](https://packagist.org/packages/aiarmada/filament-vouchers) |

### Foundation

| Package | Description | Version |
|---------|-------------|---------|
| [**aiarmada/commerce-support**](packages/support) | Shared utilities, exceptions, HTTP clients, and helpers | [![Packagist](https://img.shields.io/packagist/v/aiarmada/commerce-support.svg?style=flat-square)](https://packagist.org/packages/aiarmada/commerce-support) |

---

## Installation

### Option 1: Install Everything (Recommended)

The meta-package installs all packages for a complete commerce experience:

```bash
composer require aiarmada/commerce
```

This installs:
- Shopping cart with dynamic pricing
- CHIP payment integration
- Voucher/coupon system
- J&T Express shipping
- Stock/inventory management
- Document generation
- All Filament admin plugins
- Shared utilities

### Option 2: Install Individual Packages

Pick and choose only what you need:

```bash
# Cart only
composer require aiarmada/cart

# Cart + Filament admin
composer require aiarmada/cart aiarmada/filament-cart

# Payments only
composer require aiarmada/chip

# Custom combination
composer require aiarmada/cart aiarmada/vouchers aiarmada/filament-cart
```

### Configuration

Publish configurations for the packages you need:

```bash
# Cart
php artisan vendor:publish --tag="cart-config"
php artisan vendor:publish --tag="cart-migrations"

# CHIP Payments
php artisan vendor:publish --tag="chip-config"

# Vouchers
php artisan vendor:publish --tag="vouchers-config"
php artisan vendor:publish --tag="vouchers-migrations"

# All configurations
php artisan vendor:publish --provider="AIArmada\Cart\CartServiceProvider"
php artisan vendor:publish --provider="AIArmada\Chip\ChipServiceProvider"
php artisan vendor:publish --provider="AIArmada\Vouchers\VoucherServiceProvider"
```

Run migrations:

```bash
php artisan migrate
```

---

## Quick Start Examples

### Shopping Cart

```php
use AIArmada\Cart\Facades\Cart;

// Add items
Cart::add('prod-001', 'Premium Hoodie', 79.90, 2, [
    'size' => 'L',
    'color' => 'Navy',
]);

// Apply discount
Cart::applyCondition([
    'name' => 'summer-sale',
    'type' => 'discount',
    'target' => 'subtotal',
    'value' => '-10%',
]);

// Get total
$total = Cart::total(); // Money instance: RM 143.82

// Checkout
$cartData = Cart::content();
```

### CHIP Payment

```php
use AIArmada\Chip\Facades\Chip;

// Create purchase
$purchase = Chip::createPurchase([
    'client' => [
        'email' => 'customer@example.com',
        'full_name' => 'Jane Doe',
    ],
    'purchase' => [
        'currency' => 'MYR',
        'products' => [
            ['name' => 'Premium Hoodie', 'price' => 7990], // cents
        ],
        'success_redirect' => route('payments.success'),
        'failure_redirect' => route('payments.failed'),
    ],
]);

// Redirect to payment
return redirect($purchase->checkout_url);
```

### Vouchers

```php
use AIArmada\Vouchers\Facades\Voucher;

// Create voucher
$voucher = Voucher::create([
    'code' => 'WELCOME2025',
    'type' => 'percentage',
    'value' => '20',
    'usage_limit' => 100,
    'valid_until' => now()->addDays(30),
]);

// Validate and apply
if (Voucher::validate('WELCOME2025')) {
    $discount = Voucher::apply('WELCOME2025', Cart::subtotal());
    Cart::applyCondition([
        'name' => 'voucher-welcome2025',
        'type' => 'discount',
        'target' => 'subtotal',
        'value' => "-{$discount->getAmount()}",
    ]);
}
```

### J&T Express Shipping

```php
use AIArmada\Jnt\Facades\JntExpress;

// Get shipping rate
$rate = JntExpress::getRate(
    weight: 2.5, // kg
    origin: '50000',
    destination: '10000',
    serviceType: 'EZ'
);

// Create order
$order = JntExpress::createOrder([
    'sender' => [...],
    'receiver' => [...],
    'items' => [...],
]);

// Track shipment
$tracking = JntExpress::track($order->awb_number);
```

### Stock Management

```php
use AIArmada\Stock\Facades\Stock;

// Record stock in
Stock::in('prod-001', 100, 'warehouse-a', [
    'supplier' => 'Supplier XYZ',
    'batch' => 'B2025-001',
]);

// Check availability
$available = Stock::available('prod-001'); // 100

// Reserve stock
$reservation = Stock::reserve('prod-001', 5, 'order-123');

// Record stock out
Stock::out('prod-001', 5, 'warehouse-a', [
    'order' => 'order-123',
]);
```

---

## Filament Admin Integration

Register Filament plugins in your panel provider:

```php
use AIArmada\FilamentCart\FilamentCart;
use AIArmada\FilamentChip\FilamentChipPlugin;
use AIArmada\FilamentVouchers\FilamentVouchers;
use Filament\Panel;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...existing configuration
        ->plugins([
            FilamentCart::make(),
            FilamentChipPlugin::make(),
            FilamentVouchers::make(),
        ]);
}
```

This adds:
- **Cart Management** – View carts, items, conditions with real-time sync
- **Payment Dashboard** – CHIP purchases, transactions, webhooks
- **Voucher Admin** – Create, manage, track redemptions
- **Analytics Widgets** – Revenue, conversion, inventory metrics

---

## Features by Package

### 🛒 Cart Package
- Multi-instance support (cart, wishlist, quote, layaway)
- Dynamic pricing with stackable conditions
- Storage drivers: session, cache, database
- Optimistic locking for concurrency
- Event-driven architecture
- Guest-to-user migration
- Money/currency precision
- Comprehensive test suite

### 💳 CHIP Package
- CHIP Collect (payments, refunds, subscriptions)
- CHIP Send (payouts, disbursements)
- Webhook signature verification
- Fluent purchase builder
- Health check command
- Automatic retry logic
- Request/response logging
- Cache support

### 🎟️ Vouchers Package
- Multiple discount types (fixed, percentage, free shipping)
- Usage limits (total and per-user)
- Date range validation
- Manual and automatic redemption
- Multi-staff/multi-store support
- Cart integration
- Usage analytics
- QR code generation

### 📦 J&T Express Package
- Order creation and tracking
- Price calculation
- Service type support (standard, express)
- Address validation
- Shipping label generation
- Webhook integration
- Rate limiting
- Health checks

### 📊 Stock Package
- UUID-based tracking
- Movement recording (in, out, adjustment)
- Reservation system with expiry
- Multi-location support
- Batch and serial numbers
- Low stock alerts
- Audit trail
- Concurrent operation locking

### 📄 Docs Package
- Invoice generation
- Receipt generation
- Shipping label generation
- PDF rendering (DomPDF)
- Customizable templates
- Multi-currency support
- Logo/branding
- Template caching

---

## Requirements

- PHP ^8.4
- Laravel ^12.0
- For Filament plugins: Filament ^4.0

---

## Testing

All packages include comprehensive Pest v4 test suites:

```bash
# Test all packages
composer test

# Test with coverage
composer test-coverage

# Test specific package
cd packages/cart && vendor/bin/pest

# Format code
composer format

# Static analysis
composer phpstan

# Run all quality checks
composer ci
```

---

## Documentation

Each package has detailed documentation:

- [Cart Documentation](packages/cart/README.md)
- [CHIP Documentation](packages/chip/README.md)
- [Vouchers Documentation](packages/vouchers/README.md)
- [J&T Express Documentation](packages/jnt/README.md)
- [Stock Documentation](packages/stock/README.md)
- [Docs Documentation](packages/docs/README.md)
- [Filament Cart Documentation](packages/filament-cart/README.md)
- [Filament CHIP Documentation](packages/filament-chip/README.md)
- [Filament Vouchers Documentation](packages/filament-vouchers/README.md)
- [Support Utilities Documentation](packages/support/README.md)

---

## Monorepo Structure

```
aiarmada/commerce/
├── packages/
│   ├── support/           # Foundation utilities
│   ├── cart/              # Shopping cart
│   ├── chip/              # CHIP payments
│   ├── vouchers/          # Vouchers/coupons
│   ├── jnt/               # J&T Express
│   ├── stock/             # Inventory
│   ├── docs/              # Document generation
│   ├── filament-cart/     # Filament cart plugin
│   ├── filament-chip/     # Filament CHIP plugin
│   └── filament-vouchers/ # Filament vouchers plugin
├── tests/                 # Integration tests
├── docs/                  # Ecosystem documentation
├── composer.json          # Meta-package
└── monorepo-builder.php   # Monorepo configuration
```

---

## Contributing

We welcome contributions! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

### Development Workflow

1. Fork and clone the repository
2. Install dependencies: `composer install`
3. Create a feature branch
4. Make your changes with tests
5. Run quality checks: `composer ci`
6. Submit a pull request

---

## Security

If you discover security vulnerabilities, please email security@aiarmada.com instead of using the issue tracker.

---

## Changelog

All notable changes are documented in [CHANGELOG.md](CHANGELOG.md).

For package-specific changes, see individual package CHANGELOGs:
- [Cart](packages/cart/CHANGELOG.md)
- [CHIP](packages/chip/CHANGELOG.md)
- [Vouchers](packages/vouchers/CHANGELOG.md)
- [J&T Express](packages/jnt/CHANGELOG.md)
- [Stock](packages/stock/CHANGELOG.md)
- [Docs](packages/docs/CHANGELOG.md)
- [Filament Cart](packages/filament-cart/CHANGELOG.md)
- [Filament CHIP](packages/filament-chip/CHANGELOG.md)
- [Filament Vouchers](packages/filament-vouchers/CHANGELOG.md)
- [Support](packages/support/CHANGELOG.md)

---

## Credits

- [AIArmada Team](https://aiarmada.com)
- [All Contributors](https://github.com/aiarmada/commerce/contributors)

Built with ❤️ for the Laravel community.

---

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
