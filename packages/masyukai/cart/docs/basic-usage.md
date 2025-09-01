# Basic Usage

This guide covers the fundamental operations of the MasyukAI Cart package.

## Adding Items

### Simple Item

```php
use MasyukAI\Cart\Facades\Cart;

// Add a basic item
Cart::add('product-1', 'iPhone 15 Pro', 999.99, 1);
```

### Item with Attributes

```php
// Add item with custom attributes
Cart::add('product-2', 'MacBook Pro', 2499.99, 1, [
    'color' => 'Space Gray',
    'storage' => '512GB',
    'warranty' => '3 years'
]);
```

### Item with Conditions

```php
use MasyukAI\Cart\Conditions\CartCondition;

// Create a discount condition for this specific item
$discount = new CartCondition('bulk-discount', 'discount', 'price', '-10%');

Cart::add('product-3', 'iPad Pro', 799.99, 3, [], [$discount]);
```

### Item with Associated Model

```php
// Associate cart item with an Eloquent model
$product = Product::find(1);

Cart::add('product-1', $product->name, $product->price, 1, [], null, $product);
```

### Adding Multiple Items

```php
// Add multiple items at once
Cart::add([
    [
        'id' => 'product-1',
        'name' => 'iPhone 15 Pro',
        'price' => 999.99,
        'quantity' => 1,
        'attributes' => ['color' => 'Blue']
    ],
    [
        'id' => 'product-2', 
        'name' => 'AirPods Pro',
        'price' => 249.99,
        'quantity' => 2
    ]
]);
```

## Retrieving Items

### Get Cart Data

```php
// Get all cart items as a collection
$items = Cart::getItems();

foreach ($items as $item) {
    echo $item->name . ' - $' . $item->price;
}

// Get complete cart data (items, conditions, totals, metadata)
$cartData = Cart::getContent(); // or Cart::content()
echo "Total: $" . $cartData['total'];
echo "Items count: " . $cartData['count'];

// Get only conditions
$conditions = Cart::getConditions();
```

### Get Specific Item

```php
// Get item by ID
$item = Cart::get('product-1');

if ($item) {
    echo "Item: {$item->name}";
    echo "Quantity: {$item->quantity}";
    echo "Total: {$item->getPriceSum()}";
}
```

### Check if Item Exists

```php
if (Cart::has('product-1')) {
    echo 'Product is in cart';
}
```

## Updating Items

### Update Quantity

```php
// Update item quantity
Cart::update('product-1', ['quantity' => 3]);
```

### Update Attributes

```php
// Update item attributes
Cart::update('product-1', [
    'attributes' => [
        'color' => 'Red',
        'size' => 'Large'
    ]
]);
```

### Update Multiple Properties

```php
// Update quantity and attributes
Cart::update('product-1', [
    'quantity' => 2,
    'attributes' => [
        'color' => 'Black'
    ]
]);
```

## Removing Items

### Remove Single Item

```php
// Remove item by ID
Cart::remove('product-1');
```

### Clear All Items

```php
// Remove all items from cart
Cart::clear();
```

## Cart Information

### Get Totals

```php
// Get subtotal (before conditions)
$subtotal = Cart::getSubTotal();

// Get subtotal with item-level conditions applied
$subtotalWithConditions = Cart::getSubTotalWithConditions();

// Get final total (with all conditions)
$total = Cart::getTotal();
```

### Get Quantities

```php
// Get total quantity of all items
$totalQuantity = Cart::getTotalQuantity();

// Get total number of unique items
$itemCount = Cart::count();
```

### Check if Cart is Empty

```php
if (Cart::isEmpty()) {
    echo 'Cart is empty';
} else {
    echo 'Cart has ' . Cart::count() . ' items';
}
```

## Working with Item Attributes

### Accessing Attributes

```php
$item = Cart::get('product-1');

// Get specific attribute
$color = $item->getAttribute('color');

// Get attribute with default value
$size = $item->getAttribute('size', 'Medium');

// Check if attribute exists
if ($item->hasAttribute('warranty')) {
    echo 'Has warranty: ' . $item->getAttribute('warranty');
}

// Get all attributes
$attributes = $item->attributes->toArray();
```

### Updating Attributes

```php
$item = Cart::get('product-1');

// Add new attribute
$updatedItem = $item->addAttribute('gift_wrap', true);

// Remove attribute
$updatedItem = $item->removeAttribute('old_attribute');

// Update item in cart
Cart::update('product-1', ['attributes' => $updatedItem->attributes->toArray()]);
```

## Working with Associated Models

### Accessing Associated Model

```php
$item = Cart::get('product-1');

if ($item->associatedModel) {
    $product = $item->associatedModel;
    echo "SKU: {$product->sku}";
    echo "Category: {$product->category->name}";
}
```

### Checking Model Association

```php
$item = Cart::get('product-1');

// Check if associated with specific model class
if ($item->isAssociatedWith(Product::class)) {
    echo 'Item is associated with Product model';
}
```

## Item Properties

Each cart item has the following properties:

```php
$item = Cart::get('product-1');

echo $item->id;                    // Unique item ID
echo $item->name;                  // Item name  
echo $item->price;                 // Unit price
echo $item->quantity;              // Quantity
echo $item->attributes;            // Collection of attributes
echo $item->conditions;            // Collection of conditions
echo $item->associatedModel;       // Associated Eloquent model

// Calculated properties
echo $item->getPriceSum();                     // price * quantity
echo $item->getPriceSumWithConditions();       // with item conditions applied
echo $item->getPriceWithConditions();          // single unit price with conditions
echo $item->getDiscountAmount();               // total discount applied
```

## Converting to Array

### Cart to Array

```php
$cartArray = Cart::toArray();
// Returns complete cart data including items and conditions
```

### Item to Array

```php
$item = Cart::get('product-1');
$itemArray = $item->toArray();
```

## Error Handling

The cart throws specific exceptions for error conditions:

```php
use MasyukAI\Cart\Exceptions\InvalidCartItemException;
use MasyukAI\Cart\Exceptions\UnknownModelException;

try {
    // Invalid price will throw exception
    Cart::add('invalid', 'Test', -10, 1);
} catch (InvalidCartItemException $e) {
    echo 'Invalid item: ' . $e->getMessage();
}

try {
    // Non-existent item will return null, not throw exception
    $item = Cart::get('non-existent');
    if (!$item) {
        echo 'Item not found';
    }
} catch (UnknownModelException $e) {
    echo 'Model error: ' . $e->getMessage();
}
```

## Next Steps

- Learn about [Conditions System](conditions.md) for discounts and taxes
- Explore [Multiple Cart Instances](instances.md) for wishlists and comparisons
- Discover [Events](events.md) for custom cart logic
- Check out [Livewire Integration](livewire.md) for reactive UIs
