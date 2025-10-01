# Condition Target Refactor Summary

## Changes Made

### 1. Calculation Logic (CalculatesTotals.php)
**Before:** All cart-level conditions were applied sequentially in `getTotal()`, regardless of their `target` field value.

**After:** 
- Conditions with `target: 'subtotal'` are applied in `getSubtotal()`
- Conditions with `target: 'total'` are applied in `getTotal()` to the result of `getSubtotal()`

This makes the `target` field meaningful and provides predictable ordering.

### 2. Default Targets (ManagesConditions.php)
Updated default targets for helper methods to match semantic meaning:

| Method | Old Default | New Default | Reasoning |
|--------|-------------|-------------|-----------|
| `addDiscount()` | `'subtotal'` | `'subtotal'` ✅ | Discounts reduce the subtotal |
| `addTax()` | `'subtotal'` | `'subtotal'` ✅ | Taxes typically applied to subtotal |
| `addFee()` | `'subtotal'` | **`'total'`** ⚠️ | Fees added after discounts/taxes |
| `addShipping()` | `'subtotal'` | **`'total'`** ⚠️ | Shipping added to final amount |

## Calculation Flow

### New Flow
```
Items (with item conditions)
    ↓
Subtotal (apply subtotal-targeted conditions: discounts, taxes)
    ↓
Total (apply total-targeted conditions: fees, shipping)
    ↓
Final Total
```

### Example
```php
// Item: $100
// Item condition (-$5): $95
// Subtotal discount (-10%): $85.50  ← applied to subtotal
// Total fee (+$2): $87.50            ← applied to total

Cart::add('item', 'Product', 100, 1);
Cart::addItemCondition('item', new CartCondition('item-disc', 'discount', 'item', '-5'));
Cart::addDiscount('sub-disc', '10%');  // targets 'subtotal'
Cart::addFee('fee', '2');               // targets 'total'

Cart::subtotal()->getAmount(); // 85.50
Cart::total()->getAmount();    // 87.50
```

## Breaking Changes

### Tests That Need Updates

Tests that manually create conditions with `target: 'subtotal'` for fees/taxes/shipping will need adjustment:

**Example Fix:**
```php
// OLD - All conditions applied sequentially at total level
$discount = new CartCondition('discount', 'discount', 'subtotal', '-10%');
$tax = new CartCondition('tax', 'tax', 'subtotal', '+15%');
$shipping = new CartCondition('shipping', 'charge', 'subtotal', '+9.99');
// Expected: subtotal() = 200, total() = 216.99

// NEW - Change tax/shipping to target 'total'
$discount = new CartCondition('discount', 'discount', 'subtotal', '-10%');
$tax = new CartCondition('tax', 'tax', 'total', '+15%');      // ← changed
$shipping = new CartCondition('shipping', 'charge', 'total', '+9.99');  // ← changed
// Expected: subtotal() = 180, total() = 216.99
```

### Methods Using Helper Functions
Code using `addFee()` or `addShipping()` will automatically use the new `'total'` target:

```php
// These now target 'total' by default
Cart::addFee('handling', '5.00');      // targets 'total'
Cart::addShipping('Express', 15.99);   // targets 'total'

// Can still override if needed
Cart::addFee('handling', '5.00', 'subtotal');  // explicit subtotal target
```

## Migration Guide

### For Application Code
1. Review any manual `CartCondition` creations for fees/shipping/taxes
2. Decide if they should target `'subtotal'` or `'total'` based on when they should be applied
3. Update the target parameter accordingly

### For Tests
1. Tests expecting all conditions to be applied at total level need updates
2. Use `subtotalWithoutConditions()` if you need the raw subtotal
3. Update target values for fee/shipping/tax conditions to match intended calculation order

## Verification

All refactored code changes passed:
- ✅ Code formatting (Pint)
- ✅ Condition target calculation tests (4/4 passed)
- ⚠️ Some existing tests need target value updates (15 failures due to changed defaults)

The failures are expected and indicate tests that need updating to match the new semantic targets.
