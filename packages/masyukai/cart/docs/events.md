# Events

The MasyukAI Cart package provides a comprehensive event system that allows you to hook into cart operations and execute custom logic.

## Available Events

All events are located in the `MasyukAI\Cart\Events` namespace:

- `CartCreated` - Fired when a new cart instance is created
- `CartUpdated` - Fired when cart contents change
- `CartCleared` - Fired when cart is completely cleared
- `ItemAdded` - Fired when an item is added to the cart
- `ItemUpdated` - Fired when an item is updated
- `ItemRemoved` - Fired when an item is removed from the cart

## Event Data

Each event contains relevant data about the operation:

### CartCreated Event

```php
use MasyukAI\Cart\Events\CartCreated;

// Event properties
$event->cart;           // Cart instance
$event->instanceName;   // Cart instance name
```

### CartUpdated Event

```php
use MasyukAI\Cart\Events\CartUpdated;

// Event properties
$event->cart;           // Cart instance
$event->instanceName;   // Cart instance name
$event->action;         // Action performed ('item_added', 'item_updated', etc.)
```

### CartCleared Event

```php
use MasyukAI\Cart\Events\CartCleared;

// Event properties
$event->cart;           // Cart instance (now empty)
$event->instanceName;   // Cart instance name
$event->previousCount;  // Number of items that were cleared
```

### ItemAdded Event

```php
use MasyukAI\Cart\Events\ItemAdded;

// Event properties
$event->cart;           // Cart instance
$event->item;           // CartItem that was added
$event->instanceName;   // Cart instance name
```

### ItemUpdated Event

```php
use MasyukAI\Cart\Events\ItemUpdated;

// Event properties
$event->cart;           // Cart instance
$event->item;           // Updated CartItem
$event->previousItem;   // Previous state of the item
$event->instanceName;   // Cart instance name
$event->changes;        // Array of changed properties
```

### ItemRemoved Event

```php
use MasyukAI\Cart\Events\ItemRemoved;

// Event properties
$event->cart;           // Cart instance
$event->item;           // CartItem that was removed
$event->instanceName;   // Cart instance name
```

## Listening to Events

### Using Event Listeners

Create event listeners using Artisan:

```bash
php artisan make:listener UpdateInventory --event=ItemAdded
```

```php
<?php

namespace App\Listeners;

use MasyukAI\Cart\Events\ItemAdded;
use App\Models\Product;

class UpdateInventory
{
    public function handle(ItemAdded $event): void
    {
        // Update product inventory when item added to cart
        if ($event->item->associatedModel instanceof Product) {
            $product = $event->item->associatedModel;
            $product->decrement('stock', $event->item->quantity);
        }
    }
}
```

Register the listener in `app/Providers/EventServiceProvider.php`:

```php
protected $listen = [
    \MasyukAI\Cart\Events\ItemAdded::class => [
        \App\Listeners\UpdateInventory::class,
    ],
    \MasyukAI\Cart\Events\ItemRemoved::class => [
        \App\Listeners\RestoreInventory::class,
    ],
];
```

### Using Closures

You can also listen to events using closures:

```php
use Illuminate\Support\Facades\Event;
use MasyukAI\Cart\Events\ItemAdded;

Event::listen(ItemAdded::class, function (ItemAdded $event) {
    logger('Item added to cart', [
        'item_id' => $event->item->id,
        'quantity' => $event->item->quantity,
        'cart_instance' => $event->instanceName,
    ]);
});
```

### In Service Providers

Register event listeners in your service provider's `boot` method:

```php
public function boot(): void
{
    Event::listen(
        \MasyukAI\Cart\Events\CartCleared::class,
        fn($event) => Cache::forget("cart_summary_{$event->instanceName}")
    );
}
```

## Common Use Cases

### Inventory Management

```php
// app/Listeners/InventoryManager.php
use MasyukAI\Cart\Events\{ItemAdded, ItemUpdated, ItemRemoved};

class InventoryManager
{
    public function handleItemAdded(ItemAdded $event): void
    {
        $this->reserveStock($event->item);
    }
    
    public function handleItemUpdated(ItemUpdated $event): void
    {
        $quantityDiff = $event->item->quantity - $event->previousItem->quantity;
        if ($quantityDiff > 0) {
            $this->reserveStock($event->item, $quantityDiff);
        } else {
            $this->releaseStock($event->item, abs($quantityDiff));
        }
    }
    
    public function handleItemRemoved(ItemRemoved $event): void
    {
        $this->releaseStock($event->item);
    }
    
    private function reserveStock($item, $quantity = null): void
    {
        if ($item->associatedModel) {
            $item->associatedModel->decrement('available_stock', $quantity ?? $item->quantity);
        }
    }
    
    private function releaseStock($item, $quantity = null): void
    {
        if ($item->associatedModel) {
            $item->associatedModel->increment('available_stock', $quantity ?? $item->quantity);
        }
    }
}
```

### Analytics and Tracking

```php
use MasyukAI\Cart\Events\ItemAdded;

Event::listen(ItemAdded::class, function (ItemAdded $event) {
    // Track cart additions in analytics
    Analytics::track('cart_item_added', [
        'item_id' => $event->item->id,
        'item_name' => $event->item->name,
        'price' => $event->item->price,
        'quantity' => $event->item->quantity,
        'user_id' => auth()->id(),
        'session_id' => session()->getId(),
    ]);
});
```

### Cart Abandonment Tracking

```php
use MasyukAI\Cart\Events\{ItemAdded, CartCleared};

Event::listen(ItemAdded::class, function (ItemAdded $event) {
    // Schedule cart abandonment email
    if (auth()->check()) {
        CartAbandonmentEmail::dispatch(auth()->user())
            ->delay(now()->addHours(24));
    }
});

Event::listen(CartCleared::class, function (CartCleared $event) {
    // Cancel abandonment email if cart is cleared (purchased)
    if (auth()->check()) {
        // Cancel scheduled job logic here
    }
});
```

### Cache Management

```php
use MasyukAI\Cart\Events\CartUpdated;
use Illuminate\Support\Facades\Cache;

Event::listen(CartUpdated::class, function (CartUpdated $event) {
    // Clear cart-related cache when cart changes
    $userId = auth()->id();
    Cache::forget("cart_summary_{$userId}");
    Cache::forget("cart_recommendations_{$userId}");
});
```

### Real-time Updates

```php
use MasyukAI\Cart\Events\ItemAdded;
use Illuminate\Support\Facades\Broadcast;

Event::listen(ItemAdded::class, function (ItemAdded $event) {
    // Broadcast cart updates to connected users
    if (auth()->check()) {
        Broadcast::channel("cart.{auth()->id()}")
            ->send('CartUpdated', [
                'action' => 'item_added',
                'item' => $event->item->toArray(),
                'cart_count' => $event->cart->count(),
                'cart_total' => $event->cart->getTotal(),
            ]);
    }
});
```

### Automatic Condition Application

```php
use MasyukAI\Cart\Events\CartUpdated;
use MasyukAI\Cart\Conditions\CartCondition;

Event::listen(CartUpdated::class, function (CartUpdated $event) {
    $cart = $event->cart;
    
    // Auto-apply free shipping for orders over $100
    if ($cart->getSubTotal() >= 100 && !$cart->getCondition('free-shipping')) {
        $freeShipping = new CartCondition(
            'free-shipping',
            'shipping',
            'subtotal',
            '-0', // Free
            ['description' => 'Free shipping on orders over $100']
        );
        $cart->condition($freeShipping);
    }
    
    // Remove free shipping if under $100
    if ($cart->getSubTotal() < 100 && $cart->getCondition('free-shipping')) {
        $cart->removeCondition('free-shipping');
    }
});
```

### Notification System

```php
use MasyukAI\Cart\Events\ItemAdded;
use App\Notifications\ItemAddedToCart;

Event::listen(ItemAdded::class, function (ItemAdded $event) {
    if (auth()->check()) {
        // Notify user about cart addition
        auth()->user()->notify(new ItemAddedToCart($event->item));
        
        // Notify admin about high-value additions
        if ($event->item->getPriceSum() > 1000) {
            User::where('role', 'admin')->each(function ($admin) use ($event) {
                $admin->notify(new HighValueCartAddition($event->item, auth()->user()));
            });
        }
    }
});
```

## Disabling Events

You can disable events globally or for specific operations:

### Global Event Disabling

```php
// In configuration
'events' => false,

// Or in .env
CART_EVENTS_ENABLED=false
```

### Runtime Event Disabling

```php
use MasyukAI\Cart\Cart;
use MasyukAI\Cart\Storage\SessionStorage;

// Create cart with events disabled
$cart = new Cart(
    storage: new SessionStorage(session()),
    events: null,
    eventsEnabled: false
);

// Or disable events for specific instance
$cart = Cart::instance('silent');
// Configure this instance to not fire events
```

### Conditional Event Disabling

```php
Event::listen(ItemAdded::class, function (ItemAdded $event) {
    // Only process during business hours
    if (now()->between('09:00', '17:00')) {
        $this->processInventory($event->item);
    }
});
```

## Testing Events

### Testing Event Dispatch

```php
use Illuminate\Support\Facades\Event;
use MasyukAI\Cart\Events\ItemAdded;

it('dispatches item added event', function () {
    Event::fake();
    
    Cart::add('product-1', 'Test Product', 99.99, 1);
    
    Event::assertDispatched(ItemAdded::class, function ($event) {
        return $event->item->id === 'product-1' 
            && $event->item->name === 'Test Product';
    });
});
```

### Testing Event Listeners

```php
use MasyukAI\Cart\Events\ItemAdded;
use App\Listeners\UpdateInventory;

it('updates inventory when item added', function () {
    $product = Product::factory()->create(['stock' => 10]);
    $listener = new UpdateInventory();
    
    $item = new CartItem('test', 'Test', 10.00, 2, [], [], $product);
    $event = new ItemAdded(Cart::instance(), $item, 'default');
    
    $listener->handle($event);
    
    expect($product->fresh()->stock)->toBe(8);
});
```

## Custom Events

You can create custom events that extend the cart functionality:

```php
namespace App\Events;

use MasyukAI\Cart\Cart;
use MasyukAI\Cart\Models\CartItem;

class ItemWishlisted
{
    public function __construct(
        public Cart $cart,
        public CartItem $item,
        public string $instanceName = 'default'
    ) {}
}
```

Dispatch custom events:

```php
use App\Events\ItemWishlisted;

// Custom method to add to wishlist
class CartService
{
    public function addToWishlist(string $id, string $name, float $price): void
    {
        $wishlistCart = Cart::instance('wishlist');
        $item = $wishlistCart->add($id, $name, $price, 1);
        
        // Dispatch custom event
        event(new ItemWishlisted($wishlistCart, $item, 'wishlist'));
    }
}
```

## Best Practices

1. **Keep listeners lightweight**: Don't perform heavy operations in event listeners
2. **Use queued listeners**: For time-consuming operations, use queued event listeners
3. **Handle failures gracefully**: Event listeners should not break cart operations
4. **Test thoroughly**: Always test event listeners with unit tests
5. **Use appropriate events**: Choose the right event for your use case
6. **Consider performance**: Too many listeners can impact performance

## Next Steps

- Learn about [Livewire Integration](livewire.md) for reactive cart UIs
- Explore [Storage](storage.md) for cart persistence
- Check out [Testing](testing.md) for comprehensive test coverage
