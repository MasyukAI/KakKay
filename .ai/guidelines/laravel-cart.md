# MasyukAI Cart Package Guidelines

## Overview
This application uses the custom `masyukai/cart` package for shopping cart functionality. This is a modern, production-ready cart implementation built specifically for Laravel 12+ with comprehensive Livewire integration, advanced condition system, and excellent test coverage.

**Package Location**: `packages/masyukai/cart/`  
**Namespace**: `MasyukAI\Cart`  
**Facade**: `MasyukAI\Cart\Facades\Cart` (imported as `CartFacade` in app)

## Key Features
- ✅ **Modern Money API**: Precision money handling with `CartMoney` class
- ✅ **Advanced Conditions**: Flexible discount, tax, and fee system
- ✅ **Multiple Storage**: Session, database, and cache storage options
- ✅ **Livewire Integration**: Ready-to-use reactive components
- ✅ **Event System**: Comprehensive cart and item events
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
    'money' => [
        'default_currency' => 'USD',
        'default_precision' => 2,
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

## Application Usage

### Import Pattern
In application code, use the facade with alias:

```php
use MasyukAI\Cart\Facades\Cart as CartFacade;

// All operations use CartFacade::
CartFacade::add(...);
CartFacade::getItems();
CartFacade::total();
```

### Adding Items to Cart

#### Simple Product
```php
CartFacade::add(
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
CartFacade::add([
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
$cartItems = CartFacade::getItems();           // Collection of CartItem objects
$cartArray = CartFacade::getItems()->toArray(); 
$itemCount = CartFacade::countItems();        // Number of unique items
$totalQuantity = CartFacade::getTotalQuantity(); // Total quantity of all items
```

#### Get Specific Item
```php
$item = CartFacade::get('iphone-15-pro');
$itemPrice = $item->money();                 // CartMoney object
$itemTotal = $item->sumMoney();             // CartMoney object (price × quantity)
$itemAmount = $item->money()->getAmount();  // Float value
```

#### Get Cart Totals
```php
// Modern API: Returns CartMoney objects
$subtotal = CartFacade::subtotal();                    // CartMoney object (no conditions)
$total = CartFacade::total();                          // CartMoney object (final total)

// Get numeric values
$subtotalAmount = CartFacade::subtotal()->getAmount(); // Float value
$totalAmount = CartFacade::total()->getAmount();       // Float value

// Formatted display values
$formattedSubtotal = CartFacade::subtotal()->format(); // "$99.99"
$formattedTotal = CartFacade::total()->format();       // "$109.99"
```

### Updating Cart Items

#### Update Item Details
```php
CartFacade::update('iphone-15-pro', [
    'name' => 'iPhone 15 Pro Max',
    'price' => 1199.99,
    'quantity' => 2,
    'attributes' => ['storage' => '512GB']
]);
```

#### Update Quantity Only
```php
// Set absolute quantity
CartFacade::update('iphone-15-pro', ['quantity' => 5]); // Set to exactly 5

// In Livewire components, use this pattern:
$item = CartFacade::get($itemId);
if ($item) {
    CartFacade::update($itemId, ['quantity' => $item->quantity + 1]); // Increment
    CartFacade::update($itemId, ['quantity' => $item->quantity - 1]); // Decrement
}
```

### Removing Items
```php
CartFacade::remove('iphone-15-pro');                  // Remove specific item
CartFacade::clear();                                  // Clear entire cart
CartFacade::isEmpty();                                // Check if cart is empty
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

CartFacade::addCondition($taxCondition);
```

#### Convenience Methods
```php
// Quick discount
CartFacade::addDiscount('welcome-10', '10%');

// Quick tax
CartFacade::addTax('gst', '6%');

// Quick fee
CartFacade::addFee('shipping', '15.00');
```

#### Multiple Conditions with Order
```php
$shipping = new CartCondition('shipping', 'fee', 'subtotal', '+12.00', [], 1);
$discount = new CartCondition('save10', 'discount', 'subtotal', '-10%', [], 2);
$tax = new CartCondition('gst', 'tax', 'subtotal', '6%', [], 3);

CartFacade::addCondition([$shipping, $discount, $tax]);
```

### Item-Level Conditions

#### Add Condition to Specific Item
```php
$saleCondition = new CartCondition('flash-sale', 'discount', 'item', '-25%');
CartFacade::addItemCondition('iphone-15-pro', $saleCondition);
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
    // $event->items (Collection)
    // $event->conditions (Collection) 
    // $event->instance (string)
    // $event->total (CartMoney)
});
```

## Livewire Integration

### Application Components
The application includes these Livewire components:

#### Cart Component (`App\Livewire\Cart`)
```php
use MasyukAI\Cart\Facades\Cart as CartFacade;

class Cart extends Component
{
    public array $cartItems = [];

    public function mount(): void
    {
        $this->loadCartItems();
    }

    public function loadCartItems(): void
    {
        $cartContents = CartFacade::getItems();
        $this->cartItems = $cartContents->map(function ($item) {
            return [
                'id' => (string) $item->id,
                'name' => (string) $item->name,
                'price' => (int) $item->getPrice(),
                'quantity' => (int) $item->quantity,
                'slug' => $item->attributes->get('slug'),
            ];
        })->values()->toArray();
    }

    public function updateQuantity(string $itemId, int $quantity): void
    {
        if ($quantity <= 0) {
            $this->removeItem($itemId);
            return;
        }
        CartFacade::update($itemId, ['quantity' => $quantity]);
        $this->loadCartItems();
        $this->dispatch('product-added-to-cart');
    }

    public function removeItem(string $itemId): void
    {
        CartFacade::remove($itemId);
        $this->loadCartItems();
    }

    public function getSubtotal(): int
    {
        return (int) CartFacade::subtotal();
    }

    public function getTotal(): int
    {
        return $this->getSubtotal() + $this->getShipping();
    }
}
```

#### Cart Counter Component (`App\Livewire\CartCounter`)
```php
use MasyukAI\Cart\Facades\Cart as CartFacade;

class CartCounter extends Component
{
    public int $count = 0;

    public function mount(): void
    {
        $this->updateCartCount();
    }

    #[On('product-added-to-cart')]
    public function updateCartCount(): void
    {
        $this->count = CartFacade::getTotalQuantity();
    }
}
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

## Money Handling

The package uses the modern `CartMoney` class for all money operations:

### Working with CartMoney
```php
// Get money objects
$item = CartFacade::get('product-1');
$price = $item->money();                 // CartMoney object
$total = $item->sumMoney();             // CartMoney object

// Get values
$amount = $price->getAmount();          // Float: 19.99
$cents = $price->getCents();            // Int: 1999
$currency = $price->getCurrency();      // String: "USD"

// Format for display
$formatted = $price->format();          // String: "$19.99"
$simple = $price->formatSimple();       // String: "19.99"
```

### CartMoney Operations
```php
use MasyukAI\Cart\Support\CartMoney;

// Create money objects
$money1 = CartMoney::fromAmount(19.99);
$money2 = CartMoney::fromCents(1999); // Same as above

// Arithmetic
$sum = $money1->add($money2);           // $39.98
$difference = $money1->subtract($money2); // $0.00
$doubled = $money1->multiply(2);        // $39.98
$half = $money1->divide(2);             // $9.995 (exact)

// Comparisons
$money1->equals($money2);               // true
$money1->greaterThan($money2);          // false
$money1->isPositive();                  // true
```

## Testing

### Basic Test Setup
```php
use MasyukAI\Cart\Facades\Cart as CartFacade;

class CartTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        CartFacade::clear(); // Clear cart before each test
    }

    public function test_can_add_item_to_cart()
    {
        CartFacade::add('test-item', 'Test Item', 99.99, 1);
        
                $this->assertEquals(1, CartFacade::countItems());
        $this->assertEquals(99.99, CartFacade::subtotal()->getAmount());
    }

    public function test_can_update_item_quantity()
    {
        CartFacade::add('test-item', 'Test Item', 50.00, 1);
        CartFacade::update('test-item', ['quantity' => 3]);
        
        $item = CartFacade::get('test-item');
        $this->assertEquals(3, $item->quantity);
        $this->assertEquals(150.00, $item->sumMoney()->getAmount());
    }
}
```

## Best Practices

### 1. Always Use Facade Alias
```php
// In components, import with alias
use MasyukAI\Cart\Facades\Cart as CartFacade;

// Use CartFacade:: everywhere in application code
CartFacade::add(...);
CartFacade::getItems();
```

### 2. Handle Cart Migration on Login
```php
// In your authentication logic
public function handleUserLogin(User $user)
{
    $guestInstance = 'guest_' . session()->getId();
    $userInstance = 'user_' . $user->id;
    
    if (!CartFacade::instance($guestInstance)->isEmpty()) {
        app(CartMigrationService::class)->migrateCart(
            $guestInstance, 
            $userInstance, 
            'add_quantities'
        );
    }
    
    CartFacade::instance($userInstance);
}
```

### 3. Use Events for Analytics
```php
Event::listen(ItemAdded::class, function ($event) {
    Analytics::track('cart_item_added', [
        'item_id' => $event->item->id,
        'item_name' => $event->item->name,
        'price' => $event->item->money()->getAmount(),
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

    CartFacade::add(
        $product->id,
        $product->name,
        $product->price,
        $request->quantity,
        ['sku' => $product->sku, 'image' => $product->image]
    );
}
```

### 5. Use Money Methods for Calculations
```php
// ✅ Use CartMoney methods for accurate calculations
$total = CartFacade::total();
$shipping = CartMoney::fromAmount(5.99);
$grandTotal = $total->add($shipping);

// ✅ For display, use format methods
echo $grandTotal->format(); // "$105.99"

// ✅ For storage/API, use getAmount()
$orderData = [
    'total' => $grandTotal->getAmount(), // 105.99
    'currency' => $grandTotal->getCurrency(), // "USD"
];
```

## Migration Guide

If you're updating from older cart implementations:

### From Laravel Cart v2.x
```php
// Old API                          // New API
Cart::content()                  -> CartFacade::getItems()
Cart::count()                    -> CartFacade::getTotalQuantity()
Cart::subtotal()                 -> CartFacade::subtotal()->getAmount()
Cart::total()                    -> CartFacade::total()->getAmount()
$item->price                     -> $item->money()->getAmount()
$item->total                     -> $item->sumMoney()->getAmount()
```

### Price Handling Changes
```php
// Old: Mixed return types
$total = Cart::total(); // Could be string or float

// New: Consistent CartMoney objects
$total = CartFacade::total(); // Always CartMoney
$amount = $total->getAmount(); // Float for calculations
$display = $total->format(); // String for display
```

## Summary

The MasyukAI Cart package provides:

- **Modern API**: Consistent method naming and return types
- **Money Precision**: Accurate money handling with CartMoney
- **Livewire Ready**: Built for modern Laravel applications
- **Flexible Storage**: Session, database, or cache options
- **Rich Conditions**: Complex pricing rules and discounts
- **Event System**: Comprehensive tracking and analytics hooks
- **Testing Ready**: Clean API for unit and feature testing

Use `CartFacade::` throughout your application for cart operations, leverage the `CartMoney` class for precision, and take advantage of the event system for analytics and user experience improvements.
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
