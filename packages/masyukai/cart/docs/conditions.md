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
Cart::addCondition($tax);

// Multiple conditions
$conditions = [
    new CartCondition('tax', 'tax', 'subtotal', '+8%'),
    new CartCondition('shipping', 'shipping', 'subtotal', '+15'),
    new CartCondition('discount', 'discount', 'subtotal', '-10%')
];
Cart::addCondition($conditions);
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
Cart::addCondition([$tax, $shipping, $discount]);
```

## Dynamic Conditions

Dynamic conditions are automatically applied or removed based on rules you define. Instead of manually checking cart state and applying conditions, dynamic conditions evaluate their rules after every cart change.

### Creating Dynamic Conditions

```php
use MasyukAI\Cart\Conditions\CartCondition;

// Create a condition that automatically applies when cart total > $100
$bigSpenderDiscount = new CartCondition(
    name: 'big-spender-discount',
    type: 'discount',
    target: 'total',
    value: '-10%',
    rules: [
        fn($cart) => $cart->getRawSubTotalWithoutConditions() > 100,
    ]
);

// Register the dynamic condition
Cart::registerDynamicCondition($bigSpenderDiscount);
```

### Rule Functions

Rules are callable functions that receive the cart and optionally an item (for item-level conditions):

```php
// Cart-level rule (receives only cart)
$cartRule = fn($cart) => $cart->getRawSubTotalWithoutConditions() > 100;

// Item-level rule (receives cart and item)
$itemRule = fn($cart, $item) => $item->quantity >= 3;

// Multiple rules (ALL must return true)
$condition = new CartCondition(
    name: 'vip-bulk-discount',
    type: 'discount',
    target: 'total',
    value: '-15%',
    rules: [
        fn($cart) => $cart->getRawSubTotalWithoutConditions() > 200,
        fn($cart) => $cart->getItems()->count() >= 3,
        fn($cart) => auth()->user()?->isVip() ?? false,
    ]
);
```

### Dynamic Condition Examples

#### Automatic Volume Discounts

```php
// 5% off when cart has 3+ items
$volumeDiscount = new CartCondition(
    name: 'volume-discount',
    type: 'discount',
    target: 'total',
    value: '-5%',
    rules: [
        fn($cart) => $cart->getItems()->count() >= 3,
    ]
);

Cart::registerDynamicCondition($volumeDiscount);
```

#### Free Shipping Threshold

```php
// Free shipping when cart total >= $50
$freeShipping = new CartCondition(
    name: 'free-shipping',
    type: 'discount',
    target: 'total',
    value: '-15', // Offset standard $15 shipping
    rules: [
        fn($cart) => $cart->getRawSubTotalWithoutConditions() >= 50,
    ]
);

Cart::registerDynamicCondition($freeShipping);
```

#### Member-Only Pricing

```php
// 10% member discount for authenticated members
$memberDiscount = new CartCondition(
    name: 'member-discount',
    type: 'discount',
    target: 'total',
    value: '-10%',
    rules: [
        fn($cart) => auth()->check(),
        fn($cart) => auth()->user()->isMember(),
    ]
);

Cart::registerDynamicCondition($memberDiscount);
```

#### Item-Level Dynamic Conditions

```php
// Buy 2 get 1 free for specific items
$buy2Get1Free = new CartCondition(
    name: 'buy-2-get-1-free',
    type: 'discount',
    target: 'item',
    value: '-33%', // 1/3 off = buy 2 get 1 free
    rules: [
        fn($cart, $item) => $item->quantity >= 3,
        fn($cart, $item) => $item->getAttribute('category') === 'clothing',
    ]
);

Cart::registerDynamicCondition($buy2Get1Free);
```

### Managing Dynamic Conditions

#### Getting Dynamic Conditions

```php
// Get all registered dynamic conditions
$dynamicConditions = Cart::getDynamicConditions();

// Check if any dynamic conditions are registered
$hasDynamic = Cart::getDynamicConditions()->isNotEmpty();
```

#### Removing Dynamic Conditions

```php
// Remove a specific dynamic condition
Cart::removeDynamicCondition('big-spender-discount');

// This also removes it from active conditions if it was applied
```

### How Dynamic Conditions Work

1. **Registration**: You register dynamic conditions with their rules
2. **Automatic Evaluation**: After any cart change (add, update, remove), rules are evaluated
3. **Application**: If rules return `true`, the condition is applied to the cart/item
4. **Removal**: If rules return `false`, the condition is removed from the cart/item
5. **No Recursion**: Applied conditions don't include rules to prevent infinite loops

#### Evaluation Triggers

Dynamic conditions are automatically evaluated when:
- Items are added to the cart
- Item quantities are updated
- Items are removed from the cart
- Cart is cleared

```php
// Register dynamic condition
Cart::registerDynamicCondition($bigSpenderDiscount);

Cart::add('product-1', 'Item', 50.00, 1); // $50 total - no discount
Cart::add('product-2', 'Item', 60.00, 1); // $110 total - discount applied!
Cart::remove('product-1');                 // $60 total - discount removed
```

### Advanced Dynamic Rules

#### Complex Business Logic

```php
$holidayDiscount = new CartCondition(
    name: 'holiday-special',
    type: 'discount',
    target: 'total',
    value: '-20%',
    rules: [
        fn($cart) => $cart->getRawSubTotalWithoutConditions() > 75,
        fn($cart) => now()->between('2024-11-25', '2024-12-02'), // Black Friday week
        fn($cart) => $cart->getItems()->contains(fn($item) => 
            $item->getAttribute('category') === 'electronics'
        ),
    ]
);
```

#### Geographic Rules

```php
$localDiscount = new CartCondition(
    name: 'local-customer-discount',
    type: 'discount',
    target: 'total',
    value: '-5%',
    rules: [
        fn($cart) => auth()->check(),
        fn($cart) => auth()->user()->address?->state === 'CA',
        fn($cart) => $cart->getRawSubTotalWithoutConditions() > 50,
    ]
);
```

#### Inventory-Based Rules

```php
$clearanceDiscount = new CartCondition(
    name: 'clearance-auto-discount',
    type: 'discount',
    target: 'item',
    value: '-25%',
    rules: [
        fn($cart, $item) => Product::find($item->id)?->stock < 5,
        fn($cart, $item) => $item->getAttribute('clearance', false),
    ]
);
```

### Best Practices for Dynamic Conditions

1. **Keep rules simple**: Complex logic can impact performance
2. **Use specific names**: Make dynamic condition names descriptive
3. **Consider performance**: Rules run on every cart change
4. **Test thoroughly**: Ensure rules work as expected
5. **Handle edge cases**: Account for auth, null values, etc.
6. **Use appropriate targets**: Choose between cart-level and item-level carefully

### Dynamic vs Static Conditions

| Feature | Static Conditions | Dynamic Conditions |
|---------|------------------|-------------------|
| **Application** | Manual | Automatic |
| **Rules** | In your code | Built into condition |
| **Performance** | Faster | Slight overhead |
| **Flexibility** | Manual control | Auto-responsive |
| **Use Case** | Fixed fees/taxes | Business rule automation |

### Example: Complete Dynamic System

```php
class CartConditionsService
{
    public static function registerAllDynamicConditions(): void
    {
        // Volume discount
        Cart::registerDynamicCondition(new CartCondition(
            name: 'volume-discount',
            type: 'discount',
            target: 'total',
            value: '-5%',
            rules: [fn($cart) => $cart->getItems()->count() >= 3]
        ));
        
        // VIP member discount
        Cart::registerDynamicCondition(new CartCondition(
            name: 'vip-discount',
            type: 'discount',
            target: 'total',
            value: '-15%',
            rules: [
                fn($cart) => auth()->user()?->isVip() ?? false,
                fn($cart) => $cart->getRawSubTotalWithoutConditions() > 100,
            ]
        ));
        
        // Free shipping
        Cart::registerDynamicCondition(new CartCondition(
            name: 'free-shipping',
            type: 'discount',
            target: 'total',
            value: '-10', // Offset shipping cost
            rules: [fn($cart) => $cart->getRawSubTotalWithoutConditions() >= 75]
        ));
    }
}

// Register all dynamic conditions when user visits cart
CartConditionsService::registerAllDynamicConditions();
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

Cart::addCondition($shipping);

// Access attributes
$condition = Cart::getCondition('express-shipping');
$method = $condition->getAttribute('method');
```

### Conditional Logic

Create conditions with custom logic:

```php
$condition = new CartCondition('bulk-discount', 'discount', 'subtotal', '-10%');

// Only apply if cart total > $100
if (Cart::subtotal() > 100) {
    Cart::addCondition($condition);
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
$subtotal = Cart::subtotal();

if ($subtotal >= 500) {
    Cart::addCondition(new CartCondition('vip-discount', 'discount', 'subtotal', '-20%'));
} elseif ($subtotal >= 200) {
    Cart::addCondition(new CartCondition('premium-discount', 'discount', 'subtotal', '-15%'));
} elseif ($subtotal >= 100) {
    Cart::addCondition(new CartCondition('standard-discount', 'discount', 'subtotal', '-10%'));
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

Cart::addCondition($conditions);
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

Cart::addCondition($shipping);
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
Cart::addCondition(new CartCondition('tax', 'tax', 'subtotal', '+10%'));
Cart::addCondition(new CartCondition('discount', 'discount', 'subtotal', '-50'));

// Get different totals
$subtotal = Cart::subtotal();                    // $200.00 (base)
$subtotal = Cart::subtotal(); // $200.00 (no item conditions)
$total = Cart::total();                          // $165.00 (200 - 50 + 10% of 150)
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
    
    Cart::addCondition($condition);
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
    Cart::addCondition($memberDiscount);
}
```

## Next Steps

- Learn about [Events](events.md) to respond to condition changes
- Explore [Storage](storage.md) for persisting conditions
- Check out [API Reference](api-reference.md) for complete method documentation
