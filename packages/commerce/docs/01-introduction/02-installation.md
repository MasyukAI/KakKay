# Installation Guide

## Requirements

Before installing AIArmada Commerce, ensure your environment meets these requirements:

- **PHP**: ^8.4
- **Laravel**: ^12.0
- **Composer**: ^2.0
- **Database**: PostgreSQL 14+ (primary), MySQL 8+, SQLite 3.35+

### Optional Requirements

- **Filament**: ^4.0 (for admin panels)
- **Redis**: ^6.0 (recommended for cart caching)
- **Node.js**: ^20.0 (for frontend assets)

## Installation Methods

### Method 1: Meta-Package (Recommended)

Install all packages at once for a complete e-commerce solution:

```bash
composer require aiarmada/commerce
```

This installs:
- ✅ Cart system
- ✅ CHIP payment gateway
- ✅ Voucher management
- ✅ J&T shipping
- ✅ Stock management
- ✅ All Filament admin panels
- ✅ Support utilities

### Method 2: Individual Packages

Install only what you need:

```bash
# Core packages
composer require aiarmada/cart
composer require aiarmada/chip
composer require aiarmada/vouchers
composer require aiarmada/jnt
composer require aiarmada/stock

# Filament plugins (optional)
composer require aiarmada/filament-cart
composer require aiarmada/filament-chip
composer require aiarmada/filament-vouchers

# Support utilities (auto-installed as dependency)
composer require aiarmada/commerce-support
```

### Method 3: Development Version

For testing unreleased features:

```bash
composer require aiarmada/commerce:dev-main
```

## Configuration

### 1. Publish Configuration Files

```bash
# Publish all commerce configurations
php artisan vendor:publish --tag=commerce-config

# Or publish individually
php artisan vendor:publish --tag=cart-config
php artisan vendor:publish --tag=chip-config
php artisan vendor:publish --tag=vouchers-config
php artisan vendor:publish --tag=jnt-config
php artisan vendor:publish --tag=stock-config
```

This creates:
- `config/cart.php`
- `config/chip.php`
- `config/vouchers.php`
- `config/jnt.php`
- `config/stock.php`

### 2. Environment Variables

Add to your `.env` file:

```env
# Cart Configuration
CART_STORAGE_DRIVER=database
CART_DEFAULT_CURRENCY=MYR
CART_SESSION_KEY=shopping_cart

# CHIP Payment Gateway
CHIP_COLLECT_API_KEY=your-collect-api-key
CHIP_COLLECT_BRAND_ID=your-brand-id
CHIP_COLLECT_ENVIRONMENT=sandbox
CHIP_SEND_API_KEY=your-send-api-key
CHIP_SEND_API_SECRET=your-send-secret
CHIP_SEND_ENVIRONMENT=sandbox

# Vouchers
VOUCHER_CODE_LENGTH=8
VOUCHER_CODE_PREFIX=VCH
VOUCHER_AUTO_APPLY=false

# J&T Express
JNT_API_KEY=your-jnt-api-key
JNT_API_SECRET=your-jnt-secret
JNT_ENVIRONMENT=sandbox

# Stock Management
STOCK_RESERVATION_TIMEOUT=1800
STOCK_LOW_THRESHOLD=10
```

### 3. Database Migrations

Run migrations to create required tables:

```bash
php artisan migrate
```

This creates tables for:
- `carts`, `cart_items`, `cart_conditions`
- `chip_purchases`, `chip_payments`, `chip_clients`, `chip_webhooks`
- `vouchers`, `voucher_redemptions`, `voucher_conditions`
- `jnt_shipments`, `jnt_tracking_events`
- `stock_items`, `stock_movements`, `stock_reservations`

### 4. Register Service Providers (Laravel 11+)

Service providers are auto-discovered. Verify in `bootstrap/providers.php`:

```php
return [
    App\Providers\AppServiceProvider::class,
    // Commerce providers auto-registered via composer.json
];
```

### 5. Register Filament Plugins (Optional)

If using Filament admin panels, register plugins in `app/Providers/Filament/AdminPanelProvider.php`:

```php
use AIArmada\FilamentCart\FilamentCartPlugin;
use AIArmada\FilamentChip\FilamentChipPlugin;
use AIArmada\FilamentVouchers\FilamentVouchersPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->default()
        ->id('admin')
        ->path('admin')
        ->plugins([
            FilamentCartPlugin::make(),
            FilamentChipPlugin::make(),
            FilamentVouchersPlugin::make(),
        ]);
}
```

## Quick Start Verification

### Test Cart System

```bash
php artisan tinker
```

```php
use AIArmada\Cart\Facades\Cart;

// Add item to cart
Cart::add('test-001', 'Test Product', 9999, 1);

// View cart
Cart::content();

// Get total
Cart::total(); // Returns Money instance
```

### Test CHIP Integration

```php
use AIArmada\Chip\Facades\Chip;

// Create test purchase
$purchase = Chip::createPurchase([
    'amount' => 10000, // RM 100.00
    'currency' => 'MYR',
    'reference' => 'test-' . uniqid(),
    'client' => [
        'email' => 'test@example.com',
    ],
    'send_receipt' => false,
]);

echo $purchase->checkout_url;
```

### Test Voucher System

```php
use AIArmada\Vouchers\Models\Voucher;

// Create voucher
$voucher = Voucher::create([
    'code' => 'WELCOME10',
    'name' => 'Welcome Discount',
    'type' => 'percentage',
    'discount_amount' => 10,
    'is_active' => true,
]);

// Validate voucher
$voucher->canBeRedeemed(); // Returns ValidationResult
```

## Filament Panel Access

After installation, access the admin panel:

1. **Create Admin User**:
```bash
php artisan make:filament-user
```

2. **Navigate**: `https://your-app.test/admin`

3. **Available Resources**:
   - **Commerce > Carts**: Manage shopping carts
   - **Commerce > Cart Items**: View cart contents
   - **Payments > Purchases**: CHIP purchases
   - **Payments > Payments**: Payment transactions
   - **Payments > Clients**: Customer payment profiles
   - **Vouchers > Vouchers**: Create and manage vouchers
   - **Vouchers > Redemptions**: Track voucher usage
   - **Shipping > Shipments**: J&T shipments
   - **Inventory > Stock**: Stock levels

## Storage Driver Configuration

### Session Storage (Default)

No additional setup required. Best for simple applications.

```php
// config/cart.php
'storage_driver' => 'session',
```

### Cache Storage

Requires cache driver (Redis recommended):

```bash
composer require predis/predis
```

```env
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

```php
// config/cart.php
'storage_driver' => 'cache',
'cache' => [
    'ttl' => 3600, // 1 hour
],
```

### Database Storage

Best for production with cart persistence:

```php
// config/cart.php
'storage_driver' => 'database',
'database' => [
    'connection' => null, // Uses default
    'enable_optimistic_locking' => true,
],
```

## Webhook Configuration

### CHIP Webhooks

Register webhook URL in CHIP dashboard:

```
https://your-app.com/webhooks/chip/{webhook_id}
```

Route automatically registered by package.

### J&T Webhooks

Register webhook URL in J&T portal:

```
https://your-app.com/webhooks/jnt/{webhook_id}
```

## Testing Installation

Run package test suites:

```bash
# Test all packages
composer test

# Test specific package
vendor/bin/pest packages/cart/tests
vendor/bin/pest packages/chip/tests
vendor/bin/pest packages/vouchers/tests
```

## Common Issues

### Issue: "Class not found" errors

**Solution**: Clear autoload cache
```bash
composer dump-autoload
php artisan optimize:clear
```

### Issue: Migration conflicts

**Solution**: Check existing table names
```bash
php artisan migrate:status
```

If tables exist, skip specific migrations or use `--force`.

### Issue: Service provider not registered

**Solution**: Ensure `composer.json` has provider discovery:
```json
{
    "extra": {
        "laravel": {
            "providers": [
                "AIArmada\\Cart\\CartServiceProvider"
            ]
        }
    }
}
```

### Issue: Filament plugin not showing

**Solution**: Clear Filament cache
```bash
php artisan filament:clear-cache
php artisan filament:cache-components
```

## Next Steps

Now that you're installed, explore:

- **[Cart Basics](../02-getting-started/01-cart-basics.md)**: Build your first shopping cart
- **[Payment Integration](../02-getting-started/02-payment-integration.md)**: Accept payments with CHIP
- **[Voucher System](../02-getting-started/03-voucher-system.md)**: Create discount codes
- **[Configuration Reference](../03-packages/)**: Deep dive into each package

## Upgrading

For upgrade instructions from older versions, see:

- **[Upgrade Guide](../05-upgrade-guide.md)**

## Getting Help

- **Documentation Issues**: [GitHub Issues](https://github.com/aiarmada/commerce/issues)
- **Questions**: [GitHub Discussions](https://github.com/aiarmada/commerce/discussions)
- **Commercial Support**: info@aiarmada.com
