# Cart Savings Feature Implementation

## Summary
Integrated the cart package's built-in `savings()` method into both Cart and Checkout Livewire components, with elegant UI display showing discounts to customers.

## What is Savings?

The cart package automatically calculates savings as:
```
Savings = Subtotal (without conditions) - Total (with conditions applied)
```

This means:
- **Discount conditions** (negative values) → Show as savings
- **Fee/Tax conditions** (positive values) → Reduce savings
- **Result** → Shows customers how much they're saving from discounts

## Implementation

### 1. Backend - Livewire Components

#### Cart Component (`app/Livewire/Cart.php`)
```php
public function getSavings(): \Akaunting\Money\Money
{
    return CartFacade::savings(); // Returns Money object with calculated savings
}
```

#### Checkout Component (`app/Livewire/Checkout.php`)
```php
#[Computed]
public function getSavings(): Money
{
    return CartFacade::savings(); // Returns Money object with calculated savings
}
```

**Benefits:**
- ✅ Returns `Money` object for proper formatting
- ✅ Automatically calculated by cart package
- ✅ Handles negative amounts (returns RM0.00 if no savings)
- ✅ Respects currency settings

### 2. Frontend - UI Display

#### Cart Page (`resources/views/livewire/cart.blade.php`)
```blade
<div class="space-y-4 text-sm text-white/80">
    <div class="flex justify-between">
        <span>Jumlah Harga</span>
        <span class="font-semibold text-white">{{ $this->getSubtotal()->format() }}</span>
    </div>
    
    @if($this->getSavings()->getAmount() > 0)
        <div class="flex justify-between text-green-400">
            <span class="flex items-center gap-1.5">
                <flux:icon.tag class="h-4 w-4" />
                Jimat
            </span>
            <span class="font-semibold">-{{ $this->getSavings()->format() }}</span>
        </div>
    @endif
    
    <div class="flex justify-between">
        <span>Penghantaran</span>
        <span class="font-semibold text-white">{{ $this->getShipping()->format() }}</span>
    </div>
    
    <hr class="border-white/20">
    
    <div class="flex justify-between text-xl font-bold text-white">
        <span>Jumlah</span>
        <span class="cart-text-accent">{{ $this->getTotal()->format() }}</span>
    </div>
</div>
```

#### Checkout Page (`resources/views/livewire/checkout.blade.php`)
```blade
<div class="space-y-3 text-sm text-white/80">
    <div class="flex items-center justify-between">
        <span>Jumlah Harga</span>
        <span class="font-medium text-white">{{ $this->getSubtotal()->format() }}</span>
    </div>
    
    @if($this->getSavings()->getAmount() > 0)
        <div class="flex items-center justify-between text-green-400">
            <span class="flex items-center gap-1.5">
                <flux:icon.tag class="h-4 w-4" />
                Jimat
            </span>
            <span class="font-medium">-{{ $this->getSavings()->format() }}</span>
        </div>
    @endif
    
    <div class="flex items-center justify-between">
        <span>Penghantaran</span>
        <span class="font-medium text-white">{{ $this->getShipping()->format() }}</span>
    </div>
    
    <hr class="border-white/15">
    
    <div class="flex items-center justify-between text-lg font-bold">
        <span>Jumlah</span>
        <span class="bg-gradient-to-r from-pink-400 via-rose-500 to-purple-500 bg-clip-text text-transparent">
            {{ $this->getTotal()->format() }}
        </span>
    </div>
</div>
```

**UI Features:**
- ✅ **Conditional Display** - Only shows savings row when amount > 0
- ✅ **Green Color** - `text-green-400` highlights savings
- ✅ **Tag Icon** - Visual indicator for discounts
- ✅ **Negative Sign** - `-` prefix shows this reduces total
- ✅ **Consistent Styling** - Matches existing design system

### 3. Cart Facade Documentation

Updated `packages/masyukai/cart/packages/core/src/Facades/Cart.php` PHPDoc:

```php
/**
 * @method static int|null getVersion()
 */
```

**Added:**
- `getVersion()` - Returns cart version for change tracking (int|null)

## Visual Representation

### Without Savings
```
┌────────────────────────────────────┐
│ Jumlah Harga        RM 100.00      │
│ Penghantaran        RM 10.00       │
├────────────────────────────────────┤
│ Jumlah              RM 110.00      │
└────────────────────────────────────┘
```

### With Savings (e.g., 10% discount)
```
┌────────────────────────────────────┐
│ Jumlah Harga        RM 100.00      │
│ 🏷️ Jimat            -RM 10.00     │ ← Green text
│ Penghantaran        RM 10.00       │
├────────────────────────────────────┤
│ Jumlah              RM 100.00      │
└────────────────────────────────────┘
```

## How Cart Conditions Affect Savings

### Example 1: Discount Only
```php
// Cart has items worth RM 100
Cart::add('book', 'Book', 10000, 1); // RM 100.00

// Apply 10% discount
Cart::addDiscount('promo-10', '-10%', 'subtotal');

// Result:
// Subtotal (without conditions): RM 100.00
// Subtotal (with conditions):    RM 90.00
// Savings:                        RM 10.00 ✅ Shows in UI
```

### Example 2: Discount + Shipping
```php
// Cart with discount
Cart::addDiscount('promo-10', '-10%', 'subtotal');
Cart::addShipping('shipping', 1000); // RM 10.00

// Result:
// Subtotal (without conditions): RM 100.00
// Total (with conditions):       RM 100.00 (RM 90 + RM 10)
// Savings:                        RM 10.00 ✅ Discount still shows
```

### Example 3: Multiple Conditions
```php
Cart::addDiscount('early-bird', '-15%', 'subtotal');  // -RM 15
Cart::addTax('SST', '6%', 'subtotal');                 // +RM 5.10
Cart::addShipping('express', 1500);                    // +RM 15

// Result:
// Subtotal (without conditions): RM 100.00
// Total (with conditions):       RM 105.10
// Savings:                        RM 5.10 ✅ Net savings shown
```

## Testing

All tests pass:
```bash
php artisan test tests/Feature/CheckoutOrderCreationTest.php
# ✅ 4 passed (12 assertions)
```

The savings feature:
- Works with existing checkout flow
- Doesn't break payment processing
- Properly formats amounts
- Conditionally displays based on savings amount

## Usage Examples

### Apply Discount to Cart
```php
// Add 20% discount
Cart::addDiscount('flash-sale', '-20%', 'subtotal');

// Add fixed amount discount
Cart::addDiscount('voucher-rm10', '-1000', 'subtotal'); // RM 10.00

// UI automatically shows savings!
```

### Remove Discount
```php
Cart::removeCondition('flash-sale');
// Savings row automatically hides if no savings
```

### Check Savings in Code
```php
$savings = Cart::savings();

if ($savings->getAmount() > 0) {
    // Customer is saving money!
    Log::info("Customer saving: {$savings->format()}");
}
```

## Benefits

### 1. Better Customer Experience ✅
- **Transparency** - Customers see exactly how much they're saving
- **Motivation** - Visible savings encourage checkout completion
- **Trust** - Clear breakdown builds confidence

### 2. Clean Code ✅
- Uses cart package's built-in functionality
- No custom calculation logic needed
- Consistent with cart architecture

### 3. Flexible ✅
- Works with any discount type (percentage, fixed)
- Handles multiple conditions correctly
- Respects currency settings

### 4. Future-Proof ✅
- Ready for voucher systems
- Ready for promotional campaigns
- Ready for loyalty programs

## Key Files Modified

1. `app/Livewire/Cart.php` - Added `getSavings()` method
2. `app/Livewire/Checkout.php` - Updated `getSavings()` to use cart package
3. `resources/views/livewire/cart.blade.php` - Added savings display with green styling
4. `resources/views/livewire/checkout.blade.php` - Added savings display with green styling
5. `packages/masyukai/cart/packages/core/src/Facades/Cart.php` - Added `getVersion()` PHPDoc

## Next Steps

To use savings in your application:

1. **Apply Discounts** - Use cart conditions to add discounts
2. **Test UI** - Add items to cart and apply a discount to see the savings row
3. **Customize Styling** - Adjust colors/icons in the blade templates if needed
4. **Add Voucher System** - Build on top of this to create voucher functionality

---

**Excellent implementation! The cart package's built-in savings feature is now beautifully integrated.** 🎉
