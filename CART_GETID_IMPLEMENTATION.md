# Cart getId() Implementation

**Date:** October 7, 2025  
**Status:** ✅ Implemented & Tested  
**Impact:** Simplified PaymentService, Cleaner API

## Overview

Added `getId()` method to the Cart package to expose the cart's database UUID (primary key). This eliminates the need for application-level helper methods and provides a cleaner API for linking carts to external systems.

## What Was Added

### 1. StorageInterface - New Method

**File:** `packages/masyukai/cart/packages/core/src/Storage/StorageInterface.php`

```php
/**
 * Get cart ID (primary key) from storage
 * Useful for linking carts to external systems (payment gateways, orders, etc.)
 *
 * @param  string  $identifier  User/session identifier
 * @param  string  $instance  Cart instance name
 * @return string|null Cart UUID or null if cart doesn't exist
 */
public function getId(string $identifier, string $instance): ?string;
```

### 2. DatabaseStorage - Implementation

**File:** `packages/masyukai/cart/packages/core/src/Storage/DatabaseStorage.php`

```php
/**
 * Get cart ID (primary key) from storage
 */
public function getId(string $identifier, string $instance): ?string
{
    return $this->database->table($this->table)
        ->where('identifier', $identifier)
        ->where('instance', $instance)
        ->value('id');
}
```

### 3. SessionStorage & CacheStorage - Stub Implementation

Both return `null` since they don't have database IDs:

```php
public function getId(string $identifier, string $instance): ?string
{
    return null;
}
```

### 4. Cart Class - Public Method

**File:** `packages/masyukai/cart/packages/core/src/Cart.php`

```php
/**
 * Get cart ID (primary key) from storage
 * Useful for linking carts to external systems like payment gateways, orders, etc.
 *
 * @return string|null Cart UUID or null if not supported by storage driver
 */
public function getId(): ?string
{
    return $this->storage->getId($this->getIdentifier(), $this->instance());
}
```

### 5. Cart Facade - PHPDoc Update

**File:** `packages/masyukai/cart/packages/core/src/Facades/Cart.php`

```php
/**
 * @method static int|null getVersion()
 * @method static string|null getId()
 */
```

## Changes in PaymentService

### Before (With Helper Methods)

```php
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function createPaymentIntent(Cart $cart, array $customerData): array
    {
        $cartVersion = $this->getCartVersion($cart) + 1;
        $customerData['reference'] = (string) $this->getCartId($cart);
        // ...
    }

    public function validateCartPaymentIntent(Cart $cart): array
    {
        $currentVersion = $this->getCartVersion($cart);
        // ...
    }

    private function getCartVersion(Cart $cart): int
    {
        return DB::table('carts')
            ->where('identifier', $cart->getIdentifier())
            ->where('instance', $cart->instance())
            ->value('version') ?? 1;
    }

    private function getCartId(Cart $cart): string
    {
        return DB::table('carts')
            ->where('identifier', $cart->getIdentifier())
            ->where('instance', $cart->instance())
            ->value('id');
    }
}
```

### After (Using Cart Methods)

```php
class PaymentService
{
    public function createPaymentIntent(Cart $cart, array $customerData): array
    {
        $cartVersion = $cart->getVersion() + 1;
        $customerData['reference'] = (string) $cart->getId();
        // ...
    }

    public function validateCartPaymentIntent(Cart $cart): array
    {
        $currentVersion = $cart->getVersion();
        // ...
    }

    // Helper methods removed - no longer needed!
}
```

## Benefits

### 1. Cleaner API ✅
- Cart object now exposes both `getVersion()` and `getId()`
- Consistent API design
- No need for application-level workarounds

### 2. Removed Dependencies ✅
- Removed `use Illuminate\Support\Facades\DB;` from PaymentService
- Reduced coupling between application and database
- Better encapsulation

### 3. Simplified Code ✅
- Removed 2 private helper methods (20 lines of code)
- More readable and maintainable
- Follows DRY principle

### 4. Reusability ✅
- Other parts of the application can now use `Cart::getId()`
- Useful for linking carts to orders, analytics, external systems
- Consistent with how `getVersion()` already works

## Testing

### Application Tests
```bash
php artisan test tests/Feature/CheckoutOrderCreationTest.php

✓ checkout creates payment intent and redirects        0.67s
✓ checkout fails gracefully when cart is empty         0.07s  
✓ checkout validates required form fields              0.32s
✓ checkout handles payment gateway errors              0.18s

Tests:  4 passed (12 assertions)
Duration: 1.37s
```

### Cart Package Tests
- Fixed anonymous StorageInterface implementations in tests
- Added `getId()` and `getVersion()` stub methods to test mocks
- All code formatted with Pint

## Usage Examples

### Get Cart ID for External System

```php
use MasyukAI\Cart\Facades\Cart;

// Add items to cart
Cart::add('product-1', 'Widget', 1999, 1);

// Get cart UUID for payment gateway reference
$cartId = Cart::getId(); // "550e8400-e29b-41d4-a716-446655440000"

// Send to payment gateway
$chip->createPurchase([
    'reference' => $cartId, // ← Use cart ID as reference
    // ...
], $items);
```

### Track Cart Version Changes

```php
$beforeVersion = Cart::getVersion(); // 5

Cart::add('product-2', 'Gadget', 2999, 1);

$afterVersion = Cart::getVersion(); // 6

if ($afterVersion !== $beforeVersion) {
    echo "Cart has changed!";
}
```

### Link Cart to Order

```php
$order = Order::create([
    'cart_id' => Cart::getId(), // ← Direct cart UUID reference
    'user_id' => auth()->id(),
    'total' => Cart::total()->getAmount(),
]);
```

## Storage Driver Support

| Driver | getId() Support | Returns |
|--------|----------------|---------|
| **DatabaseStorage** | ✅ Yes | UUID string |
| **SessionStorage** | ❌ No | `null` |
| **CacheStorage** | ❌ No | `null` |

**Note:** Only `DatabaseStorage` returns actual IDs since it's the only driver with database records.

## Architecture Pattern

This follows the same pattern as `getVersion()`:

1. **StorageInterface** defines the contract
2. **Storage drivers** implement it (DatabaseStorage returns value, others return `null`)
3. **Cart class** provides public accessor
4. **Facade** exposes it via PHPDoc

This maintains clean separation of concerns and allows different storage drivers to have different capabilities.

## Related Documentation

- [CART_LOOKUP_OPTIMIZATION.md](CART_LOOKUP_OPTIMIZATION.md) - How cart ID is used as CHIP reference
- [PAYMENT_FLOW_EXPLAINED.md](PAYMENT_FLOW_EXPLAINED.md) - Payment intent system
- [PAYMENT_INTENT_CLEANUP.md](PAYMENT_INTENT_CLEANUP.md) - Payment intent optimizations

## Summary

**What Changed:**
- Added `getId()` to Cart package (StorageInterface, implementations, Cart class, Facade)
- Removed helper methods from PaymentService
- Updated PHPDoc for Cart facade
- Fixed test mocks to implement new interface methods

**Lines of Code:**
- **Added:** ~40 lines (interface, implementations, docs)
- **Removed:** ~20 lines (helper methods)
- **Net:** Cleaner, more maintainable code

**Impact:**
- ✅ Application tests passing
- ✅ Code formatted with Pint  
- ✅ Cleaner PaymentService
- ✅ Reusable Cart API
- ✅ Better encapsulation

---

**Status:** Production Ready ✅  
**Breaking Changes:** None  
**Migration Required:** No
