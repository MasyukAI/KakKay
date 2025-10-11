# Integration Approaches - Choose What Fits Your Needs

## 🎯 Three Ways to Integrate Vouchers with Cart

### Approach 1: Direct Dynamic Conditions (No Dependencies)

**Best for:** Applications that want zero package coupling

**How it works:** Use cart's existing dynamic condition system

```php
use MasyukAI\Cart\Facades\Cart;
use MasyukAI\Cart\Vouchers\Conditions\VoucherCondition;
use MasyukAI\Cart\Vouchers\Facades\Voucher;

// Validate voucher
$validationResult = Voucher::validate('SUMMER20', Cart::instance());

if (!$validationResult->isValid) {
    throw new InvalidVoucherException($validationResult->reason);
}

// Get voucher data
$voucherData = Voucher::find('SUMMER20');

// Create condition
$condition = new VoucherCondition($voucherData);

// Register as dynamic condition
Cart::registerDynamicCondition($condition);

// Cart automatically validates and applies
Cart::evaluateDynamicConditions();
```

**Pros:**
- ✅ Zero backward dependencies
- ✅ Uses cart's native dynamic conditions
- ✅ Automatic validation on cart changes
- ✅ Maximum flexibility

**Cons:**
- ❌ More verbose
- ❌ Need to understand dynamic conditions

---

### Approach 2: Application-Level Extension (Recommended)

**Best for:** Most applications - convenient API with clean architecture

**How it works:** Extend Cart class in your application

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

Bind in service provider:

```php
// app/Providers/AppServiceProvider.php
use App\Support\Cart\CartWithVouchers;
use MasyukAI\Cart\Cart;

public function register(): void
{
    $this->app->bind(Cart::class, CartWithVouchers::class);
}
```

Now use the convenient API:

```php
use MasyukAI\Cart\Facades\Cart;

Cart::applyVoucher('SUMMER20');
Cart::removeVoucher('SUMMER20');
Cart::hasVoucher('SUMMER20'); // true
Cart::getVoucherDiscount(); // 20.00
```

**Pros:**
- ✅ Convenient API (Cart::applyVoucher)
- ✅ No backward dependency (extension is in your app)
- ✅ All HasVouchers trait methods available
- ✅ Easy to test and mock

**Cons:**
- ❌ Requires one-time setup
- ❌ Need to understand Laravel service binding

---

### Approach 3: Helper Service (Clean Separation)

**Best for:** Applications with complex business logic around vouchers

**How it works:** Create a dedicated service in your application

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
        
        event(new VoucherApplied(Cart::instance(), $voucherData));
    }
    
    public function remove(string $code): void
    {
        $condition = Cart::getDynamicConditions()->get("voucher_{$code}");
        
        if ($condition) {
            Cart::removeDynamicCondition("voucher_{$code}");
            event(new VoucherRemoved(Cart::instance(), $condition->getVoucher()));
        }
    }
    
    public function hasVoucher(?string $code = null): bool
    {
        $vouchers = $this->getAppliedVouchers();
        
        if ($code === null) {
            return count($vouchers) > 0;
        }
        
        return isset($vouchers["voucher_{$code}"]);
    }
    
    public function getAppliedVouchers(): array
    {
        return Cart::getDynamicConditions()
            ->filter(fn($c) => $c instanceof VoucherCondition)
            ->all();
    }
    
    public function getVoucherDiscount(): float
    {
        $discount = 0.0;
        $subtotal = Cart::subtotal();
        
        foreach ($this->getAppliedVouchers() as $voucher) {
            $discountAmount = abs($voucher->getCalculatedValue($subtotal));
            $discount += $discountAmount;
            
            if (config('vouchers.cart.allow_stacking', false)) {
                $subtotal -= $discountAmount;
            }
        }
        
        return $discount;
    }
}
```

Register as a singleton:

```php
// app/Providers/AppServiceProvider.php
$this->app->singleton(CartVoucherService::class);
```

Use it:

```php
app(CartVoucherService::class)->apply('SUMMER20');
```

Or create a facade:

```php
// app/Facades/CartVoucher.php
namespace App\Facades;

use Illuminate\Support\Facades\Facade;
use App\Services\CartVoucherService;

class CartVoucher extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return CartVoucherService::class;
    }
}
```

Then use:

```php
use App\Facades\CartVoucher;

CartVoucher::apply('SUMMER20');
CartVoucher::remove('SUMMER20');
CartVoucher::hasVoucher('SUMMER20');
CartVoucher::getVoucherDiscount();
```

**Pros:**
- ✅ Clean separation of concerns
- ✅ Easy to add business logic
- ✅ Great for complex voucher rules
- ✅ Easy to test in isolation

**Cons:**
- ❌ More setup required
- ❌ Extra layer of abstraction

---

## 📊 Comparison Matrix

| Feature | Approach 1<br>(Direct) | Approach 2<br>(Extension) | Approach 3<br>(Service) |
|---------|-----------|-----------|---------|
| **Setup Complexity** | ⭐☆☆☆☆ | ⭐⭐☆☆☆ | ⭐⭐⭐☆☆ |
| **API Convenience** | ⭐⭐☆☆☆ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐☆ |
| **Package Independence** | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ |
| **Flexibility** | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐☆ | ⭐⭐⭐⭐⭐ |
| **Code Maintainability** | ⭐⭐⭐☆☆ | ⭐⭐⭐⭐☆ | ⭐⭐⭐⭐⭐ |
| **Learning Curve** | ⭐⭐⭐☆☆ | ⭐⭐⭐⭐☆ | ⭐⭐⭐☆☆ |
| **Best For** | Minimal apps | Most apps | Complex apps |

---

## 🎯 Recommendation by Project Size

### Small Projects (< 10 routes)
**Use Approach 1 (Direct)** - Keep it simple, avoid extra abstractions

### Medium Projects (10-50 routes)
**Use Approach 2 (Extension)** - Convenient API, minimal setup

### Large Projects (50+ routes)
**Use Approach 3 (Service)** - Clean architecture, easy to test

---

## 💡 Real-World Examples

### Example 1: E-commerce Store (Medium Size)

**Recommendation: Approach 2 (Extension)**

```php
// Setup once:
class CartWithVouchers extends Cart
{
    use HasVouchers;
}

// Bind in AppServiceProvider
$this->app->bind(Cart::class, CartWithVouchers::class);

// Use everywhere:
// CheckoutController.php
public function applyVoucher(Request $request)
{
    try {
        Cart::applyVoucher($request->input('code'));
        return back()->with('success', 'Voucher applied!');
    } catch (InvalidVoucherException $e) {
        return back()->withErrors(['code' => $e->getMessage()]);
    }
}

// CartLivewire.php
public function applyVoucher()
{
    try {
        Cart::applyVoucher($this->voucherCode);
        $this->voucherCode = '';
    } catch (InvalidVoucherException $e) {
        $this->addError('voucher', $e->getMessage());
    }
}
```

### Example 2: Marketplace (Large Size)

**Recommendation: Approach 3 (Service)**

```php
// CartVoucherService.php - with business logic
class CartVoucherService
{
    public function apply(string $code, ?User $user = null): void
    {
        // Custom validation logic
        if ($user && $user->hasUsedVoucher($code)) {
            throw new InvalidVoucherException('Already used');
        }
        
        // Apply voucher
        $validationResult = Voucher::validate($code, Cart::instance());
        
        if (!$validationResult->isValid) {
            throw new InvalidVoucherException($validationResult->reason);
        }
        
        $voucherData = Voucher::find($code);
        $condition = new VoucherCondition($voucherData);
        
        Cart::registerDynamicCondition($condition);
        Cart::evaluateDynamicConditions();
        
        // Track in analytics
        Analytics::track('voucher_applied', [
            'code' => $code,
            'user_id' => $user?->id,
            'cart_total' => Cart::total(),
        ]);
    }
    
    // More custom methods...
}
```

### Example 3: API-Only Application

**Recommendation: Approach 1 (Direct)**

```php
// VoucherController.php
public function apply(Request $request)
{
    $validationResult = Voucher::validate(
        $request->input('code'),
        Cart::instance()
    );
    
    if (!$validationResult->isValid) {
        return response()->json([
            'error' => $validationResult->reason
        ], 422);
    }
    
    $voucherData = Voucher::find($request->input('code'));
    $condition = new VoucherCondition($voucherData);
    
    Cart::registerDynamicCondition($condition);
    Cart::evaluateDynamicConditions();
    
    return response()->json([
        'message' => 'Voucher applied',
        'discount' => Cart::getCondition("voucher_{$request->input('code')}")->getCalculatedValue(Cart::subtotal()),
        'total' => Cart::total(),
    ]);
}
```

---

## 🔧 How Dynamic Conditions Work

The cart package's `ManagesDynamicConditions` trait provides:

```php
// Register a condition with validation rules
Cart::registerDynamicCondition($condition);

// Cart automatically evaluates all dynamic conditions
Cart::evaluateDynamicConditions();

// Get all dynamic conditions
Cart::getDynamicConditions();

// Remove a dynamic condition
Cart::removeDynamicCondition($name);
```

**Key Features:**
- Conditions with rules are "dynamic"
- Cart evaluates rules before applying
- Invalid conditions are automatically removed
- Works seamlessly with VoucherCondition

---

## ✅ Summary

**For Package Independence:**
- Cart package: Generic dynamic condition system ✅
- Vouchers package: VoucherCondition implementation ✅
- Your application: Choose integration approach ✅

**No backward dependencies** - both packages remain independent!

**Choose your approach:**
- Simple app → Direct (Approach 1)
- Most apps → Extension (Approach 2)
- Complex app → Service (Approach 3)

All approaches maintain clean architecture and package independence! 🎉
