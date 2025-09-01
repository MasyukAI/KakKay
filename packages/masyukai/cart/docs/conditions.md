# Conditions System

The conditions system is one of the most powerful features of the MasyukAI Cart package. It allows you to apply discounts, taxes, shipping fees, and other charges to your cart items or the entire cart.

## Understanding Conditions

Conditions can be applied at two levels:
- **Cart-level conditions**: Apply to the entire cart (tax, shipping, cart discounts)
- **Item-level conditions**: Apply to specific items (bulk discounts, item-specific offers)

## Creating Conditions

### Basic Condition

```php
use MasyukAI\Cart\Conditions\CartCondition;

// Create a 10% tax condition
$tax = new CartCondition('tax', 'tax', 'subtotal', '+10%');

// Create a $50 discount
$discount = new CartCondition('discount', 'discount', 'subtotal', '-50');

// Create a 15% item discount
$itemDiscount = new CartCondition('bulk-discount', 'discount', 'price', '-15%');
```

### Condition Parameters

```php
new CartCondition(
    name: 'unique-name',        // Unique identifier
    type: 'tax',                // Type: 'tax', 'discount', 'shipping', etc.
    target: 'subtotal',         // Target: 'subtotal', 'total', 'price'
    value: '+10%',              // Value: percentage or fixed amount
    attributes: [],             // Optional attributes
    order: 0                    // Order of application (lower = first)
);
```

## Condition Targets

### Cart-level Targets

- `subtotal` - Apply to cart subtotal
- `total` - Apply to cart total (after other conditions)

### Item-level Targets

- `price` - Apply to individual item price

## Condition Value Formats

### Percentage Values

```php
// Add 10% tax
new CartCondition('tax', 'tax', 'subtotal', '+10%');

// 15% discount
new CartCondition('discount', 'discount', 'subtotal', '-15%');
```

### Fixed Amount Values

```php
// Add $5 shipping
new CartCondition('shipping', 'shipping', 'subtotal', '+5');

// $20 discount
new CartCondition('discount', 'discount', 'subtotal', '-20');
```

## Applying Conditions

### Cart-level Conditions

```php
use MasyukAI\Cart\Facades\Cart;

// Single condition
$tax = new CartCondition('tax', 'tax', 'subtotal', '+8%');
Cart::condition($tax);

// Multiple conditions
$conditions = [
    new CartCondition('tax', 'tax', 'subtotal', '+8%'),
    new CartCondition('shipping', 'shipping', 'subtotal', '+15'),
    new CartCondition('discount', 'discount', 'subtotal', '-10%')
];
Cart::condition($conditions);
```

### Item-level Conditions

```php
// Add condition to specific item
$discount = new CartCondition('vip-discount', 'discount', 'price', '-20%');
Cart::addItemCondition('product-1', $discount);

// Add condition when adding item
Cart::add('product-2', 'Special Item', 99.99, 1, [], [$discount]);
```

## Managing Conditions

### Getting Conditions

```php
// Get all cart conditions
$conditions = Cart::getConditions();

// Get specific condition
$tax = Cart::getCondition('tax');

// Get conditions by type
$discounts = Cart::getConditions()->getByType('discount');
$taxes = Cart::getConditions()->getByType('tax');
```

### Removing Conditions

```php
// Remove specific condition
Cart::removeCondition('discount');

// Clear all cart conditions
Cart::clearConditions();

// Remove item condition
Cart::removeItemCondition('product-1', 'vip-discount');

// Clear all conditions from item
Cart::clearItemConditions('product-1');
```

## Advanced Condition Features

### Condition Order

Control the order in which conditions are applied:

```php
$tax = new CartCondition('tax', 'tax', 'subtotal', '+10%', [], 1);
$shipping = new CartCondition('shipping', 'shipping', 'subtotal', '+5', [], 2);
$discount = new CartCondition('discount', 'discount', 'subtotal', '-20%', [], 0);

// Applied in order: discount (0), tax (1), shipping (2)
Cart::condition([$tax, $shipping, $discount]);
```

### Condition Attributes

Store additional data with conditions:

```php
$shipping = new CartCondition(
    name: 'express-shipping',
    type: 'shipping', 
    target: 'subtotal',
    value: '+25',
    attributes: [
        'method' => 'UPS Next Day',
        'tracking' => true,
        'estimated_days' => 1
    ]
);

Cart::condition($shipping);

// Access attributes
$condition = Cart::getCondition('express-shipping');
$method = $condition->getAttribute('method');
```

### Conditional Logic

Create conditions with custom logic:

```php
$condition = new CartCondition('bulk-discount', 'discount', 'subtotal', '-10%');

// Only apply if cart total > $100
if (Cart::getSubTotal() > 100) {
    Cart::condition($condition);
}
```

## Working with Condition Collections

### Filtering Conditions

```php
$conditions = Cart::getConditions();

// Get only discounts
$discounts = $conditions->getByType('discount');

// Get only charges (positive values)
$charges = $conditions->getCharges();

// Get only discounts (negative values) 
$savings = $conditions->getDiscounts();

// Filter by target
$subtotalConditions = $conditions->getByTarget('subtotal');
```

### Condition Summary

```php
$conditions = Cart::getConditions();

// Get summary of all conditions
$summary = $conditions->getSummary();
/*
Returns:
[
    'total_discount' => 25.50,
    'total_charge' => 8.99,
    'net_effect' => -16.51,
    'count' => 3
]
*/
```

### Grouping Conditions

```php
$conditions = Cart::getConditions();

// Group by type
$grouped = $conditions->groupByType();
/*
Returns:
[
    'tax' => Collection of tax conditions,
    'discount' => Collection of discount conditions,
    'shipping' => Collection of shipping conditions
]
*/
```

## Complex Examples

### Progressive Discount System

```php
$subtotal = Cart::getSubTotal();

if ($subtotal >= 500) {
    Cart::condition(new CartCondition('vip-discount', 'discount', 'subtotal', '-20%'));
} elseif ($subtotal >= 200) {
    Cart::condition(new CartCondition('premium-discount', 'discount', 'subtotal', '-15%'));
} elseif ($subtotal >= 100) {
    Cart::condition(new CartCondition('standard-discount', 'discount', 'subtotal', '-10%'));
}
```

### Multi-tier Tax System

```php
$conditions = [];

// State tax
$conditions[] = new CartCondition('state-tax', 'tax', 'subtotal', '+7%', [], 1);

// City tax (applied after state tax)
$conditions[] = new CartCondition('city-tax', 'tax', 'total', '+2%', [], 2);

// Federal tax (applied last)
$conditions[] = new CartCondition('federal-tax', 'tax', 'total', '+1%', [], 3);

Cart::condition($conditions);
```

### Shipping Calculator

```php
$weight = Cart::getItems()->sum(fn($item) => $item->getAttribute('weight', 0) * $item->quantity);
$shippingCost = match(true) {
    $weight <= 1 => 5.99,
    $weight <= 5 => 9.99, 
    $weight <= 10 => 15.99,
    default => 25.99
};

$shipping = new CartCondition(
    'shipping',
    'shipping', 
    'subtotal',
    "+{$shippingCost}",
    ['weight' => $weight]
);

Cart::condition($shipping);
```

### Buy X Get Y Free

```php
// Add items
Cart::add('product-1', 'T-Shirt', 29.99, 3);

$item = Cart::get('product-1');
if ($item && $item->quantity >= 2) {
    // Buy 2 get 1 free: 33% discount on total
    $discount = new CartCondition('buy-2-get-1', 'discount', 'price', '-33%');
    Cart::addItemCondition('product-1', $discount);
}
```

## Calculating Totals

Understanding how conditions affect calculations:

```php
// Add items and conditions
Cart::add('product-1', 'Item', 100.00, 2); // $200 subtotal
Cart::condition(new CartCondition('tax', 'tax', 'subtotal', '+10%'));
Cart::condition(new CartCondition('discount', 'discount', 'subtotal', '-50'));

// Get different totals
$subtotal = Cart::getSubTotal();                    // $200.00 (base)
$subtotalWithConditions = Cart::getSubTotalWithConditions(); // $200.00 (no item conditions)
$total = Cart::getTotal();                          // $165.00 (200 - 50 + 10% of 150)
```

## Best Practices

1. **Use descriptive names**: Make condition names clear and unique
2. **Set appropriate order**: Lower numbers are applied first
3. **Use proper targets**: Choose between subtotal, total, and price carefully
4. **Validate before applying**: Check cart state before adding conditions
5. **Store metadata**: Use attributes for additional information
6. **Clean up**: Remove conditions when no longer needed

## Common Patterns

### Coupon System

```php
function applyCoupon(string $code): bool
{
    $coupon = Coupon::where('code', $code)->valid()->first();
    
    if (!$coupon) {
        return false;
    }
    
    $condition = new CartCondition(
        "coupon-{$code}",
        'discount',
        'subtotal', 
        "-{$coupon->value}%",
        ['coupon_id' => $coupon->id]
    );
    
    Cart::condition($condition);
    return true;
}
```

### Member Pricing

```php
if (auth()->user()?->isMember()) {
    $memberDiscount = new CartCondition(
        'member-discount',
        'discount',
        'subtotal',
        '-10%'
    );
    Cart::condition($memberDiscount);
}
```

## Next Steps

- Learn about [Events](events.md) to respond to condition changes
- Explore [Storage](storage.md) for persisting conditions
- Check out [API Reference](api-reference.md) for complete method documentation
