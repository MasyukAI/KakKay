# Commerce Monorepo Documentation

> **Note:** For package-specific documentation, see each package's README.md file.

## 📦 Packages

### Core Packages
- **[Cart](../packages/cart/README.md)** - Multi-instance shopping cart with dynamic pricing
- **[Stock](../packages/stock/README.md)** - Inventory management and tracking
- **[Vouchers](../packages/vouchers/README.md)** - Discount vouchers and promotions

### Integration Packages
- **[CHIP](../packages/chip/README.md)** - CHIP payment gateway integration
- **[J&T Express](../packages/jnt/README.md)** - J&T Express shipping integration

### Filament Packages
- **[Filament Cart](../packages/filament-cart/README.md)** - Admin panel for cart management
- **[Filament CHIP](../packages/filament-chip/README.md)** - Admin panel for payment management

---

## 🚀 Quick Start

### Installation

```bash
composer require aiarmada/commerce
```

### Basic Usage

```php
use AIArmada\Cart\Facades\Cart;

// Add item to cart
Cart::add([
    'id' => 1,
    'name' => 'Product Name',
    'price' => 99.99,
    'quantity' => 2,
]);

// Apply discount
Cart::condition([
    'name' => 'SALE10',
    'type' => 'discount',
    'value' => '-10%',
]);

// Get total
$total = Cart::total();
```

---

## 📚 Documentation Index

### Getting Started
- [Installation](./installation.md)
- [Configuration](./configuration.md)
- [Quick Start Guide](./quickstart.md)

### Core Features
- [Cart Management](./cart/index.md)
- [Dynamic Pricing & Conditions](./cart/conditions.md)
- [Storage Drivers](./cart/storage.md)
- [Multi-Instance Carts](./cart/instances.md)
- [Events](./cart/events.md)

### Integrations
- [CHIP Payment Gateway](./chip/index.md)
- [J&T Express Shipping](./jnt/index.md)

### Advanced Topics
- [Concurrency & Locking](./advanced/concurrency.md)
- [Currency Handling](./advanced/currency.md)
- [Testing](./advanced/testing.md)
- [Performance](./advanced/performance.md)

### Filament Admin
- [Cart Management UI](./filament/cart.md)
- [Payment Management UI](./filament/chip.md)

---

## 🛠️ Development

- [Contributing](../CONTRIBUTING.md)
- [Architecture](./development/architecture.md)
- [Testing Guide](./development/testing.md)
- [Monorepo Structure](./development/monorepo.md)

---

## 📖 API Reference

- [Cart API](./api/cart.md)
- [CHIP API](./api/chip.md)
- [J&T API](./api/jnt.md)
- [Stock API](./api/stock.md)
- [Vouchers API](./api/vouchers.md)

---

## 🔗 External Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Filament Documentation](https://filamentphp.com/docs)
- [CHIP API Documentation](https://gate.chip-in.asia/docs)
- [J&T Express API Documentation](https://developers.jtexpress.my/)

---

## 💬 Support

- **Questions?** [Open a Discussion](https://github.com/masyukai/kakkay/discussions)
- **Bug Reports?** [Create an Issue](https://github.com/masyukai/kakkay/issues)
- **Security Issues?** Email security@example.com

---

## 📄 License

The Commerce monorepo is open-sourced software licensed under the [MIT license](../LICENSE).
