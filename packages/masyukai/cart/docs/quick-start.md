# Quick Start Guide

Get up and running with MasyukAI Cart in under 5 minutes.

## 1. Installation

```bash
composer require masyukai/cart
```

The package uses Laravel's auto-discovery, so it's ready to use immediately.

## 2. Your First Cart

```php
use MasyukAI\Cart\Facades\Cart;

// Add your first item
Cart::add('product-1', 'iPhone 15 Pro', 999.99, 1);

// Check what's in the cart
$items = Cart::content();
$total = Cart::total();
$count = Cart::count();

echo "Cart has {$count} items worth ${$total}";
```

## 3. Add More Features

### Add Items with Attributes

```php
Cart::add('laptop-1', 'MacBook Pro', 2499.99, 1, [
    'color' => 'Space Gray',
    'storage' => '512GB',
    'memory' => '16GB'
]);
```

### Apply Discounts and Taxes

```php
// Add a 20% discount
Cart::addDiscount('black-friday', '20%');

// Add sales tax
Cart::addTax('sales-tax', '8.5%');

// Add shipping fee
Cart::addFee('shipping', '9.99');

// Get final total
$finalTotal = Cart::total(); // Includes all conditions
```

### Update and Remove Items

```php
// Update quantity
Cart::update('product-1', ['quantity' => 3]);

// Update attributes
Cart::update('laptop-1', [
    'attributes' => ['color' => 'Silver']
]);

// Remove item
Cart::remove('product-1');

// Clear entire cart
Cart::clear();
```

## 4. Multiple Cart Types

```php
// Main shopping cart
$mainCart = Cart::instance('default');
$mainCart->add('product-1', 'Main Product', 99.99);

// Customer wishlist
$wishlist = Cart::instance('wishlist');
$wishlist->add('product-2', 'Dream Item', 199.99);

// Product comparison
$comparison = Cart::instance('comparison');
$comparison->add('product-3', 'Alternative', 89.99);
```

## 5. Using Livewire Components

Add reactive cart components to your Blade templates:

```blade
<!-- Add to cart button -->
<livewire:add-to-cart 
    product-id="123" 
    product-name="iPhone 15" 
    product-price="999.99"
    :product-attributes="['color' => 'blue']"
/>

<!-- Cart summary in header -->
<livewire:cart-summary />

<!-- Full cart page -->
<livewire:cart-table />
```

## 6. Advanced Usage

### Search and Filter

```php
// Find expensive items
$luxuryItems = Cart::search(function($item) {
    return $item->price > 1000;
});

// Find items by attribute
$blueItems = Cart::search(function($item) {
    return $item->getAttribute('color') === 'blue';
});
```

### Complex Conditions

```php
use MasyukAI\Cart\Conditions\CartCondition;

// Bulk discount for large quantities
$bulkDiscount = new CartCondition(
    'bulk-discount',
    'discount', 
    'price',
    '-15%',
    ['min_quantity' => 10]
);

Cart::addItemCondition('product-1', $bulkDiscount);
```

### Event Handling

```php
// Listen to cart events
Event::listen(ItemAdded::class, function ($event) {
    Log::info('Item added to cart', [
        'item_id' => $event->item->id,
        'cart_instance' => $event->cart->instance()
    ]);
});
```

## 7. Testing Your Implementation

```php
// In your tests
use MasyukAI\Cart\Facades\Cart;

public function test_can_add_item_to_cart()
{
    Cart::add('test-product', 'Test Product', 99.99);
    
    $this->assertEquals(1, Cart::count());
    $this->assertEquals(99.99, Cart::total());
    $this->assertTrue(Cart::has('test-product'));
}
```

## Next Steps

Now that you have the basics working:

1. **[Read the Basic Usage Guide](basic-usage.md)** - Learn all cart operations
2. **[Explore the Conditions System](conditions.md)** - Master discounts and taxes  
3. **[Configure Storage](storage.md)** - Choose the right storage driver
4. **[Set Up Events](events.md)** - Hook into cart lifecycle
5. **[Customize Livewire Components](livewire.md)** - Build your perfect UI

## Need Help?

- 📖 **[Complete Documentation](./)**
- 🐛 **[Report Issues](../../issues)**
- 💬 **[Join Discussions](../../discussions)**
- 📧 **Email**: support@masyukai.com

You're all set! Start building amazing cart experiences. 🚀
