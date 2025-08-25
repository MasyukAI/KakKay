# Laravel Cart Package Guidelines

## Overview
This application uses `joelwmale/laravel-cart` for shopping cart functionality. This is a robust cart implementation for Laravel with support for conditions, database storage, and events.

**Package Version**: Latest (supports Laravel 10, 11, and 12)  
**Repository**: https://github.com/joelwmale/laravel-cart  

## Installation & Configuration

### Installation
```bash
composer require joelwmale/laravel-cart
```

### Configuration
Publish the configuration file:
```bash
php artisan vendor:publish --provider="Joelwmale\Cart\CartServiceProvider" --tag="config"
```

### Configuration Options
```php
// config/cart.php
return [
    'format_numbers' => env('LARAVEL_CART_FORMAT_VALUES', false),
    'decimals' => env('LARAVEL_CART_DECIMALS', 2),
    'round_mode' => env('LARAVEL_CART_ROUND_MODE', 'down'),
];
```

## Basic Usage

### Adding Items to Cart

#### Simple Product
```php
Cart::add(
    455, // product id
    'Sample Item', // product name
    100.99, // product price
    2, // quantity
    [] // optional attributes
);
```

#### Array Format
```php
Cart::add([
    456, // product id
    'Leather Shoes', // product name
    187, // product price
    1, // quantity
    [] // optional attributes
]);
```

#### With Attributes
```php
Cart::add([
    457, // product id
    'T-Shirt', // product name
    29.99, // product price
    1, // quantity
    [
        'size' => 'L',
        'color' => 'Blue'
    ] // attributes
]);
```

#### Multiple Items at Once
```php
Cart::add(
    [
        456, // product id
        'Leather Shoes', // product name
        187, // product price
        1, // quantity
        [] // optional attributes
    ],
    [
        431, // product id
        'Leather Jacket', // product name
        254.50, // product price
        1, // quantity
        [] // optional attributes
    ]
);
```

### Updating Cart Items

#### Update Item Details
```php
Cart::update(
    456, // product id
    [   
        'name' => 'New Item Name', // new item name
        'price' => 98.67, // new item price
    ]
);
```

#### Update Quantity (Relative)
```php
// Add to existing quantity
Cart::update(
    456, // product id
    [
        'quantity' => 2, // adds 2 to current quantity
    ]
);

// Reduce quantity
Cart::update(
    456, 
    [
        'quantity' => -1, // reduces by 1
    ]
);
```

#### Update Quantity (Absolute)
```php
Cart::update(
    456, // product id
    [
        'quantity' => [
            'relative' => false,
            'value' => 5 // sets quantity to exactly 5
        ],
    ]
);
```

### Retrieving Cart Data

#### Get Cart Contents
```php
$cartContents = Cart::getContent();

// Transform to array or JSON
$cartArray = $cartContents->toArray();
$cartJson = $cartContents->toJson();

// Count items (not quantity)
$itemCount = $cartContents->count();
```

#### Get Specific Item
```php
$item = Cart::get(456);

// Get item's total price
$summedPrice = Cart::get($itemId)->getPriceSum();
```

#### Get Cart Totals
```php
// Total quantity of all items
$totalQuantity = Cart::getTotalQuantity();

// Subtotal (without conditions)
$subTotal = Cart::getSubTotal();

// Subtotal without any conditions applied
$subTotalWithoutConditions = Cart::getSubTotalWithoutConditions();

// Final total (with all conditions)
$total = Cart::getTotal();
```

### Removing Items
```php
// Remove specific item
Cart::remove(456);

// Check if cart is empty
$isEmpty = Cart::isEmpty();

// Clear entire cart
Cart::clear();

// Clear items only (keep conditions)
Cart::clearItems();
```

## Session Management

### Cart Session Keys
```php
// Bind cart to specific user
Cart::setSessionKey(User::first()->id);

// Use before any other cart methods
Cart::setSessionKey($sessionKey);
```

## Conditions System

Conditions are powerful for adding discounts, taxes, shipping, etc. They can be applied at cart level or item level.

### Cart-Level Conditions

#### Basic Condition
```php
$condition = new \Joelwmale\Cart\CartCondition([
    'name' => 'Tax: 10%',
    'type' => 'tax',
    'target' => 'subtotal', // applied to cart subtotal
    'value' => '10%',
    'attributes' => [
        'description' => 'Compulsory tax',
    ]
]);

Cart::condition($condition);
```

#### Multiple Conditions with Order
```php
$tax = new \Joelwmale\Cart\CartCondition([
    'name' => 'Tax: 10%',
    'type' => 'tax',
    'target' => 'subtotal',
    'value' => '10%',
    'order' => 2
]);

$shipping = new \Joelwmale\Cart\CartCondition([
    'name' => 'Shipping: $15',
    'type' => 'shipping',
    'target' => 'subtotal',
    'value' => '+15',
    'order' => 1
]);

// Add individually or as array
Cart::condition($tax);
Cart::condition($shipping);
// OR
Cart::condition([$tax, $shipping]);
```

#### Conditions with Minimum/Maximum Values
```php
// Activate only after minimum amount
$tenPercentOff = new CartCondition([
    'name' => '10% Off',
    'type' => 'discount',
    'target' => 'subtotal',
    'value' => '-10%',
    'minimum' => 120, // activate only if subtotal >= 120
    'order' => 1,
]);

// Activate only up to maximum amount
$shipping = new CartCondition([
    'name' => 'Shipping',
    'type' => 'shipping',
    'target' => 'subtotal',
    'value' => '12',
    'maximum' => 200, // activate only if subtotal <= 200
    'order' => 1,
]);
```

### Item-Level Conditions

#### Adding Conditions During Item Creation
```php
$saleCondition = new \Joelwmale\Cart\CartCondition([
    'name' => '50% Off',
    'type' => 'sale',
    'value' => '-50%',
]);

$product = [
    'id' => 456,
    'name' => 'Sample Item 1',
    'price' => 100,
    'quantity' => 1,
    'attributes' => [],
    'conditions' => $saleCondition
];

Cart::add($product);
```

#### Multiple Item Conditions
```php
$saleCondition = new \Joelwmale\Cart\CartCondition([
    'name' => 'SALE 5%',
    'type' => 'sale',
    'value' => '-5%',
]);

$discountCode = new CartCondition([
    'name' => 'Discount Code',
    'type' => 'promo',
    'value' => '-25',
]);

$item = [
    'id' => 456,
    'name' => 'Sample Item 1',
    'price' => 100,
    'quantity' => 1,
    'attributes' => [],
    'conditions' => [$saleCondition, $discountCode]
];

Cart::add($item);
```

#### Adding Conditions to Existing Items
```php
$condition = new CartCondition([
    'name' => 'COUPON 101',
    'type' => 'coupon',
    'value' => '-5%',
]);

Cart::addItemCondition(456, $condition);
```

### Managing Conditions

#### Retrieving Conditions
```php
// Get all cart conditions
$cartConditions = Cart::getConditions();

foreach($cartConditions as $condition) {
    $condition->getTarget();
    $condition->getName();
    $condition->getType();
    $condition->getValue();
    $condition->getOrder();
    $condition->getMinimum();
    $condition->getMaximum();
    $condition->getAttributes();
}

// Get conditions as array (useful for Livewire)
$cartConditions = Cart::getConditions(true);

// Get specific condition by name
$condition = Cart::getCondition('GST');

// Get only active conditions
$activeConditions = Cart::getConditions(active: true);

// Get conditions by type
$taxConditions = Cart::getConditionsByType('tax');
```

#### Calculating Condition Values
```php
// Method 1: Using condition instance
$subTotal = Cart::getSubTotal();
$condition = Cart::getCondition('10% GST');
$conditionValue = $condition->getCalculatedValue($subTotal);

// Method 2: Using cart method
$conditionValue = Cart::getCalculatedValueForCondition('Coupon Discount');
```

#### Removing Conditions
```php
// Remove specific cart condition
Cart::removeCartCondition('Summer Sale 5%');

// Remove specific item condition
Cart::removeItemCondition(456, 'SALE 5%');

// Clear all item conditions for specific item
Cart::clearItemConditions(456);

// Clear all cart conditions
Cart::clearCartConditions();

// Clear all conditions (cart + items)
Cart::clearAllConditions();

// Remove conditions by type
Cart::removeConditionsByType('tax');
```

## Item Methods

### Item Price Methods
```php
$item = Cart::get(456);

// Price without conditions
$item->getPriceSum();

// Price with item conditions
$item->getPriceWithConditions();

// Sum with item conditions
$item->getPriceSumWithConditions();
```

## Storage Options

### Session Storage (Default)
Cart data is stored in Laravel's session by default.

### Database Storage
For persistent cart storage across sessions:

#### Create Cart Model
```php
// Migration
$table->string('session_id');
$table->text('items');
$table->text('conditions');

// Model
class Cart extends Model
{
    protected $guarded = [];

    protected $casts = [
        'items' => 'array',
        'conditions' => 'array',
    ];
}
```

#### Update Configuration
```php
// config/cart.php
return [
    'driver' => 'database',
    'storage' => [
        'database' => [
            'model' => \App\Models\Cart::class,
            'id' => 'session_id',
            'items' => 'items',
            'conditions' => 'conditions',
        ],
    ],
];
```

## Events

Laravel Cart fires several events you can listen to:

### Available Events
```php
// Cart lifecycle
Event::listen('LaravelCart.Created', function () {
    // cart was created
});

// Item management
Event::listen('LaravelCart.Adding', function ($item) {
    // item is being added
});

Event::listen('LaravelCart.Added', function ($item) {
    // item was added
});

Event::listen('LaravelCart.Updating', function ($item) {
    // item is being updated
});

Event::listen('LaravelCart.Updated', function ($item) {
    // item was updated
});

Event::listen('LaravelCart.Removing', function ($item) {
    // item is being removed
});

Event::listen('LaravelCart.Removed', function ($item) {
    // item was removed
});

// Cart clearing
Event::listen('LaravelCart.Clearing', function () {
    // cart is being cleared
});

Event::listen('LaravelCart.Cleared', function () {
    // cart was cleared
});
```

## Best Practices

### 1. Always Use Session Keys for Multi-User Applications
```php
// In your controller or middleware
Cart::setSessionKey(auth()->user()->id ?? session()->getId());
```

### 2. Add Conditions Before Calculating Totals
```php
// Add all cart-level conditions first
Cart::condition($taxCondition);
Cart::condition($shippingCondition);

// Then calculate totals
$subtotal = Cart::getSubTotal();
$total = Cart::getTotal();
```

### 3. Use Events for Logging or Analytics
```php
Event::listen('LaravelCart.Added', function ($item) {
    Log::info('Item added to cart', ['item' => $item]);
    // Track analytics
});
```

### 4. Validate Items Before Adding
```php
public function addToCart(Request $request)
{
    $request->validate([
        'product_id' => 'required|exists:products,id',
        'quantity' => 'required|integer|min:1',
    ]);

    $product = Product::find($request->product_id);

    Cart::add([
        'id' => $product->id,
        'name' => $product->name,
        'price' => $product->price,
        'quantity' => $request->quantity,
        'attributes' => [
            'image' => $product->image,
            'slug' => $product->slug,
        ]
    ]);
}
```

### 5. Handle Cart Persistence
```php
// For guest users, transfer cart on login
public function transferGuestCart(User $user)
{
    $guestSessionId = session()->getId();
    Cart::setSessionKey($guestSessionId);
    $guestCart = Cart::getContent();

    Cart::setSessionKey($user->id);
    
    foreach ($guestCart as $item) {
        Cart::add([
            'id' => $item->id,
            'name' => $item->name,
            'price' => $item->price,
            'quantity' => $item->quantity,
            'attributes' => $item->attributes->toArray(),
        ]);
    }

    // Clear guest cart
    Cart::setSessionKey($guestSessionId);
    Cart::clear();
}
```

## Common Use Cases

### E-commerce Cart with Tax and Shipping
```php
// Add products
Cart::add([
    'id' => 1,
    'name' => 'T-Shirt',
    'price' => 2999, // RM 29.99
    'quantity' => 2,
    'attributes' => ['size' => 'L', 'color' => 'Blue']
]);

// Add tax condition
$tax = new CartCondition([
    'name' => 'GST 6%',
    'type' => 'tax',
    'target' => 'subtotal',
    'value' => '6%',
    'order' => 1,
]);

// Add shipping condition
$shipping = new CartCondition([
    'name' => 'Standard Shipping',
    'type' => 'shipping',
    'target' => 'subtotal',
    'value' => '+15.00',
    'maximum' => 100, // Free shipping over RM 100
    'order' => 2,
]);

Cart::condition([$tax, $shipping]);

// Get totals
$subtotal = Cart::getSubTotal(); // RM 59.98
$total = Cart::getTotal(); // RM 78.58 (with tax + shipping)
```

### Discount Codes
```php
// Apply percentage discount
$discountCondition = new CartCondition([
    'name' => 'SAVE10',
    'type' => 'discount',
    'target' => 'subtotal',
    'value' => '-10%',
    'minimum' => 50, // Only for orders above RM 50
]);

Cart::condition($discountCondition);
```

### Buy One Get One (BOGO)
```php
// Apply BOGO as item condition
$bogoCondition = new CartCondition([
    'name' => 'BOGO 50% Off',
    'type' => 'discount',
    'value' => '-50%',
]);

Cart::addItemCondition($productId, $bogoCondition);
```

This package provides a robust and flexible cart system that integrates well with Laravel applications. Use it for any e-commerce or shopping cart functionality in this application.
