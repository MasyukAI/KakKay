<p align="center">
  <img src="https://raw.githubusercontent.com/aiarmada/cart/main/art/banner.png" alt="AIArmada Cart" width="720" />
</p>

<p align="center">
  <a href="https://php.net"><img src="https://img.shields.io/badge/PHP-8.4-777BB4?style=flat-square&logo=php" alt="PHP 8.4"></a>
  <a href="https://laravel.com"><img src="https://img.shields.io/badge/Laravel-12-ff2d20?style=flat-square&logo=laravel" alt="Laravel 12"></a>
  <a href="https://pestphp.com"><img src="https://img.shields.io/badge/Tests-Pest%20v4-34d399?style=flat-square&logo=pest" alt="Pest"></a>
  <a href="LICENSE"><img src="https://img.shields.io/badge/License-MIT-4c1?style=flat-square" alt="MIT"></a>
</p>

# AIArmada Cart

> A production-grade, multi-instance shopping cart engine for Laravel 12, crafted for modern commerce applications.

AIArmada Cart delivers developer ergonomics paired with enterprise durability: optimistic locking for concurrency safety, dynamic pricing rules, powerful event hooks, and comprehensive documentation. Whether you're building an online storefront, a B2B quoting system, or a headless checkout API, this package provides the foundation you need—without vendor lock-in.

## ✨ Key Features

- 🚀 **Ready in minutes** – Composer install, Laravel auto-discovery, intuitive fluent API
- 💰 **Accurate totals** – Precision currency handling via [Akaunting Money](https://github.com/akaunting/money)
- 🎯 **Dynamic pricing** – Stackable conditions for discounts, taxes, fees, and shipping
- 🔄 **Flexible storage** – Session, cache, or database drivers with seamless identifier migration
- 🔐 **Concurrency safe** – Optimistic locking prevents race conditions in high-traffic scenarios
- 🎭 **Multi-instance** – Separate carts, wishlists, and quotes per user without collision
- 🔔 **Event-driven** – Comprehensive events for auditing and integration workflows
- ⚡ **Octane ready** – Works seamlessly with long-lived worker processes
- ✅ **Battle-tested** – Comprehensive Pest test suite with 100+ tests

# AIArmada Cart

> A production-grade, multi-instance shopping cart engine for Laravel 12, crafted for modern commerce teams.

AIArmada Cart pairs developer ergonomics with enterprise durability: optimistic locking, dynamic pricing rules, powerful analytics hooks, and batteries included documentation. Whether you’re building a classic storefront, a B2B quoting flow, or a headless checkout, this package gives you the building blocks you need—without vendor lock-in.

- 🚀 **Ready in minutes** – Composer install, auto-discovery, intuitive API.
- 🧮 **Accurate totals** – Akaunting\Money under the hood, stackable conditions, dynamic rules.
- ♻️ **Resilient storage** – Session, cache, and database drivers with identifier swapping.
- 📊 **Observability built in** – Metrics, conflict tracking, artisan dashboards.
- 🧱 **Flexible design** – Bring your own storage (session, cache, database with concurrency guards).
- � **Multi-currency aware** – Powered by [akaunting/money](https://github.com/akaunting/money) for precision.
- �🔐 **Safety first** – Built-in validation, payload limits, sanitised logging.
- ⚡ **Optional batteries** – Events, guest→user switching, multiple cart instances.
- 🏗 **Full-stack ready** – Easy integration with Laravel, Livewire, Filament.


## 📚 Documentation

Comprehensive documentation to get you from zero to production:

### Getting Started
- **[Installation & Setup](docs/getting-started.md)** – Get up and running in 5 minutes
- **[Core Concepts](docs/architecture.md)** – Understand the cart architecture
- **[Quick Examples](docs/examples.md)** – Copy-paste recipes for common scenarios

### Core Features
- **[Cart Operations](docs/cart-operations.md)** – Add, update, remove items and manage metadata
- **[Conditions & Pricing](docs/conditions.md)** – Build dynamic discounts, taxes, fees, and shipping rules
- **[Storage Drivers](docs/storage.md)** – Choose between session, cache, or database storage
- **[Money & Currency](docs/money-and-currency.md)** – Work with precise monetary values

### Advanced Topics
- **[User Migration](docs/identifiers-and-migration.md)** – Guest-to-user cart migration strategies
- **[Concurrency Control](docs/concurrency-and-retry.md)** – Handle conflicts in high-traffic scenarios
- **[Event System](docs/events.md)** – Hook into cart lifecycle events
- **[Laravel Octane](docs/octane.md)** – Deploy on long-lived worker processes

### Reference
- **[Configuration](docs/configuration.md)** – Complete configuration reference
- **[API Reference](docs/api-reference.md)** – Full API documentation
- **[Security Guide](docs/security.md)** – Best practices for production
- **[Testing Guide](docs/testing.md)** – Test your cart integration
- **[Troubleshooting](docs/troubleshooting.md)** – Solutions to common issues

📖 **[View Full Documentation](docs/index.md)**

## 🚀 Quick Start

### Installation

```bash
composer require aiarmada/cart
```

Laravel will auto-discover the service provider. No manual registration needed.

### Basic Usage

```php
use AIArmada\Cart\Facades\Cart;

// Add items to cart
Cart::add('sku-001', 'Premium Hoodie', 79.90, 2, [
    'size' => 'L',
    'color' => 'charcoal',
]);

// Apply discounts and taxes
Cart::addDiscount('new-customer', '10%');
Cart::addTax('vat', '8%');

// Get totals
$subtotal = Cart::subtotal()->format(); // "143.82"
$total = Cart::total()->format();       // "155.32"
$count = Cart::count();                 // 2

// Access items
$items = Cart::getItems();
foreach ($items as $item) {
    echo "{$item->name}: {$item->getSubtotal()->format()}\n";
}
```

### Multiple Instances

Manage separate carts, wishlists, and quote baskets simultaneously:

```php
// Shopping cart
Cart::add('product-1', 'Laptop', 999.00);

// Wishlist
Cart::instance('wishlist')->add('product-2', 'Monitor', 449.00);

// Get counts per instance
Cart::instance('default')->count();   // 1
Cart::instance('wishlist')->count();  // 1
```

### Guest-to-User Migration

Automatically migrate guest carts when users log in:

```php
// config/cart.php
'migration' => [
    'auto_migrate_on_login' => true,
    'merge_strategy' => 'add_quantities',
],
```

The cart seamlessly transfers from session to authenticated user with configurable merge strategies.

## 🏗️ Architecture Highlights

### Precision Money Handling
All monetary calculations use [`akaunting/laravel-money`](https://github.com/akaunting/money) to avoid floating-point precision issues. Every total, subtotal, and price returns a `Money` object with proper currency formatting.

### Flexible Storage Drivers

| Driver | Use Case | Persistence | Concurrency |
|--------|----------|-------------|-------------|
| **Session** | Quick prototypes, single-device carts | Session lifetime | Session locks |
| **Cache** | Multi-server, fast access | Configurable TTL | Redis locks (if supported) |
| **Database** | Cross-device, analytics, high traffic | Permanent | Optimistic locking |

### Event-Driven Architecture
Comprehensive events for every cart operation:
- `ItemAdded`, `ItemUpdated`, `ItemRemoved`
- `CartCleared`, `CartMerged`
- `ConditionAdded`, `ConditionRemoved`
- `MetadataAdded`, `MetadataRemoved`
- Consolidated `CartUpdated` event for all changes

### Conflict-Safe Concurrency
The database driver uses optimistic locking with version tracking:
- Automatic conflict detection
- Rich exception metadata with resolution suggestions
- Optional pessimistic locking for critical operations
- Configurable retry strategies

## 🧪 Testing & Quality

```bash
# Run test suite
vendor/bin/pest

# Run with coverage
vendor/bin/pest --coverage

# Format code
vendor/bin/pint

# Static analysis
vendor/bin/phpstan analyse
```

The package includes 100+ tests covering:
- Item operations and totals
- Condition calculations
- Storage driver implementations
- Concurrency scenarios
- Event dispatching
- Migration flows

## 🤝 Contributing

We welcome contributions! Please:

1. Fork the repository and create a feature branch
2. Follow the [coding guidelines](.ai/cart.md)
3. Write tests for new features
4. Run `vendor/bin/pint` before committing
5. Update documentation for public API changes
6. Submit a pull request with a clear description

### Development Setup

```bash
git clone https://github.com/aiarmada/cart.git
cd cart
composer install
vendor/bin/pest
```

## 📄 License

AIArmada Cart is open-sourced software licensed under the [MIT license](LICENSE).

## 🔒 Security

If you discover a security vulnerability, please email **security@aiarmada.dev** instead of using the issue tracker. All security vulnerabilities will be promptly addressed.

## 💬 Support

- 📖 **Documentation**: [docs/index.md](docs/index.md)
- 🐛 **Issues**: [GitHub Issues](https://github.com/aiarmada/cart/issues)
- 💡 **Discussions**: [GitHub Discussions](https://github.com/aiarmada/cart/discussions)

---

<p align="center">Made with ❤️ by <a href="https://aiarmada.com">AIArmada</a></p>
