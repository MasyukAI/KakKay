# MasyukAI Cart Package Guidelines

## Overview
This application uses the custom `masyukai/cart` package for shopping cart functionality. This is a modern, production-ready cart implementation built specifically for Laravel 12+ with comprehensive Livewire integration, advanced condition system, and 96.2% test coverage.

**Package Location**: `packages/masyukai/cart/`  
**Namespace**: `MasyukAI\Cart`  
**Facade**: `MasyukAI\Cart\Facades\Cart`

## Key Features
- ✅ **Modern API**: Clean, intuitive methods with consistent naming
- ✅ **Advanced Conditions**: Flexible discount, tax, and fee system
- ✅ **Multiple Storage**: Session, database, and cache storage options
- ✅ **Livewire Integration**: Ready-to-use reactive components
- ✅ **Event System**: Comprehensive cart and item events
- ✅ **Price Formatting**: Automatic price formatting with multiple transformers
- ✅ **Instance Management**: Multi-cart support for complex scenarios
- ✅ **Migration Tools**: Guest-to-user cart migration utilities

## Installation & Configuration

### Basic Setup
The package is already installed as a local package. Configuration file is at `config/cart.php`:

```php
// config/cart.php
return [
    'storage' => [
        'driver' => 'session', // session, database, cache
        'key' => 'cart',
    ],
    'formatting' => [
        'enabled' => true,
        'transformer' => 'integer', // integer, localized
        'currency' => 'MYR',
        'decimals' => 2,
    ],
    'events' => ['enabled' => true],
];
```

### Database Storage (Optional)
For persistent carts:
```bash
php artisan vendor:publish --tag=cart-migrations
php artisan migrate
```

## Basic Usage

### Adding Items to Cart

#### Simple Product
```php
use MasyukAI\Cart\Facades\Cart;

Cart::add(
    'iphone-15-pro',    // product id  
    'iPhone 15 Pro',    // product name
    999.99,             // product price
    1,                  // quantity
    [                   // optional attributes
        'color' => 'Natural Titanium',
        'storage' => '256GB'
    ]
);
```

#### Multiple Items
```php
Cart::add([
    'id' => 'laptop-pro',
    'name' => 'MacBook Pro',
    'price' => 2499.99,
    'quantity' => 1,
    'attributes' => ['model' => '16-inch', 'color' => 'Space Gray']
], [
    'id' => 'mouse-mx',
    'name' => 'MX Master 3',
    'price' => 99.99,
    'quantity' => 2,
    'attributes' => ['color' => 'Graphite']
]);
```

### Retrieving Cart Data

#### Get Cart Contents
```php
$cartItems = Cart::content();           // CartCollection
$cartArray = Cart::content()->toArray(); 
$itemCount = Cart::countItems();        // Number of unique items
$totalQuantity = Cart::count();         // Total quantity of all items
```

#### Get Specific Item
```php
$item = Cart::get('iphone-15-pro');
$itemPrice = $item->getPriceSum();      // Item total (price × quantity)
$itemWithConditions = $item->getPriceSumWithConditions();
```

#### Get Cart Totals
```php
// ⚠️ IMPORTANT: New API uses formatted methods for user-facing values
$subtotal = Cart::subtotal();                    // Formatted subtotal (no conditions)
$subtotalWithConditions = Cart::subtotalWithConditions(); // With item-level conditions
$total = Cart::total();                          // Final total (all conditions applied)

// Raw values for internal calculations (events, etc.)
$rawSubtotal = Cart::getRawSubtotal();           // Float value
$rawTotal = Cart::getRawTotal();                 // Float value
```

### Updating Cart Items

#### Update Item Details
```php
Cart::update('iphone-15-pro', [
    'name' => 'iPhone 15 Pro Max',
    'price' => 1199.99,
    'quantity' => 2,
    'attributes' => ['storage' => '512GB']
]);
```

#### Update Quantity Only
```php
// Relative quantity change
Cart::updateQuantity('iphone-15-pro', 2);       // Add 2 more
Cart::updateQuantity('iphone-15-pro', -1);      // Remove 1

// Absolute quantity
Cart::setQuantity('iphone-15-pro', 5);          // Set to exactly 5
```

### Removing Items
```php
Cart::remove('iphone-15-pro');                  // Remove specific item
Cart::clear();                                  // Clear entire cart
Cart::isEmpty();                                // Check if cart is empty
```

## Advanced Conditions System

The package uses a powerful condition system for discounts, taxes, fees, and other cart modifications.

### Cart-Level Conditions

#### Basic Condition
```php
use MasyukAI\Cart\Conditions\CartCondition;

$taxCondition = new CartCondition(
    'gst-6',                    // name
    'tax',                      // type
    'subtotal',                 // target
    '6%',                       // value
    ['description' => 'GST 6%'] // attributes (optional)
);

Cart::addCondition($taxCondition);
```

#### Convenience Methods
```php
// Quick discount
Cart::addDiscount('welcome-10', '10%');

// Quick tax
Cart::addTax('gst', '6%');

// Quick fee
Cart::addFee('shipping', '15.00');
```

#### Multiple Conditions with Order
```php
$shipping = new CartCondition('shipping', 'fee', 'subtotal', '+12.00', [], 1);
$discount = new CartCondition('save10', 'discount', 'subtotal', '-10%', [], 2);
$tax = new CartCondition('gst', 'tax', 'subtotal', '6%', [], 3);

Cart::addCondition([$shipping, $discount, $tax]);
```

### Item-Level Conditions

#### Add Condition to Specific Item
```php
$saleCondition = new CartCondition('flash-sale', 'discount', 'item', '-25%');
Cart::addItemCondition('iphone-15-pro', $saleCondition);
```

#### Add Conditions During Item Creation
```php
$bulkDiscount = new CartCondition('bulk-discount', 'discount', 'item', '-15%');

Cart::add('bulk-item', 'Bulk Product', 50.00, 10, [], [$bulkDiscount]);
```

### Managing Conditions

#### Retrieve Conditions
```php
$cartConditions = Cart::getConditions();                    // All cart conditions
$taxConditions = Cart::getConditionsByType('tax');          // Conditions by type
$specificCondition = Cart::getCondition('gst-6');           // Specific condition
$itemConditions = Cart::getItemConditions('iphone-15-pro'); // Item conditions
```

#### Remove Conditions
```php
Cart::removeCondition('welcome-10');                        // Remove specific
Cart::removeConditionsByType('discount');                   // Remove by type
Cart::clearConditions();                                    // Clear all cart conditions
Cart::clearItemConditions('iphone-15-pro');                 // Clear item conditions
```

## Instance Management

For multi-cart scenarios (guest carts, vendor separation, etc.):

```php
// Switch to specific cart instance
Cart::instance('guest_' . session()->getId());
Cart::add('product-1', 'Product 1', 99.99, 1);

// Switch to user cart
Cart::instance('user_' . auth()->id());
Cart::add('product-2', 'Product 2', 149.99, 1);

// Get current instance name
$currentInstance = Cart::getCurrentInstance();

// Merge instances
Cart::instance('user_' . auth()->id())
    ->merge(Cart::instance('guest_' . session()->getId()));
```

## Cart Migration

For guest-to-user cart transfers:

```php
use MasyukAI\Cart\Services\CartMigrationService;

$migrationService = app(CartMigrationService::class);

// Migrate guest cart to user cart
$migrationService->migrateCart(
    'guest_' . session()->getId(),  // source instance
    'user_' . auth()->id(),         // target instance
    'add_quantities'                // merge strategy
);

// Available merge strategies:
// - 'add_quantities': Add quantities together
// - 'keep_highest': Keep highest quantity
// - 'keep_user': Keep user cart items
// - 'replace_with_guest': Replace with guest items
```

## Events

The package fires comprehensive events for cart operations:

### Available Events
```php
use MasyukAI\Cart\Events\{CartCreated, CartUpdated, ItemAdded, ItemUpdated, ItemRemoved};

Event::listen(CartCreated::class, function ($event) {
    Log::info('Cart created', ['instance' => $event->instance]);
});

Event::listen(ItemAdded::class, function ($event) {
    Log::info('Item added', ['item' => $event->item->toArray()]);
});

Event::listen(CartUpdated::class, function ($event) {
    // $event->items (CartCollection)
    // $event->conditions (CartConditionCollection) 
    // $event->instance (string)
    // $event->total (float)
});
```

## Livewire Integration

Ready-to-use Livewire components:

### Add to Cart Component
```php
<livewire:add-to-cart 
    :product-id="$product->id"
    :product-name="$product->name"
    :product-price="$product->price"
    :max-quantity="$product->stock"
    :show-form="true" />
```

### Cart Summary Component
```php
<livewire:cart-summary :show-details="true" />
```

### Cart Table Component
```php
<livewire:cart-table :show-conditions="true" />
```

## Storage Options

### Session Storage (Default)
```php
// config/cart.php
'storage' => ['driver' => 'session']
```

### Database Storage
```php
// config/cart.php
'storage' => ['driver' => 'database']
```

### Cache Storage
```php
// config/cart.php
'storage' => ['driver' => 'cache', 'ttl' => 3600]
```

## Price Formatting

The package supports automatic price formatting:

### Configuration
```php
// config/cart.php
'formatting' => [
    'enabled' => true,
    'transformer' => 'integer',     // or 'localized'
    'currency' => 'MYR',
    'decimals' => 2,
],
```

### Integer Transformer (Default)
Converts prices to integers for storage (cents) and formats for display:
```php
Cart::add('item', 'Item', 29.99, 1);   // Stored as 2999 cents
echo Cart::subtotal();                  // Displays "29.99"
```

### Localized Transformer
Respects locale formatting:
```php
// For Malaysian locale
echo Cart::total();                     // "RM 1,234.56"
```

## Testing

### Basic Test Setup
```php
use MasyukAI\Cart\Facades\Cart;

class CartTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cart::clear(); // Clear cart before each test
    }

    public function test_can_add_item_to_cart()
    {
        Cart::add('test-item', 'Test Item', 99.99, 1);
        
        $this->assertEquals(1, Cart::countItems());
        $this->assertEquals('99.99', Cart::subtotal());
    }
}
```

### Testing with Conditions
```php
public function test_applies_discount_correctly()
{
    Cart::add('item', 'Item', 100.00, 1);
    Cart::addDiscount('test-discount', '10%');
    
    $this->assertEquals('100.00', Cart::subtotal());
    $this->assertEquals('90.00', Cart::total());
}
```

## Best Practices

### 1. Always Use Instance Management
```php
// In controllers or middleware
$instanceKey = auth()->check() 
    ? 'user_' . auth()->id() 
    : 'guest_' . session()->getId();
    
Cart::instance($instanceKey);
```

### 2. Handle Cart Migration on Login
```php
// In your authentication logic
public function handleUserLogin(User $user)
{
    $guestInstance = 'guest_' . session()->getId();
    $userInstance = 'user_' . $user->id;
    
    if (!Cart::instance($guestInstance)->isEmpty()) {
        app(CartMigrationService::class)->migrateCart(
            $guestInstance, 
            $userInstance, 
            'add_quantities'
        );
    }
    
    Cart::instance($userInstance);
}
```

### 3. Use Events for Analytics
```php
Event::listen(ItemAdded::class, function ($event) {
    Analytics::track('cart_item_added', [
        'item_id' => $event->item->id,
        'item_name' => $event->item->name,
        'price' => $event->item->price,
        'quantity' => $event->item->quantity,
    ]);
});
```

### 4. Validate Items Before Adding
```php
public function addToCart(Request $request)
{
    $request->validate([
        'product_id' => 'required|exists:products,id',
        'quantity' => 'required|integer|min:1|max:10',
    ]);

    $product = Product::findOrFail($request->product_id);
    
    // Check stock availability
    if ($product->stock < $request->quantity) {
        throw ValidationException::withMessages([
            'quantity' => 'Insufficient stock available.'
        ]);
    }

    Cart::add(
        $product->id,
        $product->name,
        $product->price,
        $request->quantity,
        ['sku' => $product->sku, 'image' => $product->image]
    );
}
```

### 5. Use Raw Methods for Internal Calculations
```php
// ❌ Don't use formatted methods for calculations
$tax = Cart::total() * 0.06;  // Will fail if total() returns "1,234.56"

// ✅ Use raw methods for calculations
$tax = Cart::getRawTotal() * 0.06;  // Correct: uses float value
```

## Common Use Cases

### E-commerce with Tax and Shipping
```php
// Add products
Cart::add('shirt', 'Cotton Shirt', 29.99, 2);
Cart::add('pants', 'Denim Pants', 79.99, 1);

// Apply business rules
Cart::addTax('gst', '6%');
Cart::addFee('shipping', '12.00');

// Apply customer discount
if (auth()->user()->isVip()) {
    Cart::addDiscount('vip-discount', '15%');
}

// Get totals
$subtotal = Cart::subtotal();           // "139.97"
$total = Cart::total();                 // Final amount with all conditions
```

### Multi-vendor Marketplace
```php
$vendors = ['vendor_a', 'vendor_b'];

foreach ($vendors as $vendorId) {
    Cart::instance("vendor_{$vendorId}");
    
    // Add vendor-specific items
    Cart::add("product_{$vendorId}", "Vendor Product", 99.99, 1);
    
    // Apply vendor-specific conditions
    Cart::addFee('vendor_fee', VendorService::getFee($vendorId));
}

// Calculate grand total
$grandTotal = 0;
foreach ($vendors as $vendorId) {
    $grandTotal += Cart::instance("vendor_{$vendorId}")->getRawTotal();
}
```

This package provides a robust, modern cart solution that integrates seamlessly with Laravel applications. Use it for any e-commerce or shopping cart functionality in this application.
