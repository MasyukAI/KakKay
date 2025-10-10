# Static vs Dynamic Vouchers & Multiple Extensions

## Question 1: Static vs Dynamic Vouchers

### ✅ Answer: YES, VoucherCondition can be used as BOTH!

The `VoucherCondition` constructor now supports a `$dynamic` parameter:

```php
public function __construct(
    VoucherData $voucher, 
    int $order = 0, 
    bool $dynamic = true  // ← NEW: Control static/dynamic behavior
)
```

### Usage: Static Voucher (No Rules)

**When to use:** Voucher has been pre-validated and you just want to apply the discount without re-validation.

```php
use MasyukAI\Cart\Facades\Cart;
use MasyukAI\Cart\Vouchers\Facades\Voucher;
use MasyukAI\Cart\Vouchers\Conditions\VoucherCondition;

// Pre-validate once
$voucherData = Voucher::find('SIMPLE5');
$validationResult = Voucher::validate('SIMPLE5', Cart::instance());

if ($validationResult->isValid) {
    // Create static condition (no rules, no re-validation)
    $condition = new VoucherCondition(
        voucher: $voucherData,
        order: 50,
        dynamic: false  // ← Static mode
    );
    
    // Add as regular condition
    Cart::addCondition($condition);
}
```

**Characteristics:**
- ✅ Faster (no re-validation on each calculation)
- ✅ Simpler (just apply discount)
- ✅ Good for vouchers without complex rules
- ❌ No automatic removal if cart changes
- ❌ Need manual validation before applying

### Usage: Dynamic Voucher (With Rules)

**When to use:** Voucher has rules that need continuous validation (min cart value, usage limits, etc.)

```php
// Create dynamic condition (with validation rules)
$condition = new VoucherCondition(
    voucher: $voucherData,
    order: 50,
    dynamic: true  // ← Dynamic mode (default)
);

// Register as dynamic condition
Cart::registerDynamicCondition($condition);

// Cart automatically re-validates on each calculation
Cart::evaluateDynamicConditions();
```

**Characteristics:**
- ✅ Automatic re-validation on cart changes
- ✅ Auto-removal if voucher becomes invalid
- ✅ Best for vouchers with rules (min cart value, etc.)
- ❌ Slightly slower (validates on each calculation)

### Decision Tree

```
Does your voucher have requirements that can change?
│
├─ YES (min cart value, usage limits, date range)
│   └─ Use Dynamic: new VoucherCondition($voucher, $order, dynamic: true)
│
└─ NO (simple "$5 off" with no conditions)
    └─ Use Static: new VoucherCondition($voucher, $order, dynamic: false)
```

### Real-World Examples

#### Example 1: Simple Static Voucher

```php
// "$5 off" voucher - no special rules
Voucher::create([
    'code' => 'SIMPLE5',
    'type' => VoucherType::Fixed,
    'value' => 5,
    // No min_cart_value, no usage limits - just $5 off
]);

// Apply as static (faster, no re-validation needed)
$voucherData = Voucher::find('SIMPLE5');
$condition = new VoucherCondition($voucherData, order: 50, dynamic: false);
Cart::addCondition($condition);
```

#### Example 2: Dynamic Voucher with Min Cart Value

```php
// "10% off orders over $100"
Voucher::create([
    'code' => 'MIN100',
    'type' => VoucherType::Percentage,
    'value' => 10,
    'min_cart_value' => 100,  // ← Has rule!
]);

// Must use dynamic (needs re-validation)
$voucherData = Voucher::find('MIN100');
$condition = new VoucherCondition($voucherData, order: 50, dynamic: true);
Cart::registerDynamicCondition($condition);

// If cart drops below $100, voucher is auto-removed!
```

#### Example 3: Dynamic Voucher with Usage Limits

```php
// Limited use voucher
Voucher::create([
    'code' => 'LIMITED100',
    'type' => VoucherType::Fixed,
    'value' => 20,
    'max_uses' => 100,
    'max_uses_per_user' => 1,  // ← Has rule!
]);

// Must use dynamic (validates usage limits)
$voucherData = Voucher::find('LIMITED100');
$condition = new VoucherCondition($voucherData, order: 50, dynamic: true);
Cart::registerDynamicCondition($condition);
```

### Recommendation

**Default to Dynamic** unless you have a specific reason to use static:

```php
// Default (dynamic) - safest approach
$condition = new VoucherCondition($voucherData);

// Explicit static - only if you're sure
$condition = new VoucherCondition($voucherData, dynamic: false);
```

---

## Question 2: Automatic CartWithVouchers Binding & Multiple Extensions

### 🎯 The Challenge

When you have multiple cart extensions:
- `masyukai/cart-vouchers`
- `masyukai/cart-loyalty-points`
- `masyukai/cart-gift-cards`
- `masyukai/cart-subscriptions`

Each wants to extend Cart, but only one binding can exist!

### ❌ The Naive Approach (Doesn't Work)

```php
// In each package's service provider:
$this->app->bind(Cart::class, CartWithVouchers::class);  // Vouchers package
$this->app->bind(Cart::class, CartWithLoyalty::class);   // Loyalty package
$this->app->bind(Cart::class, CartWithGiftCards::class); // Gift cards package

// ❌ Problem: Last binding wins, others are lost!
```

### ✅ Solution 1: Manual Composition (Recommended)

**Applications create their own composed Cart class:**

```php
// app/Support/Cart/ExtendedCart.php
namespace App\Support\Cart;

use MasyukAI\Cart\Cart;
use MasyukAI\Cart\Vouchers\Traits\HasVouchers;
use MasyukAI\Cart\Loyalty\Traits\HasLoyaltyPoints;
use MasyukAI\Cart\GiftCards\Traits\HasGiftCards;

class ExtendedCart extends Cart
{
    use HasVouchers;
    use HasLoyaltyPoints;
    use HasGiftCards;
    // Add as many extensions as needed!
}
```

Bind in AppServiceProvider:

```php
// app/Providers/AppServiceProvider.php
use App\Support\Cart\ExtendedCart;
use MasyukAI\Cart\Cart;

public function register(): void
{
    $this->app->bind(Cart::class, ExtendedCart::class);
}
```

**Benefits:**
- ✅ Application controls what gets included
- ✅ Can use multiple extensions together
- ✅ Clear, explicit, no magic
- ✅ Easy to debug

**Setup Steps:**
1. Install packages: `composer require masyukai/cart-vouchers masyukai/cart-loyalty`
2. Create ExtendedCart class with traits
3. Bind in AppServiceProvider
4. Use: `Cart::applyVoucher()`, `Cart::addLoyaltyPoints()`, etc.

### ✅ Solution 2: Trait Detection (Advanced)

**For packages that want to be "smart" about detection:**

```php
// In VoucherServiceProvider
public function register(): void
{
    // Only bind if no other extension has bound yet
    if (!$this->app->bound(Cart::class)) {
        // Check if this is the only cart extension
        $this->bindCartIfSolo();
    }
}

protected function bindCartIfSolo(): void
{
    // Check if other cart extension packages are installed
    $otherExtensions = [
        'MasyukAI\Cart\Loyalty\LoyaltyServiceProvider',
        'MasyukAI\Cart\GiftCards\GiftCardServiceProvider',
    ];
    
    $hasOtherExtensions = collect($otherExtensions)
        ->filter(fn($provider) => class_exists($provider))
        ->isNotEmpty();
    
    if ($hasOtherExtensions) {
        // Multiple extensions detected - let application handle binding
        $this->emitCompositionWarning();
    } else {
        // Only vouchers extension - safe to bind
        $this->app->bind(Cart::class, CartWithVouchers::class);
    }
}

protected function emitCompositionWarning(): void
{
    $this->app->booted(function () {
        if (config('app.debug')) {
            logger()->info(
                'Multiple cart extensions detected. ' .
                'Please create a composed ExtendedCart class in your application. ' .
                'See: https://docs.example.com/cart-extensions'
            );
        }
    });
}
```

**Benefits:**
- ✅ Automatic binding when alone
- ✅ Smart detection of conflicts
- ✅ Helpful warnings for developers
- ❌ More complex
- ❌ Still requires manual composition for multiple extensions

### ✅ Solution 3: Configuration-Based Binding

**Let applications configure which extensions to use:**

```php
// config/cart.php
return [
    'extensions' => [
        'vouchers' => true,
        'loyalty' => true,
        'gift_cards' => false,
        'subscriptions' => true,
    ],
];
```

In a dedicated CartExtensionServiceProvider:

```php
// app/Providers/CartExtensionServiceProvider.php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use MasyukAI\Cart\Cart;

class CartExtensionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(Cart::class, function ($app) {
            $cartClass = $this->buildExtendedCartClass();
            return $app->make($cartClass);
        });
    }
    
    protected function buildExtendedCartClass(): string
    {
        $extensions = config('cart.extensions', []);
        
        // Build anonymous class with enabled traits
        return new class extends Cart {
            // Dynamically use traits based on config
        };
    }
}
```

**Benefits:**
- ✅ Configuration-driven
- ✅ Easy to enable/disable extensions
- ❌ Complex implementation
- ❌ Runtime overhead

### 🎯 Recommended Approach: Manual Composition

**Why it's best:**

1. **Explicit is better than implicit** - developers know exactly what's happening
2. **No conflicts** - application controls everything
3. **Easy to debug** - just one class to look at
4. **Framework standard** - follows Laravel conventions
5. **IDE friendly** - autocomplete works perfectly

**Setup Guide:**

```bash
# 1. Install extensions
composer require masyukai/cart-vouchers
composer require masyukai/cart-loyalty
composer require masyukai/cart-gift-cards

# 2. Create composed cart class
php artisan make:class Support/Cart/ExtendedCart
```

```php
// app/Support/Cart/ExtendedCart.php
<?php

namespace App\Support\Cart;

use MasyukAI\Cart\Cart;
use MasyukAI\Cart\Vouchers\Traits\HasVouchers;
use MasyukAI\Cart\Loyalty\Traits\HasLoyaltyPoints;
use MasyukAI\Cart\GiftCards\Traits\HasGiftCards;

/**
 * Extended Cart with all enabled extensions.
 * 
 * To add/remove extensions:
 * 1. Install/uninstall the package
 * 2. Add/remove the trait
 * 3. Clear cache: php artisan optimize:clear
 */
class ExtendedCart extends Cart
{
    use HasVouchers;
    use HasLoyaltyPoints;
    use HasGiftCards;
}
```

```php
// app/Providers/AppServiceProvider.php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Support\Cart\ExtendedCart;
use MasyukAI\Cart\Cart;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind extended cart
        $this->app->bind(Cart::class, ExtendedCart::class);
    }
}
```

```php
// Now use anywhere:
use MasyukAI\Cart\Facades\Cart;

Cart::applyVoucher('SUMMER20');
Cart::addLoyaltyPoints(100);
Cart::applyGiftCard('GIFT-123');
```

### Package-Provided CartWithVouchers

The vouchers package now provides `CartWithVouchers` as a **convenience class**, but does **NOT** auto-bind it:

```php
// Available in package:
use MasyukAI\Cart\Vouchers\Support\CartWithVouchers;

// Applications can use it:
$this->app->bind(Cart::class, CartWithVouchers::class);

// Or extend it:
class ExtendedCart extends CartWithVouchers
{
    use HasLoyaltyPoints;
}
```

### Documentation for Other Extension Packages

If you create other cart extensions, follow this pattern:

```php
// packages/masyukai/cart-loyalty/src/Support/CartWithLoyalty.php
namespace MasyukAI\Cart\Loyalty\Support;

use MasyukAI\Cart\Cart;
use MasyukAI\Cart\Loyalty\Traits\HasLoyaltyPoints;

/**
 * Provided for convenience - NOT automatically bound.
 * See docs for manual binding or composition with other extensions.
 */
class CartWithLoyalty extends Cart
{
    use HasLoyaltyPoints;
}
```

---

## Summary

### Question 1: Static vs Dynamic

✅ **Both are supported!**

```php
// Static (no re-validation)
new VoucherCondition($voucher, order: 50, dynamic: false);

// Dynamic (auto re-validation) - DEFAULT
new VoucherCondition($voucher, order: 50, dynamic: true);
```

**Recommendation:** Use dynamic by default, use static only for simple vouchers without rules.

### Question 2: Multiple Extensions

✅ **Manual composition is best!**

```php
// Your app: app/Support/Cart/ExtendedCart.php
class ExtendedCart extends Cart
{
    use HasVouchers;
    use HasLoyaltyPoints;
    use HasGiftCards;
}

// Bind once in AppServiceProvider
$this->app->bind(Cart::class, ExtendedCart::class);
```

**Result:** Clean architecture, full control, no conflicts! 🎉
