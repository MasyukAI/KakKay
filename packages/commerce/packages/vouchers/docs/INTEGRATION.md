# Cart-Vouchers Integration Guide

This document explains how the voucher package integrates with the MasyukAI Cart package.

## Architecture Overview

The voucher package uses a **hybrid architecture** that combines:

1. **Independent package management** - Vouchers are managed separately with their own models, services, and business logic
2. **Cart condition integration** - Vouchers apply to carts through the cart's condition system

This approach provides:
- ✅ Clean separation of concerns
- ✅ Reusable voucher management across applications
- ✅ Seamless integration with cart pricing
- ✅ Independent package publishing

## How It Works

### 1. VoucherCondition Bridge

The `VoucherCondition` class extends `CartCondition` to bridge voucher data with the cart's pricing engine:

```php
use MasyukAI\Cart\Vouchers\Conditions\VoucherCondition;
use MasyukAI\Cart\Vouchers\Data\VoucherData;

$voucherData = Voucher::find('SUMMER20');
$condition = new VoucherCondition($voucherData);

// The condition automatically:
// - Formats the voucher value for cart calculation
// - Validates the voucher on each cart calculation
// - Applies max discount caps
// - Handles free shipping logic
```

### 2. HasVouchers Trait

The `HasVouchers` trait adds voucher management methods to the Cart class:

```php
// Apply a voucher
Cart::applyVoucher('SUMMER20');

// Remove a voucher
Cart::removeVoucher('SUMMER20');

// Check if cart has voucher
if (Cart::hasVoucher('SUMMER20')) {
    // ...
}

// Get all applied vouchers
$vouchers = Cart::getAppliedVouchers();

// Get voucher discount amount
$discount = Cart::getVoucherDiscount();

// Validate all vouchers (useful after cart modifications)
$removedCodes = Cart::validateAppliedVouchers();
```

### 3. Event System

When vouchers are applied or removed, events are dispatched:

```php
use MasyukAI\Cart\Vouchers\Events\VoucherApplied;
use MasyukAI\Cart\Vouchers\Events\VoucherRemoved;

// Listen for voucher events
Event::listen(VoucherApplied::class, function (VoucherApplied $event) {
    $cart = $event->cart;
    $voucher = $event->voucher;
    
    // Track voucher usage, send analytics, etc.
    Voucher::recordUsage($voucher->code, $cart);
});
```

## Integration Points

### Cart Class Integration

The `Cart` class uses the `HasVouchers` trait:

```php
// packages/core/src/Cart.php
namespace MasyukAI\Cart;

use MasyukAI\Cart\Vouchers\Traits\HasVouchers;

final class Cart
{
    use CalculatesTotals;
    use ManagesConditions;
    // ... other traits
    use HasVouchers; // ← Adds voucher methods
}
```

This makes voucher methods available on any Cart instance:

```php
$cart = Cart::instance('shopping');
$cart->applyVoucher('WELCOME10');
```

### Validation Flow

When a voucher is applied, the following validation occurs:

1. **Initial validation** - `VoucherValidator` checks voucher status, dates, limits, and cart requirements
2. **Dynamic validation** - `VoucherCondition` validates on each cart calculation using the condition's rules
3. **Re-validation** - You can manually re-validate all vouchers after cart modifications

```php
// Initial validation happens automatically
Cart::applyVoucher('SUMMER20'); // Throws InvalidVoucherException if invalid

// Dynamic validation happens on each cart calculation
$total = Cart::total(); // Voucher is re-validated here

// Manual re-validation after cart changes
Cart::add($product); // Added a product
$removed = Cart::validateAppliedVouchers(); // Check if vouchers still valid

if (count($removed) > 0) {
    // Notify user that vouchers were removed
    session()->flash('warning', 'Some vouchers were removed due to cart changes');
}
```

### Condition Order

Vouchers are applied in the cart's condition processing order:

```php
// Default order: 50 (configurable in vouchers.php)
Cart::applyVoucher('SUMMER20'); // Applied with order 50

// Custom order
Cart::applyVoucher('PRIORITY10', order: 10); // Applied before other conditions
```

**Standard condition order:**
- 0-49: Pre-subtotal conditions (item-level discounts, taxes)
- 50-99: Vouchers (default: 50)
- 100+: Post-subtotal conditions (shipping, handling fees)

## Usage Examples

### Basic Usage

```php
use MasyukAI\Cart\Facades\Cart;
use MasyukAI\Cart\Vouchers\Facades\Voucher;

// Create a voucher
Voucher::create([
    'code' => 'WELCOME10',
    'type' => VoucherType::Percentage,
    'value' => 10,
    'description' => '10% off for new customers',
    'starts_at' => now(),
    'expires_at' => now()->addMonth(),
]);

// Add items to cart
Cart::add($product, quantity: 2);

// Apply voucher
Cart::applyVoucher('WELCOME10');

// Get totals
$subtotal = Cart::subtotal(); // 100.00
$discount = Cart::getVoucherDiscount(); // 10.00
$total = Cart::total(); // 90.00

// Check applied vouchers
$codes = Cart::getAppliedVoucherCodes(); // ['WELCOME10']
```

### Multiple Vouchers (Stacking)

```php
// Enable stacking in config/vouchers.php
'cart' => [
    'max_vouchers_per_cart' => 2,
    'allow_stacking' => true,
],

// Apply multiple vouchers
Cart::applyVoucher('SAVE10');
Cart::applyVoucher('EXTRA5');

// Stacking calculation:
// Subtotal: $100
// SAVE10 (10%): $100 - $10 = $90
// EXTRA5 (5%): $90 - $4.50 = $85.50
// Total: $85.50
```

### Free Shipping Vouchers

```php
Voucher::create([
    'code' => 'FREESHIP',
    'type' => VoucherType::FreeShipping,
    'value' => 0,
    'description' => 'Free shipping',
]);

Cart::applyVoucher('FREESHIP');

// Check if free shipping
$voucher = Cart::getVoucherCondition('FREESHIP');
if ($voucher && $voucher->isFreeShipping()) {
    // Apply free shipping logic
}
```

### Minimum Cart Value

```php
Voucher::create([
    'code' => 'BIG50',
    'type' => VoucherType::Fixed,
    'value' => 50,
    'min_cart_value' => 200,
]);

Cart::add($product, quantity: 1); // $150 subtotal
Cart::applyVoucher('BIG50'); // Throws InvalidVoucherException

Cart::add($product, quantity: 2); // $300 subtotal
Cart::applyVoucher('BIG50'); // Success!
```

### Maximum Discount Cap

```php
Voucher::create([
    'code' => 'MEGA50',
    'type' => VoucherType::Percentage,
    'value' => 50, // 50% off
    'max_discount_amount' => 100, // But max $100 discount
]);

Cart::add($expensiveProduct); // $500 subtotal
Cart::applyVoucher('MEGA50');

// Without cap: $500 * 50% = $250 discount → $250 total
// With cap: $500 - $100 = $400 total
$total = Cart::total(); // 400.00
```

### Usage Limits

```php
Voucher::create([
    'code' => 'LIMITED',
    'max_uses' => 100, // Total uses across all users
    'max_uses_per_user' => 1, // One use per user
]);

// First use by user
Cart::applyVoucher('LIMITED'); // Success
Voucher::recordUsage('LIMITED', Cart::instance());

// Second use by same user
Cart::applyVoucher('LIMITED'); // Throws InvalidVoucherException
```

## Configuration

Configure voucher behavior in `config/vouchers.php`:

```php
return [
    'cart' => [
        // Maximum vouchers per cart (0 = disabled, -1 = unlimited)
        'max_vouchers_per_cart' => 1,
        
        // Allow multiple vouchers to stack
        'allow_stacking' => false,
        
        // Default condition order for vouchers
        'condition_order' => 50,
    ],
    
    'validation' => [
        'check_user_limit' => true,
        'check_global_limit' => true,
        'check_date_range' => true,
        'check_min_cart_value' => true,
    ],
    
    'events' => [
        'dispatch' => true, // Dispatch VoucherApplied/VoucherRemoved events
    ],
];
```

## Testing

Example test for voucher integration:

```php
use MasyukAI\Cart\Facades\Cart;
use MasyukAI\Cart\Vouchers\Facades\Voucher;
use MasyukAI\Cart\Vouchers\Enums\VoucherType;

test('can apply percentage voucher to cart', function () {
    // Create voucher
    $voucher = Voucher::create([
        'code' => 'TEST10',
        'type' => VoucherType::Percentage,
        'value' => 10,
        'starts_at' => now(),
        'expires_at' => now()->addMonth(),
    ]);
    
    // Add item to cart
    $product = Product::factory()->create(['price' => 100]);
    Cart::add($product, quantity: 1);
    
    expect(Cart::subtotal())->toBe(100.0);
    
    // Apply voucher
    Cart::applyVoucher('TEST10');
    
    expect(Cart::hasVoucher('TEST10'))->toBeTrue();
    expect(Cart::getVoucherDiscount())->toBe(10.0);
    expect(Cart::total())->toBe(90.0);
});

test('voucher is validated on cart changes', function () {
    $voucher = Voucher::create([
        'code' => 'MIN100',
        'type' => VoucherType::Fixed,
        'value' => 20,
        'min_cart_value' => 100,
    ]);
    
    // Add item meeting minimum
    $product = Product::factory()->create(['price' => 100]);
    Cart::add($product, quantity: 1);
    Cart::applyVoucher('MIN100');
    
    expect(Cart::hasVoucher('MIN100'))->toBeTrue();
    
    // Remove item, cart now below minimum
    Cart::remove($product->id);
    $removed = Cart::validateAppliedVouchers();
    
    expect($removed)->toContain('MIN100');
    expect(Cart::hasVoucher('MIN100'))->toBeFalse();
});
```

## Best Practices

### 1. Always Validate After Cart Changes

```php
// After adding/removing items
Cart::remove($itemId);
$removed = Cart::validateAppliedVouchers();

if (count($removed) > 0) {
    session()->flash('warning', 'Some vouchers were removed');
}
```

### 2. Handle Exceptions Gracefully

```php
try {
    Cart::applyVoucher($request->input('code'));
    return back()->with('success', 'Voucher applied!');
} catch (InvalidVoucherException $e) {
    return back()->withErrors(['code' => $e->getMessage()]);
}
```

### 3. Record Usage After Order Completion

```php
Event::listen(OrderPaid::class, function (OrderPaid $event) {
    $cart = Cart::instance($event->order->cart_id);
    
    foreach ($cart->getAppliedVouchers() as $voucherCondition) {
        Voucher::recordUsage(
            code: $voucherCondition->getVoucherCode(),
            cart: $cart,
            userId: $event->order->user_id
        );
    }
});
```

### 4. Clear Vouchers on Checkout

```php
// After successful payment
Cart::clearVouchers();
Cart::clear();
```

## API Reference

See the main package README for full API documentation on all voucher methods.

## Troubleshooting

### Voucher not applying discount

1. Check voucher is active: `$voucher->isActive()`
2. Check date range: `$voucher->hasStarted() && !$voucher->isExpired()`
3. Check usage limits: `$voucher->hasUsageLimitRemaining()`
4. Check minimum cart value: `Cart::subtotal() >= $voucher->min_cart_value`

### Voucher removed after cart change

This is expected behavior. Vouchers are re-validated on each cart calculation. If the cart no longer meets requirements (e.g., falls below minimum value), the voucher is automatically removed.

Call `validateAppliedVouchers()` after cart changes to detect and notify users.

### Multiple vouchers not stacking

Enable stacking in configuration:

```php
// config/vouchers.php
'cart' => [
    'max_vouchers_per_cart' => 2, // Or more
    'allow_stacking' => true,
],
```

## Advanced Topics

### Custom Validation Rules

Create custom validators by extending `VoucherValidator`:

```php
namespace App\Services;

use MasyukAI\Cart\Vouchers\Services\VoucherValidator as BaseValidator;

class CustomVoucherValidator extends BaseValidator
{
    protected function validateCustomRule(VoucherData $voucher, Cart $cart): bool
    {
        // Your custom validation logic
        return true;
    }
}

// Register in service provider
$this->app->singleton(VoucherValidator::class, CustomVoucherValidator::class);
```

### Voucher-Specific Events

Listen to voucher events for analytics, notifications, etc.:

```php
// app/Providers/EventServiceProvider.php
protected $listen = [
    VoucherApplied::class => [
        SendVoucherAppliedNotification::class,
        TrackVoucherUsageAnalytics::class,
    ],
    VoucherRemoved::class => [
        LogVoucherRemoval::class,
    ],
];
```

---

This integration provides a powerful, flexible voucher system that seamlessly works with the cart's pricing engine while maintaining clean separation of concerns.
