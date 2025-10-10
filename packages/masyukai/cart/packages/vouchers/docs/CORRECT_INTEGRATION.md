# Correct Integration Architecture

## ❌ The Problem We Just Fixed

Initially, we made the Cart class directly use the HasVouchers trait:

```php
// ❌ WRONG - Creates backward dependency!
namespace MasyukAI\Cart;

use MasyukAI\Cart\Vouchers\Traits\HasVouchers;

final class Cart
{
    use HasVouchers; // ❌ Cart now depends on vouchers package!
}
```

**Problem:** This makes the core cart package **dependent** on the vouchers package, breaking independence.

## ✅ The Correct Solution: Two Integration Approaches

### Approach 1: Using Cart's Native Dynamic Conditions (Recommended)

The cart package already has a **ManagesDynamicConditions** trait that handles automatic condition application/removal based on rules. VoucherCondition already extends CartCondition and has dynamic validation built-in.

```php
use MasyukAI\Cart\Facades\Cart;
use MasyukAI\Cart\Vouchers\Conditions\VoucherCondition;
use MasyukAI\Cart\Vouchers\Facades\Voucher;

// Find voucher
$voucherData = Voucher::find('SUMMER20');

// Create voucher condition
$voucherCondition = new VoucherCondition($voucherData);

// Register as dynamic condition (automatic validation)
Cart::registerDynamicCondition($voucherCondition);

// Cart automatically evaluates and applies/removes based on rules
Cart::evaluateDynamicConditions();
```

**Benefits:**
- ✅ No backward dependency
- ✅ Uses cart's existing dynamic condition system
- ✅ Automatic validation on cart changes
- ✅ No modification to core cart package needed

### Approach 2: Application-Level Trait Extension (Alternative)

For a more convenient API, applications can **extend** the Cart class in their own codebase:

```php
// app/Support/Cart/CartWithVouchers.php
namespace App\Support\Cart;

use MasyukAI\Cart\Cart as BaseCart;
use MasyukAI\Cart\Vouchers\Traits\HasVouchers;

class CartWithVouchers extends BaseCart
{
    use HasVouchers;
}
```

Then bind it in your service provider:

```php
// app/Providers/AppServiceProvider.php
use App\Support\Cart\CartWithVouchers;
use MasyukAI\Cart\Cart;

public function register()
{
    $this->app->bind(Cart::class, CartWithVouchers::class);
}
```

Now you get the convenient API:

```php
Cart::applyVoucher('SUMMER20'); // Works!
```

**Benefits:**
- ✅ No backward dependency on core packages
- ✅ Convenient API (Cart::applyVoucher)
- ✅ Application controls the integration
- ✅ Can mix multiple extensions

## 🎯 Recommended Integration Method

### For Most Applications: Use Approach 1 (Dynamic Conditions)

This is the cleanest approach that maintains package independence:

```php
// In your controller or service
use MasyukAI\Cart\Facades\Cart;
use MasyukAI\Cart\Vouchers\Services\VoucherService;
use MasyukAI\Cart\Vouchers\Conditions\VoucherCondition;

class ApplyVoucherAction
{
    public function __construct(
        private VoucherService $voucherService
    ) {}
    
    public function execute(string $code): void
    {
        // Validate voucher
        $validationResult = $this->voucherService->validate($code, Cart::instance());
        
        if (!$validationResult->isValid) {
            throw new InvalidVoucherException($validationResult->reason);
        }
        
        // Get voucher data
        $voucherData = $this->voucherService->find($code);
        
        // Create condition
        $condition = new VoucherCondition($voucherData);
        
        // Register as dynamic condition
        Cart::registerDynamicCondition($condition);
        
        // Cart will automatically validate and apply
        Cart::evaluateDynamicConditions();
    }
}
```

### For Convenience: Wrap in a Service

Create a helper service in your application:

```php
// app/Services/CartVoucherService.php
namespace App\Services;

use MasyukAI\Cart\Facades\Cart;
use MasyukAI\Cart\Vouchers\Conditions\VoucherCondition;
use MasyukAI\Cart\Vouchers\Exceptions\InvalidVoucherException;
use MasyukAI\Cart\Vouchers\Facades\Voucher;

class CartVoucherService
{
    public function apply(string $code): void
    {
        $validationResult = Voucher::validate($code, Cart::instance());
        
        if (!$validationResult->isValid) {
            throw new InvalidVoucherException($validationResult->reason);
        }
        
        $voucherData = Voucher::find($code);
        $condition = new VoucherCondition($voucherData);
        
        Cart::registerDynamicCondition($condition);
        Cart::evaluateDynamicConditions();
    }
    
    public function remove(string $code): void
    {
        Cart::removeDynamicCondition("voucher_{$code}");
    }
    
    public function hasVoucher(?string $code = null): bool
    {
        if ($code === null) {
            return Cart::getDynamicConditions()
                ->filter(fn($c) => $c instanceof VoucherCondition)
                ->isNotEmpty();
        }
        
        return Cart::getDynamicConditions()->has("voucher_{$code}");
    }
}
```

Then use it:

```php
app(CartVoucherService::class)->apply('SUMMER20');
```

Or register a facade:

```php
// config/app.php
'aliases' => [
    'CartVoucher' => App\Facades\CartVoucher::class,
],

// Usage
CartVoucher::apply('SUMMER20');
CartVoucher::remove('SUMMER20');
CartVoucher::hasVoucher('SUMMER20');
```

## 📊 Architecture Comparison

### ❌ Wrong Approach (Backward Dependency)

```
┌─────────────────────────────────────────┐
│     masyukai/cart (Core Package)        │
│                                         │
│  Cart class uses HasVouchers trait     │
│         ↓                               │
│  Depends on masyukai/cart-vouchers     │ ← PROBLEM!
└─────────────────────────────────────────┘
```

### ✅ Correct Approach 1 (Dynamic Conditions)

```
┌─────────────────────────────────────────┐
│     masyukai/cart (Core Package)        │
│                                         │
│  Cart has ManagesDynamicConditions     │
│  (generic condition system)            │
└─────────────────────────────────────────┘
             ↑
             │ extends CartCondition
             │
┌─────────────────────────────────────────┐
│   masyukai/cart-vouchers (Package)     │
│                                         │
│  VoucherCondition extends CartCondition│
│  Voucher logic + integration           │
└─────────────────────────────────────────┘
             ↑
             │ uses
             │
┌─────────────────────────────────────────┐
│    Your Laravel Application             │
│                                         │
│  Registers voucher conditions          │
│  Provides convenient wrappers          │
└─────────────────────────────────────────┘
```

### ✅ Correct Approach 2 (Application Extension)

```
┌─────────────────────────────────────────┐
│     masyukai/cart (Core Package)        │
│                                         │
│  Cart class (no voucher knowledge)     │
└─────────────────────────────────────────┘
             ↑                    ↑
             │ extends            │ provides trait
             │                    │
┌────────────┴────────┐  ┌────────┴─────────────────┐
│  Your Application   │  │  masyukai/cart-vouchers  │
│                     │  │                          │
│  CartWithVouchers   │  │  HasVouchers trait       │
│  extends Cart       │  │                          │
│  uses HasVouchers ←─┼──┤                          │
└─────────────────────┘  └──────────────────────────┘
```

## 🔧 What Needs to Change in HasVouchers Trait

The HasVouchers trait should work with the dynamic conditions system:

```php
trait HasVouchers
{
    public function applyVoucher(string $code, int $order = 100): self
    {
        // Validate
        $validationResult = Voucher::validate($code, $this);
        
        if (!$validationResult->isValid) {
            throw new InvalidVoucherException($validationResult->reason);
        }
        
        // Create condition
        $voucherData = Voucher::find($code);
        $condition = new VoucherCondition($voucherData, $order);
        
        // Use cart's dynamic condition system
        $this->registerDynamicCondition($condition);
        $this->evaluateDynamicConditions();
        
        return $this;
    }
    
    public function removeVoucher(string $code): self
    {
        $this->removeDynamicCondition("voucher_{$code}");
        return $this;
    }
    
    public function hasVoucher(?string $code = null): bool
    {
        $vouchers = $this->getDynamicConditions()
            ->filter(fn($c) => $c instanceof VoucherCondition);
            
        if ($code === null) {
            return $vouchers->isNotEmpty();
        }
        
        return $vouchers->has("voucher_{$code}");
    }
    
    // ... other methods adapted to use dynamic conditions
}
```

## 📝 Updated Documentation

### For Package Users

**Option 1: Direct Usage (No Helper Trait)**

```php
use MasyukAI\Cart\Facades\Cart;
use MasyukAI\Cart\Vouchers\Conditions\VoucherCondition;
use MasyukAI\Cart\Vouchers\Facades\Voucher;

$voucherData = Voucher::find('SUMMER20');
$condition = new VoucherCondition($voucherData);

Cart::registerDynamicCondition($condition);
Cart::evaluateDynamicConditions();
```

**Option 2: With HasVouchers Trait (Application-Level Extension)**

```php
// 1. Extend Cart in your application
class CartWithVouchers extends \MasyukAI\Cart\Cart
{
    use \MasyukAI\Cart\Vouchers\Traits\HasVouchers;
}

// 2. Bind in service provider
$this->app->bind(\MasyukAI\Cart\Cart::class, CartWithVouchers::class);

// 3. Use convenient API
Cart::applyVoucher('SUMMER20');
```

**Option 3: Create a Helper Service**

```php
// Best for most applications - clean separation
app(CartVoucherService::class)->apply('SUMMER20');
```

## ✅ Benefits of This Architecture

1. **Package Independence**
   - Cart package has no knowledge of vouchers
   - Vouchers package extends cart's existing features
   - No backward dependencies

2. **Flexibility**
   - Applications choose integration method
   - Can use trait, service, or direct approach
   - Easy to customize behavior

3. **Uses Cart's Native Features**
   - Dynamic conditions already exist
   - VoucherCondition fits naturally
   - Automatic validation built-in

4. **Clean Separation**
   - Core cart: Generic condition system
   - Vouchers: Specific implementation
   - Application: Integration glue

## 🎯 Summary

**The Problem:** Cart using HasVouchers trait creates backward dependency

**The Solution:** 
1. Cart keeps its generic dynamic condition system (no changes needed)
2. VoucherCondition extends CartCondition (already done)
3. Applications choose how to integrate:
   - Direct: Use `Cart::registerDynamicCondition()`
   - Convenient: Extend Cart in application code
   - Clean: Create application-level helper service

**Result:** Both packages remain independent and can be published separately! ✅
