# Testing

Comprehensive testing guide for the MasyukAI Cart package using PestPHP 4.

## Test Environment Setup

The package comes with a complete test suite using modern PestPHP 4 features. 

### Running Tests

```bash
# Run all tests
./vendor/bin/pest

# Run specific test file
./vendor/bin/pest tests/Unit/CartTest.php

# Run with coverage (requires Xdebug)
./vendor/bin/pest --coverage

# Run with parallel execution
./vendor/bin/pest --parallel

# Run with detailed output
./vendor/bin/pest --verbose
```

### Test Structure

The package includes comprehensive tests:

```
tests/
├── Pest.php                           # Pest configuration
├── TestCase.php                       # Base test case
├── Unit/
│   ├── CartTest.php                   # Core cart functionality
│   ├── CartConditionTest.php          # Condition system
│   └── CartConditionCollectionTest.php # Condition collections
└── Feature/                           # Integration tests
    └── (Future feature tests)
```

## Writing Tests for Your Cart Implementation

### Testing Cart Operations

```php
<?php

use MasyukAI\Cart\Facades\Cart;
use MasyukAI\Cart\Conditions\CartCondition;

describe('Shopping Cart', function () {
    beforeEach(function () {
        Cart::clear();
    });

    it('can add items to cart', function () {
        Cart::add('product-1', 'Test Product', 99.99, 2);
        
        expect(Cart::count())->toBe(1)
            ->and(Cart::getTotalQuantity())->toBe(2)
            ->and(Cart::subtotal())->toBe(199.98);
    });

    it('can update item quantities', function () {
        Cart::add('product-1', 'Test Product', 99.99, 1);
        Cart::update('product-1', ['quantity' => 3]);
        
        $item = Cart::get('product-1');
        expect($item->quantity)->toBe(3)
            ->and(Cart::subtotal())->toBe(299.97);
    });

    it('applies conditions correctly', function () {
        Cart::add('product-1', 'Test Product', 100.00, 2);
        
        $tax = new CartCondition('tax', 'tax', 'subtotal', '+10%');
        Cart::addCondition($tax);
        
        expect(Cart::subtotal())->toBe(200.00)
            ->and(Cart::total())->toBe(220.00);
    });
});
```

### Testing with Events

```php
use Illuminate\Support\Facades\Event;
use MasyukAI\Cart\Events\ItemAdded;

it('dispatches events when adding items', function () {
    Event::fake();
    
    Cart::add('product-1', 'Test Product', 99.99);
    
    Event::assertDispatched(ItemAdded::class, function ($event) {
        return $event->item->id === 'product-1' 
            && $event->item->name === 'Test Product';
    });
});
```

### Testing Cart Conditions

```php
use MasyukAI\Cart\Conditions\CartCondition;

describe('Cart Conditions', function () {
    it('applies percentage discounts correctly', function () {
        $condition = new CartCondition('discount', 'discount', 'subtotal', '-10%');
        
        expect($condition->apply(100.0))->toBe(90.0);
    });

    it('applies fixed amount charges correctly', function () {
        $condition = new CartCondition('shipping', 'shipping', 'subtotal', '+15');
        
        expect($condition->apply(100.0))->toBe(115.0);
    });

    it('validates condition targets', function () {
        expect(fn() => new CartCondition('test', 'test', 'invalid', '+10%'))
            ->toThrow(InvalidCartConditionException::class);
    });
});
```

### Testing Multiple Cart Instances

```php
it('maintains separate cart instances', function () {
    $mainCart = Cart::instance('default');
    $wishlist = Cart::instance('wishlist');
    
    $mainCart->add('product-1', 'Main Product', 99.99);
    $wishlist->add('product-2', 'Wish Product', 149.99);
    
    expect($mainCart->count())->toBe(1)
        ->and($wishlist->count())->toBe(1)
        ->and($mainCart->get('product-2'))->toBeNull()
        ->and($wishlist->get('product-1'))->toBeNull();
});
```

## Testing with Different Storage Drivers

### Session Storage Tests

```php
use MasyukAI\Cart\Storage\SessionStorage;

it('persists cart data in session', function () {
    $storage = new SessionStorage(session());
    $cart = new Cart($storage);
    
    $cart->add('product-1', 'Test Product', 99.99);
    
    // Create new cart instance with same storage
    $newCart = new Cart($storage);
    
    expect($newCart->get('product-1'))->not->toBeNull()
        ->and($newCart->get('product-1')->name)->toBe('Test Product');
});
```

### Database Storage Tests

```php
use MasyukAI\Cart\Storage\DatabaseStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('persists cart data in database', function () {
    $storage = new DatabaseStorage(DB::connection(), 'cart_storage');
    $cart = new Cart($storage, instanceName: 'test');
    
    $cart->add('product-1', 'Test Product', 99.99);
    
    // Verify database storage
    expect(DB::table('cart_storage')->where('key', 'cart.test')->exists())
        ->toBeTrue();
    
    // Create new cart instance
    $newCart = new Cart($storage, instanceName: 'test');
    
    expect($newCart->get('product-1'))->not->toBeNull();
});
```

### Cache Storage Tests

```php
use MasyukAI\Cart\Storage\CacheStorage;
use Illuminate\Support\Facades\Cache;

it('persists cart data in cache', function () {
    Cache::flush();
    
    $storage = new CacheStorage(Cache::store(), 'cart_test', 3600);
    $cart = new Cart($storage, instanceName: 'test');
    
    $cart->add('product-1', 'Test Product', 99.99);
    
    // Verify cache storage
    expect(Cache::has('cart_test.cart.test'))->toBeTrue();
    
    // Create new cart instance
    $newCart = new Cart($storage, instanceName: 'test');
    
    expect($newCart->get('product-1'))->not->toBeNull();
});
```

## Testing Cart Models and Associations

```php
use App\Models\Product;

it('works with associated models', function () {
    $product = Product::factory()->create([
        'name' => 'Test Product',
        'price' => 99.99
    ]);
    
    Cart::add($product->id, $product->name, $product->price, 1, [], null, $product);
    
    $item = Cart::get($product->id);
    
    expect($item->associatedModel)->toBeInstanceOf(Product::class)
        ->and($item->associatedModel->id)->toBe($product->id)
        ->and($item->isAssociatedWith(Product::class))->toBeTrue();
});
```

## Testing Validation and Error Handling

```php
use MasyukAI\Cart\Exceptions\InvalidCartItemException;

describe('Cart Validation', function () {
    it('throws exception for invalid price', function () {
        expect(fn() => Cart::add('product-1', 'Test', -10.0, 1))
            ->toThrow(InvalidCartItemException::class, 'Price must be greater than zero');
    });

    it('throws exception for invalid quantity', function () {
        expect(fn() => Cart::add('product-1', 'Test', 10.0, 0))
            ->toThrow(InvalidCartItemException::class, 'Quantity must be greater than zero');
    });

    it('throws exception for empty name', function () {
        expect(fn() => Cart::add('product-1', '', 10.0, 1))
            ->toThrow(InvalidCartItemException::class, 'Name cannot be empty');
    });
});
```

## Performance Testing

```php
it('handles large number of items efficiently', function () {
    $startTime = microtime(true);
    
    // Add 1000 items
    for ($i = 1; $i <= 1000; $i++) {
        Cart::add("product-{$i}", "Product {$i}", rand(10, 100), rand(1, 5));
    }
    
    $addTime = microtime(true) - $startTime;
    
    // Calculate total
    $startTime = microtime(true);
    $total = Cart::total();
    $calculateTime = microtime(true) - $startTime;
    
    expect($addTime)->toBeLessThan(1.0) // Should add 1000 items in under 1 second
        ->and($calculateTime)->toBeLessThan(0.1) // Should calculate total in under 100ms
        ->and(Cart::count())->toBe(1000);
});
```

## Testing Custom Event Listeners

```php
use App\Listeners\UpdateInventory;
use MasyukAI\Cart\Events\ItemAdded;

it('updates inventory when item is added', function () {
    $product = Product::factory()->create(['stock' => 10]);
    
    // Mock the listener
    $listener = new UpdateInventory();
    
    // Create event
    Cart::add($product->id, $product->name, $product->price, 3, [], null, $product);
    $item = Cart::get($product->id);
    $event = new ItemAdded(Cart::instance(), $item, 'default');
    
    // Handle event
    $listener->handle($event);
    
    // Verify inventory was updated
    expect($product->fresh()->stock)->toBe(7);
});
```

## Browser Testing with PestPHP 4

PestPHP 4 includes browser testing capabilities:

```php
use function Pest\Laravel\{visit, assertSee};

it('can add items via browser', function () {
    visit('/products/1')
        ->click('Add to Cart')
        ->assertSee('Item added to cart')
        ->visit('/cart')
        ->assertSee('1 item in cart');
});

it('updates cart totals in real-time', function () {
    // Add item via browser
    visit('/products/1')
        ->click('Add to Cart')
        ->visit('/cart')
        ->assertSee('$99.99')
        ->fill('quantity', '2')
        ->click('Update')
        ->assertSee('$199.98');
});
```

## Mocking and Faking

```php
use Illuminate\Support\Facades\Event;

it('can mock cart events for testing', function () {
    Event::fake([ItemAdded::class]);
    
    Cart::add('product-1', 'Test Product', 99.99);
    
    Event::assertDispatched(ItemAdded::class);
    Event::assertNotDispatched(ItemRemoved::class);
});
```

## Test Data Factories

Create factories for cart testing:

```php
// tests/Factories/CartItemFactory.php
class CartItemFactory
{
    public static function make(array $attributes = []): array
    {
        return array_merge([
            'id' => 'product-' . rand(1, 1000),
            'name' => 'Test Product',
            'price' => rand(10, 100),
            'quantity' => rand(1, 5),
            'attributes' => [],
            'conditions' => null,
            'associatedModel' => null,
        ], $attributes);
    }
}

// Usage in tests
it('creates items from factory', function () {
    $itemData = CartItemFactory::make(['name' => 'Custom Product']);
    
    Cart::add(...array_values($itemData));
    
    expect(Cart::get($itemData['id'])->name)->toBe('Custom Product');
});
```

## Database Testing

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Run migrations
    $this->artisan('migrate');
});

it('persists cart data across requests', function () {
    // First request - add item
    Cart::add('product-1', 'Test Product', 99.99);
    
    // Simulate new request by clearing in-memory cache
    Cart::setInstance('default');
    
    // Verify item still exists
    expect(Cart::get('product-1'))->not->toBeNull();
});
```

## Best Practices

1. **Use descriptive test names**: Make test purpose clear
2. **Test edge cases**: Invalid inputs, empty carts, large quantities
3. **Test all storage drivers**: Ensure compatibility across storage methods
4. **Mock external dependencies**: Use fakes for events, cache, database
5. **Test performance**: Verify cart operations are efficient
6. **Use factories**: Create reusable test data generators
7. **Test error conditions**: Verify proper exception handling
8. **Test browser interactions**: Use PestPHP 4 browser testing for UI

## Running Tests in CI/CD

```yaml
# .github/workflows/tests.yml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v2
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: 8.4
        extensions: mbstring, pdo_sqlite
        
    - name: Install dependencies
      run: composer install
      
    - name: Run tests
      run: ./vendor/bin/pest
      
    - name: Run tests with coverage
      run: ./vendor/bin/pest --coverage --min=90
```

## Next Steps

- Explore [API Reference](api-reference.md) for complete testing methods
- Learn about [Events](events.md) for testing event-driven functionality  
- Check out [Livewire Integration](livewire.md) for UI testing
