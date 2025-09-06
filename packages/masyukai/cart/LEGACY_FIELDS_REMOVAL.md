# Legacy Fields Removal Documentation

## Overview
This document describes the removal of legacy fields from the CartItem `toArray()` method and related serialization methods to clean up the API and reduce backwards compatibility overhead.

## Changes Made

### CartItem.php
**Removed legacy fields from `toArray()` method:**
- `price_sum` 
- `price_without_conditions`
- `price_sum_without_conditions` 
- `subtotal_without_conditions`
- `discount_amount`

**These values are still accessible via public methods:**
- `$item->getPriceSum()`
- `$item->getPriceWithoutConditions()` 
- `$item->getPriceSumWithoutConditions()`
- `$item->subtotalWithoutConditions()`
- `$item->getDiscountAmount()`

### CartCollection.php
**Removed from `toFormattedArray()` method:**
- `subtotal_without_conditions`

**Still available via:**
- `$collection->getSubTotalWithoutConditions()`

### ManagesStorage.php (Cart trait)
**Removed from `content()` method:**
- `subtotal_without_conditions`

**Still available via:**
- `$cart->getSubTotalWithoutConditions()`

## Test Updates

### CartItemTest.php
- Updated "convert to array" test to verify legacy fields are NOT present
- Added explicit verification that the methods still work correctly
- Confirmed all calculated values are accessible via public methods

### CartCollectionTest.php
- Updated "formatted array" test to expect clean array structure
- Removed expectation for `subtotal_without_conditions` field

## Migration Guide

If your application was relying on these legacy fields in serialized cart data:

### Before (Legacy):
```php
$cartArray = $item->toArray();
$priceSum = $cartArray['price_sum'];
$discountAmount = $cartArray['discount_amount'];
```

### After (Recommended):
```php
$cartArray = $item->toArray(); // Clean structure
$priceSum = $item->getPriceSum(); // Direct method call
$discountAmount = $item->getDiscountAmount(); // Direct method call
```

## Benefits

1. **Cleaner API**: The `toArray()` output now contains only core data
2. **Reduced confusion**: Clear separation between raw data and calculated values
3. **Better performance**: Smaller serialized arrays
4. **Maintainability**: Fewer fields to maintain in serialization

## Backwards Compatibility

⚠️ **Breaking Change**: This is a breaking change for code that relied on the legacy fields in serialized arrays.

**Safe migration path:**
1. Update any code that accesses these fields from arrays to use the direct methods instead
2. Test thoroughly with your specific use cases
3. Update any API consumers or frontend code that expected these fields

## Verification

All tests pass after removal:
- ✅ 112 CartItem/CartCollection tests (327 assertions)
- ✅ Core cart functionality tests  
- ✅ Price formatting tests
- ✅ Diagnostic tests show clean structure

The cart package maintains full functionality while providing a cleaner, more maintainable API.
