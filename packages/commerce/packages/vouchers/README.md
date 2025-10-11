# AIArmada Cart Vouchers

> Professional voucher and coupon system for AIArmada Cart

Add powerful voucher functionality to your Laravel shopping cart with support for percentage discounts, fixed amounts, free shipping, usage limits, and advanced validation rules.

## ✨ Features

- 🎫 **Multiple Voucher Types** - Percentage, fixed amount, free shipping
- 🔒 **Usage Limits** - Global limits and per-user restrictions
- 📅 **Time-Based** - Start and expiry dates for campaigns
- 🎯 **Targeted Discounts** - Apply to specific products or categories
- 💰 **Smart Constraints** - Minimum cart values, maximum discounts
- 📊 **Usage Tracking** - Complete history of voucher applications
- ⚡ **Real-Time Validation** - Instant feedback on voucher validity
- 🔐 **Secure** - Built-in validation and fraud prevention

## 📦 Installation

```bash
composer require aiarmada/cart-vouchers
```

Publish the configuration and migrations:

```bash
php artisan vendor:publish --tag=vouchers-config
php artisan vendor:publish --tag=vouchers-migrations
```

Run migrations:

```bash
php artisan migrate
```

## 🚀 Quick Start

### Create a Voucher

```php
use AIArmada\Cart\Vouchers\Facades\Voucher;
use AIArmada\Cart\Vouchers\Enums\VoucherType;

$voucher = Voucher::create([
    'code' => 'SUMMER2024',
    'name' => 'Summer Sale 2024',
    'description' => '20% off your entire order',
    'type' => VoucherType::Percentage,
    'value' => 20.00,
    'min_cart_value' => 50.00,
    'usage_limit' => 1000,
    'usage_limit_per_user' => 1,
    'starts_at' => now(),
    'expires_at' => now()->addMonths(3),
]);
```

### Apply to Cart

```php
use AIArmada\Cart\Facades\Cart;

try {
    Cart::applyVoucher('SUMMER2024');
    
    $total = Cart::getTotal();
    echo "Total with voucher: {$total->format()}";
    
} catch (\AIArmada\Cart\Vouchers\Exceptions\VoucherNotFoundException $e) {
    // Voucher doesn't exist
} catch (\AIArmada\Cart\Vouchers\Exceptions\VoucherExpiredException $e) {
    // Voucher has expired
}
```

## 📚 Documentation

- [Installation & Setup](docs/installation.md)
- [Creating Vouchers](docs/creating-vouchers.md)
- [Validation Rules](docs/validation-rules.md)
- [Cart Integration](docs/cart-integration.md)
- [Usage Tracking](docs/usage-tracking.md)
- [API Reference](docs/api-reference.md)

## 🧪 Testing

```bash
composer test
```

## 📝 Requirements

- PHP 8.2+
- Laravel 12+
- AIArmada Cart 2.0+

## 📄 License

MIT License. See [LICENSE](LICENSE) for details.

## 🤝 Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](../../CONTRIBUTING.md) for details.

## 🔗 Related Packages

- [aiarmada/cart](https://github.com/aiarmada/cart) - Core shopping cart (required)
- [aiarmada/filament-cart](https://github.com/aiarmada/filament-cart) - Filament admin panel for cart

## 💬 Support

- [Documentation](https://github.com/aiarmada/cart/tree/main/docs)
- [Issues](https://github.com/aiarmada/cart/issues)
- [Discussions](https://github.com/aiarmada/cart/discussions)
