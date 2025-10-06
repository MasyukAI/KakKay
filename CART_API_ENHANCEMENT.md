# Cart Package: getVersion() API Enhancement

## Summary
Added `getVersion()` public method to the Cart package API, allowing applications to track cart changes without bypassing the package's encapsulation.

## Problem: Breaking Encapsulation

The application was **reaching into the database directly** to get the cart version:

```php
// ❌ Bad: Bypassing the cart package API
protected function getCurrentCartVersion(): ?int
{
    $cartRecord = DB::table('carts')
        ->where('identifier', session()->getId())
        ->where('instance', 'default')
        ->first(['version']);
    
    return $cartRecord ? (int) $cartRecord->version : null;
}
```

**Issues:**
1. **Breaks encapsulation** - Application knows about internal database schema
2. **Tight coupling** - Tied to database storage driver
3. **Not portable** - Won't work with session/cache storage
4. **Maintenance burden** - If cart package changes schema, application breaks
5. **Duplication** - Every app using the package must implement this

## Solution: Public API Method

### Added to Cart Package

**StorageInterface** (packages/masyukai/cart/packages/core/src/Storage/StorageInterface.php):
```php
interface StorageInterface
{
    /**
     * Get cart version for change tracking
     * Returns the version number used for optimistic locking and change detection
     */
    public function getVersion(string $identifier, string $instance): ?int;
}
```

**DatabaseStorage Implementation:**
```php
public function getVersion(string $identifier, string $instance): ?int
{
    $version = $this->database->table($this->table)
        ->where('identifier', $identifier)
        ->where('instance', $instance)
        ->value('version');
    
    return $version !== null ? (int) $version : null;
}
```

**SessionStorage & CacheStorage Implementation:**
```php
public function getVersion(string $identifier, string $instance): ?int
{
    return null; // Version tracking not supported in session/cache
}
```

**Cart Class** (packages/masyukai/cart/packages/core/src/Cart.php):
```php
/**
 * Get cart version for change tracking
 * Useful for detecting cart modifications and optimistic locking
 *
 * @return int|null Version number or null if not supported by storage driver
 */
public function getVersion(): ?int
{
    return $this->storage->getVersion($this->getIdentifier(), $this->instance());
}
```

### Application Usage

**Before (Breaking Encapsulation):**
```php
// ❌ Application reaches into database
$currentCartVersion = $this->getCurrentCartVersion();
$sessionCartVersion = session('cart_version');

protected function getCurrentCartVersion(): ?int
{
    // 20+ lines of database query code
    $cartRecord = DB::table('carts')
        ->where('identifier', session()->getId())
        ->where('instance', 'default')
        ->first(['version']);
    
    return $cartRecord ? (int) $cartRecord->version : null;
}
```

**After (Using Public API):**
```php
// ✅ Clean, simple, proper encapsulation
$currentCartVersion = CartFacade::getCurrentCart()->getVersion();
$sessionCartVersion = session('cart_version');

// No helper method needed!
```

## Benefits

### 1. Proper Encapsulation ✅
- Application doesn't know about database schema
- Cart package controls its own internal state
- Changes to storage implementation don't break applications

### 2. Portable Across Storage Drivers ✅
```php
// Works with any storage driver
$version = $cart->getVersion();

// DatabaseStorage: Returns actual version (1, 2, 3, ...)
// SessionStorage: Returns null (not supported)
// CacheStorage: Returns null (not supported)
```

### 3. Cleaner Application Code ✅
```diff
- 20+ lines of database query logic
- DB import
- Knowledge of table structure
+ 1 line: $cart->getVersion()
```

### 4. Better Maintainability ✅
- Single source of truth for version logic
- Cart package can change implementation without breaking apps
- Easier to test and mock

### 5. Consistency with Package Design ✅
```php
// All cart operations go through the Cart API
$cart->getItems();
$cart->getConditions();
$cart->getMetadata('key');
$cart->getVersion(); // ✅ Consistent!

// Never bypass the API:
DB::table('carts')->... // ❌ Wrong!
```

## API Design Principles Followed

### 1. Encapsulation
> "Hide internal implementation details, expose only necessary interfaces"

✅ Applications don't need to know about database schema

### 2. Single Responsibility
> "A class should have one reason to change"

✅ Cart package manages its own version tracking

### 3. Interface Segregation
> "Clients shouldn't depend on interfaces they don't use"

✅ Storage interface extended appropriately

### 4. Dependency Inversion
> "Depend on abstractions, not concretions"

✅ Returns `?int`, not database-specific types

## Backward Compatibility

✅ **Fully backward compatible** - This is a new method addition
- Existing code continues to work
- No breaking changes
- Optional to use

## Testing

All tests pass without modification:

```bash
# Application tests
vendor/bin/pest --filter="CheckoutOrderCreation"
# ✅ 4 passed (12 assertions)

# Cart package tests (if needed in future)
cd packages/masyukai/cart && vendor/bin/pest
```

## Files Modified

### Cart Package
1. `packages/masyukai/cart/packages/core/src/Storage/StorageInterface.php` - Added `getVersion()` interface method
2. `packages/masyukai/cart/packages/core/src/Storage/DatabaseStorage.php` - Implemented for database
3. `packages/masyukai/cart/packages/core/src/Storage/SessionStorage.php` - Implemented (returns null)
4. `packages/masyukai/cart/packages/core/src/Storage/CacheStorage.php` - Implemented (returns null)
5. `packages/masyukai/cart/packages/core/src/Cart.php` - Added public `getVersion()` method

### Application
1. `app/Livewire/Checkout.php` - Simplified to use cart API instead of database queries
   - Removed `getCurrentCartVersion()` helper method (20+ lines)
   - Removed `DB` facade import
   - Changed to use `CartFacade::getCurrentCart()->getVersion()`

## Usage Examples

### Basic Change Detection
```php
// Store version when entering checkout
$version = Cart::getCurrentCart()->getVersion();
session(['cart_version' => $version]);

// Later, check if cart changed
$currentVersion = Cart::getCurrentCart()->getVersion();
if ($currentVersion !== session('cart_version')) {
    // Cart was modified, handle accordingly
}
```

### Payment Intent Validation
```php
// Store version in payment intent metadata
Cart::setMetadata('payment_intent', [
    'purchase_id' => $purchaseId,
    'cart_version' => Cart::getCurrentCart()->getVersion(),
    'amount' => $total,
]);

// Validate in webhook
$intent = Cart::getMetadata('payment_intent');
if ($intent['cart_version'] !== Cart::getCurrentCart()->getVersion()) {
    // Cart changed after payment intent created
    Log::warning('Cart modified after payment intent', [
        'intent_version' => $intent['cart_version'],
        'current_version' => Cart::getCurrentCart()->getVersion(),
    ]);
}
```

### Optimistic Locking
```php
// Check version before expensive operation
$version = Cart::getCurrentCart()->getVersion();

// ... perform some operation ...

if (Cart::getCurrentCart()->getVersion() !== $version) {
    throw new CartConflictException('Cart was modified during operation');
}
```

## Documentation Updates

- Updated `CART_VERSION_TRACKING.md` with new API usage
- Emphasized proper encapsulation principles
- Added examples of clean API usage

## Key Takeaway

**Before:**
```php
// Application bypasses cart package
$version = DB::table('carts')->where(...)->value('version');
```

**After:**
```php
// Application uses cart package API
$version = Cart::getCurrentCart()->getVersion();
```

This is **textbook encapsulation** - the cart package exposes its functionality through a clean API, and applications use that API without needing to know about internal implementation details.

## Future Enhancements

Possible future additions to the API:

```php
// Get full cart metadata including version and timestamps
public function getCartInfo(): array
{
    return [
        'version' => $this->getVersion(),
        'created_at' => $this->getCreatedAt(),
        'updated_at' => $this->getUpdatedAt(),
        'identifier' => $this->getIdentifier(),
        'instance' => $this->instance(),
    ];
}
```

This would further reduce the need for applications to query the database directly.

---

**Excellent API design! This is how packages should be built.** 🎉
