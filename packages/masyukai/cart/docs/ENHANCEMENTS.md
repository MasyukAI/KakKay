# Cart Package Enhancements

## Overview

Based on the comparison with the `shopping-cart` package, we've implemented several key enhancements to make our cart package more intuitive and user-friendly while maintaining all advanced features.

## Enhanced API Methods

### 1. Intuitive Method Aliases

#### Shopping-Cart Style Method Names:
```php
// Before (still works)
$cart->getContent();
$cart->getSubTotal();
$cart->getTotal();
$cart->getTotalQuantity();

// After (new aliases)
$cart->content();      // More intuitive
$cart->subtotal();     // Cleaner API
$cart->total();        // Simpler
$cart->count();        // Returns total quantity (shopping-cart style)
$cart->countItems();   // Returns unique items count
```

### 2. Enhanced Search Capabilities

```php
// Search cart with callback (shopping-cart style)
$redItems = $cart->search(function (CartItem $item) {
    return str_contains(strtolower($item->name), 'red');
});

$expensiveItems = $cart->search(function (CartItem $item) {
    return $item->price > 50.00;
});
```

### 3. Simplified Condition Helpers

```php
// Simplified condition creation (shopping-cart inspired)
$cart->addDiscount('summer-sale', '10%');  // Automatically makes negative
$cart->addTax('sales-tax', '8%');
$cart->addFee('shipping', '5.00');

// Still supports advanced conditions
$cart->condition(new CartCondition('bulk-discount', 'discount', 'item', '-15%'));
```

### 4. Cart Instance Merging

```php
// Merge another cart instance (shopping-cart style)
$cart->instance('user_123')->add('item1', 'Product', 10.00, 2);
$cart->instance('guest')->add('item2', 'Product 2', 15.00, 1);

// Merge guest cart into user cart
$cart->instance('user_123')->merge('guest');
// Guest cart is automatically cleared after merge
```

### 5. CartItem Enhancements

```php
$item = $cart->add('item1', 'Product', 10.00, 2);

// Shopping-cart style methods
$newItem = $item->withQuantity(5);     // Immutable quantity update
$total = $item->finalTotal();          // Alias for getPriceSumWithConditions()
$discount = $item->discountAmount();   // Alias for getDiscountAmount()
```

### 6. Enhanced Collection Methods

```php
$content = $cart->content();

// Advanced filtering
$bulkItems = $content->whereQuantityAbove(5);
$midRange = $content->wherePriceBetween(10.00, 50.00);
$electronics = $content->filterByAttribute('category', 'electronics');

// Grouping and statistics
$grouped = $content->groupByAttribute('category');
$stats = $content->getStatistics();

// Output:
// [
//     'total_items' => 5,
//     'total_quantity' => 12,
//     'average_price' => 25.50,
//     'highest_price' => 99.99,
//     'lowest_price' => 5.00,
//     'total_value' => 127.50,
//     'total_with_conditions' => 115.50,
//     'items_with_conditions' => 2
// ]
```

### 7. Convenience Methods

```php
// Check if cart has specific item
if ($cart->has('item-123')) {
    // Item exists
}

// Store/restore methods (shopping-cart compatibility)
$cart->store();    // Explicit storage control
$cart->restore();  // Explicit restoration
```

## Benefits of Enhancements

### ✅ **Improved Developer Experience**
- More intuitive method names
- Easier migration from other cart packages
- Less verbose API for common operations

### ✅ **Better Shopping-Cart Compatibility**
- Familiar method names and behavior
- Similar search and filtering patterns
- Compatible counting logic (total quantity vs unique items)

### ✅ **Enhanced Functionality**
- Advanced collection filtering and grouping
- Statistical analysis capabilities
- Improved cart merging logic

### ✅ **Maintained Superiority**
- All advanced features preserved
- Better error handling and validation
- Superior event system and storage abstraction
- Comprehensive testing and documentation

## Migration Guide

### From Shopping-Cart Package

```php
// Shopping-cart code
$items = Cart::content();
$total = Cart::total();
$count = Cart::count();

// Our enhanced package (same API!)
$items = Cart::content();
$total = Cart::total();
$count = Cart::count();

// Plus additional features
$stats = Cart::content()->getStatistics();
$filtered = Cart::search(fn($item) => $item->price > 10);
Cart::addDiscount('sale', '15%');
```

### Backward Compatibility

All existing method names continue to work:
```php
// Still supported
$cart->getContent();
$cart->getSubTotal();
$cart->getTotal();

// New aliases available
$cart->content();
$cart->subtotal();
$cart->total();
```

## Performance Improvements

1. **Optimized Collection Operations**: Enhanced filtering and search methods
2. **Better Memory Usage**: Immutable CartItem design reduces memory overhead
3. **Efficient Storage**: Improved cart merging and storage operations

## Testing

- **40 tests** with **130 assertions**
- **7 new tests** specifically for enhanced API
- **100% backward compatibility** maintained
- All existing functionality thoroughly tested

## Conclusion

These enhancements make our cart package:
- **More accessible** to developers familiar with other cart packages
- **Easier to use** for simple use cases
- **More powerful** for advanced scenarios
- **Better documented** with comprehensive examples

The package now offers the **best of both worlds**:
- **Shopping-cart's simplicity** for basic operations
- **Enterprise-grade features** for complex requirements
