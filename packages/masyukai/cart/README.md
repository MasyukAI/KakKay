<p align="center">
  <img src="https://raw.githubusercontent.com/masyukai/cart/main/art/banner.png" alt="MasyukAI Cart" width="720" />
</p>

<p align="center">
  <a href="https://php.net"><img src="https://img.shields.io/badge/PHP-8.4-777BB4?style=flat-square&logo=php" alt="PHP 8.4"></a>
  <a href="https://laravel.com"><img src="https://img.shields.io/badge/Laravel-12-ff2d20?style=flat-square&logo=laravel" alt="Laravel 12"></a>
  <a href="https://pestphp.com"><img src="https://img.shields.io/badge/Tests-Pest%20v4-34d399?style=flat-square&logo=pest" alt="Pest"></a>
  <a href="LICENSE"><img src="https://img.shields.io/badge/License-MIT-4c1?style=flat-square" alt="MIT"></a>
</p>

# MasyukAI Cart

> A production-grade, multi-instance shopping cart engine for Laravel 12, crafted for modern commerce teams.

MasyukAI Cart pairs developer ergonomics with enterprise durability: optimistic locking, dynamic pricing rules, powerful analytics hooks, and batteries included documentation. Whether you’re building a classic storefront, a B2B quoting flow, or a headless checkout, this package gives you the building blocks you need—without vendor lock-in.

- 🚀 **Ready in minutes** – Composer install, auto-discovery, intuitive API.
- 🧮 **Accurate totals** – Akaunting\Money under the hood, stackable conditions, dynamic rules.
- ♻️ **Resilient storage** – Session, cache, and database drivers with identifier swapping.
- 📊 **Observability built in** – Metrics, conflict tracking, artisan dashboards.
- 🔐 **Safety first** – Strict validation, payload limits, sanitised logging.

## Documentation

| Guide | Description |
| --- | --- |
| [Getting Started](docs/getting-started.md) | Installation checklist, first code sample, next steps. |
| [Cart Operations](docs/cart-operations.md) | All item, total, metadata, and instance APIs. |
| [Conditions & Discounts](docs/conditions.md) | Build complex promotions, taxes, shipping fees, and dynamic rules. |
| [Configuration Reference](docs/configuration.md) | Every config flag, explained and cross-linked. |
| [Storage Drivers](docs/storage.md) | Session vs cache vs database, plus custom driver guidance. |
| [Identifiers & Migration](docs/identifiers-and-migration.md) | Guest → user flows, merge strategies, identifier swaps. |
| [Concurrency & Retry](docs/concurrency-and-retry.md) | Optimistic locking, conflict handling, smart retry patterns. |
| [Metrics & Observability](docs/metrics-and-observability.md) | Capture cart signals and navigate the artisan metrics UI. |
| [Money & Currency](docs/money-and-currency.md) | Working with Money objects and multi-currency strategies. |
| [Laravel Octane](docs/octane.md) | Deploy safely on long-lived workers. |
| [Testing Guide](docs/testing.md) | Pest patterns, Testbench setup, recommended assertions. |
| [Security Checklist](docs/security.md) | Guardrails for payload size, PII handling, logging. |
| [Troubleshooting](docs/troubleshooting.md) | Quick fixes for common integration questions. |
| [API Reference](docs/api-reference.md) | Facade, services, collections, console commands. |
| [Recipes & Examples](docs/examples.md) | Drop-in snippets for everyday scenarios. |

Want the full tour? Start at [docs/index.md](docs/index.md).

## Quick Start

```bash
composer require masyukai/cart
```

```php
use MasyukAI\Cart\Facades\Cart;

Cart::add('sku-1', 'Limited Hoodie', 79.90, 1, [
    'size' => 'L',
    'color' => 'charcoal',
]);

Cart::addDiscount('new-customer', '10%');
Cart::addTax('sales-tax', '8.25%');

$total = Cart::total()->format();
$count = Cart::count();

printf("%s items → %s\n", $count, $total);
// 1 items → 71.15
```

- Need raw numbers? `Cart::getRawTotal()` returns `float` for further math.
- Want to track conversions? `Cart::recordConversion([...])` feeds the metrics dashboard.

## Feature Highlights

### Multi-Instance by Design

Serve different journeys simultaneously—shopping cart, wishlist, quick quotes—without clobbering data.

```php
$cart = Cart::getCurrentCart();        // default instance
$wishlist = Cart::getCartInstance('wishlist');

$wishlist->add('dream-phone', 'Concept Phone', 1299.00);
Cart::setInstance('wishlist');

Cart::countItems(); // 1
```

### Dynamic Pricing Rules

Layer discounts, fees, and shipping logic with predictable ordering.

```php
Cart::addCondition(new CartCondition(
    name: 'vip-tier',
    type: 'discount',
    target: 'subtotal',
    value: '-15%',
    attributes: ['source' => 'VIP'],
));

Cart::addShipping('express', '25.00', 'express', ['eta' => '1-2 days']);
```

Dynamic conditions respond to live cart state—see [Conditions & Discounts](docs/conditions.md).

### Durable Storage with Optimistic Locking

Use the database driver for cross-device carts and analytics. Conflicts raise `CartConflictException` with actionable metadata, and `CartRetryService` provides exponential backoff helpers out of the box.

```php
app(CartRetryService::class)->executeWithSmartRetry(function () {
    Cart::update('sku-1', ['quantity' => 3]);
});
```

### Metrics & Observability

Get operational visibility in one command:

```bash
php artisan cart:metrics --json
```

Track conversions vs abandonments, conflict severities, and slow operations. Configure logging channels via `CART_METRICS_LOG_CHANNEL`.

## Testing & Tooling

- Tests: `vendor/bin/pest`
- Coding standards: `vendor/bin/pint --dirty`
- Docs live in [`docs/`](docs/index.md); keep them updated when behaviour changes.

## Contributing

Contributions are welcome! Please:

1. Read the [Coding Guidelines](.ai/guidelines/laravel-cart.md).
2. Fork the repo and create a feature branch.
3. Run the focused test suite relevant to your change.
4. Update documentation when altering public behaviour.
5. Submit a pull request describing the motivation and impact.

Security issues? Email security@masyukai.dev instead of opening a public issue.

## License

MasyukAI Cart is open-sourced software licensed under the [MIT license](LICENSE).
