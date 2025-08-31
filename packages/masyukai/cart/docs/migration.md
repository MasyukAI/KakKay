# Migration Guide

Migrate from other cart packages to MasyukAI Cart with ease.

## From Laravel Shopping Cart (darryldecode)

The most common migration scenario. MasyukAI Cart provides compatibility while offering enhanced features.

### API Compatibility

Most methods work without changes:

```php
// ✅ These work exactly the same
Cart::add('id', 'name', 100, 1);
Cart::content();
Cart::count();
Cart::total();
Cart::clear();
Cart::remove('id');
```

### Updated Method Names

Some methods have been enhanced with aliases:

```php
// Old way (still works)           // New enhanced way
Cart::getContent();               Cart::content();
Cart::getTotal();                 Cart::total();
Cart::getSubTotal();              Cart::subtotal();
Cart::getTotalQuantity();         Cart::count(); // Now returns total quantity

// Both APIs are supported for seamless migration
```

### Conditions System

Enhanced condition system with backward compatibility:

```php
// Old way (still works)
$condition = new CartCondition([
    'name' => 'tax',
    'type' => 'tax',
    'target' => 'subtotal',
    'value' => '+10%'
]);

// New enhanced way
$condition = new CartCondition('tax', 'tax', 'subtotal', '+10%');

// Simplified helpers (new)
Cart::addTax('vat', '20%');
Cart::addDiscount('sale', '15%');
Cart::addFee('shipping', '9.99');
```

### Storage Configuration

Enhanced storage options:

```php
// Old config
'storage' => 'session',

// New enhanced config
'storage' => [
    'driver' => 'session', // session, database, cache
    'database' => [
        'connection' => null,
        'table' => 'cart_storage',
    ],
    'cache' => [
        'store' => null,
        'prefix' => 'cart',
    ],
],
```

### Migration Steps

1. **Install MasyukAI Cart**:
   ```bash
   composer require masyukai/cart
   ```

2. **Update Config** (if customized):
   ```bash
   php artisan vendor:publish --tag=cart-config
   # Merge your old configuration
   ```

3. **Update Imports**:
   ```php
   // Change namespace
   use Darryldecode\Cart\CartServiceProvider;
   use MasyukAI\Cart\CartServiceProvider;
   
   use Darryldecode\Cart\Facades\CartFacade;
   use MasyukAI\Cart\Facades\Cart;
   ```

4. **Test Existing Code** - Most should work without changes!

5. **Gradually Adopt New Features**:
   ```php
   // Add enhanced search
   $expensiveItems = Cart::search(fn($item) => $item->price > 100);
   
   // Use simplified conditions
   Cart::addDiscount('holiday', '25%');
   
   // Leverage Livewire components
   <livewire:cart-summary />
   ```

## From Crinsane/LaravelShoppingcart

Similar API with some differences:

### Method Mapping

```php
// Crinsane                      // MasyukAI Cart
Cart::add(['id' => '1', ...]);  Cart::add('1', 'name', 100, 1);
Cart::get('id');                Cart::get('id');
Cart::update('id', 'qty', 2);   Cart::update('id', ['quantity' => 2]);
Cart::content();                Cart::content();
Cart::destroy();                Cart::clear();
```

### Quantity Updates

```php
// Crinsane (absolute)
Cart::update('id', 'qty', 3);

// MasyukAI (relative by default)
Cart::update('id', ['quantity' => 1]); // Adds 1 to current

// MasyukAI (absolute)  
Cart::update('id', ['quantity' => ['value' => 3]]); // Sets to exactly 3
```

### Tax and Discounts

```php
// Crinsane
Cart::setGlobalDiscount(10);
Cart::setGlobalTax(5);

// MasyukAI
Cart::addDiscount('discount', '10%');
Cart::addTax('tax', '5%');
```

## From Vanilo Cart

Framework-agnostic to Laravel-specific:

### Basic Operations

```php
// Vanilo
$cart->addItem($product, 1);
$cart->removeItem($item);
$cart->clear();

// MasyukAI
Cart::add($product->id, $product->name, $product->price, 1);
Cart::remove($product->id);
Cart::clear();
```

### Events

```php
// Vanilo
$cart->addItem($product, 1); // Manual event dispatching

// MasyukAI (automatic events)
Cart::add($product->id, $product->name, $product->price, 1);
// Automatically fires ItemAdded event
```

## From Gloudemans/Shoppingcart

Very similar API:

### Direct Replacement

```php
// Gloudemans → MasyukAI (mostly identical)
Cart::add('293ad', 'Product 1', 1, 9.99);
Cart::update('293ad', 2);
Cart::remove('293ad');
Cart::destroy();  // Use Cart::clear() instead
```

### Row ID Handling

```php
// Gloudemans uses generated row IDs
$rowId = Cart::add('293ad', 'Product 1', 1, 9.99);

// MasyukAI uses your provided IDs
$item = Cart::add('293ad', 'Product 1', 9.99, 1);
$itemId = $item->id; // '293ad'
```

## General Migration Tips

### 1. Test in Stages

```php
// Create a test route to verify functionality
Route::get('/test-cart', function() {
    Cart::add('test-1', 'Test Product', 99.99, 1);
    
    return [
        'content' => Cart::content(),
        'total' => Cart::total(),
        'count' => Cart::count(),
    ];
});
```

### 2. Backup Before Migration

```bash
# Backup your database
mysqldump -u user -p database > cart_backup.sql

# Backup your config files
cp -r config/ config_backup/
```

### 3. Use Feature Flags

```php
// Gradually enable new features
if (config('cart.enhanced_features')) {
    Cart::addDiscount('sale', '20%');
} else {
    // Old condition system
}
```

### 4. Run Comprehensive Tests

```php
// Test all critical cart operations
public function test_cart_migration()
{
    // Add items
    Cart::add('test-1', 'Product 1', 100, 2);
    Cart::add('test-2', 'Product 2', 50, 1);
    
    // Test calculations
    $this->assertEquals(250, Cart::total());
    $this->assertEquals(3, Cart::count());
    $this->assertEquals(2, Cart::countItems());
    
    // Test updates
    Cart::update('test-1', ['quantity' => 1]);
    $this->assertEquals(150, Cart::total());
    
    // Test removal
    Cart::remove('test-2');
    $this->assertEquals(100, Cart::total());
    
    // Test clear
    Cart::clear();
    $this->assertTrue(Cart::isEmpty());
}
```

## New Features to Explore

After successful migration, explore these enhanced features:

### 1. Advanced Search

```php
$results = Cart::search(function($item) {
    return $item->getAttribute('category') === 'electronics'
        && $item->price > 500;
});
```

### 2. Multiple Instances

```php
$wishlist = Cart::instance('wishlist');
$comparison = Cart::instance('comparison');
```

### 3. Livewire Components

```blade
<livewire:add-to-cart product-id="123" />
<livewire:cart-summary />
```

### 4. Enhanced Events

```php
Event::listen(ItemAdded::class, function($event) {
    // Custom logic when items are added
});
```

### 5. Better Condition System

```php
// Complex conditions with attributes
$condition = new CartCondition(
    'bulk-discount',
    'discount', 
    'price',
    '-15%',
    ['min_quantity' => 10]
);
```

## Support During Migration

Need help migrating?

- 📖 **[Complete API Reference](api-reference.md)**
- 🐛 **[Report Issues](../../issues)**
- 💬 **[Ask Questions](../../discussions)**
- 📧 **Email**: support@masyukai.com

We're here to help make your migration smooth! 🚀
