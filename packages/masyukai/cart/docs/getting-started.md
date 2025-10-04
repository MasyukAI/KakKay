# Getting Started

This guide gets a fresh Laravel 12 project running MasyukAI Cart in minutes.

## Requirements

| Requirement | Version |
| --- | --- |
| PHP | 8.4.x |
| Laravel | 12.x |
| Database (optional) | Any Laravel-supported driver if you choose the database storage driver |

No additional PHP extensions are required beyond Laravel’s defaults.

## Installation

```bash
composer require masyukai/cart
```

Laravel auto-discovers the service provider. No manual registration is required.

> **Tip:** When contributing or installing from source, run `composer install` followed by `vendor/bin/pest` to confirm the package is green.

## First Run Checklist

1. **Clear caches** if you previously cached config: `php artisan config:clear`.
2. **Publish assets** only if you need to customize them:
   - `php artisan vendor:publish --tag=cart-config`
   - `php artisan vendor:publish --tag=cart-migrations`
3. **Choose a storage driver**. Session storage works immediately; database or cache drivers require config tweaks (see [Storage Drivers](storage.md)).

## Your First Cart Interaction

```php
use MasyukAI\Cart\Facades\Cart;

Cart::add('sku-1', 'Starter Product', 49.90, 1, [
    'color' => 'black',
]);

echo Cart::total()->format();        // "49.90"
echo Cart::count();                  // 1
echo Cart::get('sku-1')->name;       // "Starter Product"
```

Every monetary response is an [`Akaunting\Money\Money`](https://github.com/akaunting/money) instance. Use `->getAmount()` for the raw numeric value or `->format()` for display.

## Verifying the Package

Run a smoke test inside `artisan tinker` or a feature test:

```php
Cart::clear();
Cart::add('sku-99', 'Verifiable Widget', 10.00, 2);

expect(Cart::total()->getAmount())->toBe(20.00);
```

If you’re using the database driver, ensure the migration has run and the `carts` table exists.

## Next Steps

- Browse [Cart Operations](cart-operations.md) to learn about updates, removals, and metadata.
- Review [Configuration](configuration.md) to align the package with your environment.
- Jump to [Testing](testing.md) when you’re ready to automate coverage.
