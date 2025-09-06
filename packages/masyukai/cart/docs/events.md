# Cart Events System

The MasyukAI Cart package provides a comprehensive event system that allows you to listen for and respond to various cart operations. Events are dispatched automatically when cart operations occur, enabling you to implement custom business logic, analytics tracking, notifications, and integrations.

## Event Overview

The cart package dispatches the following events:

### Core Cart Events
- **`CartCreated`** - When a new cart instance is created
- **`CartUpdated`** - When cart contents or totals change
- **`CartCleared`** - When all items are removed from the cart
- **`CartMerged`** - When two cart instances are merged

### Item Events
- **`ItemAdded`** - When an item is added to the cart
- **`ItemUpdated`** - When an existing item's quantity or attributes change
- **`ItemRemoved`** - When an item is removed from the cart

### Condition Events *(New in v2.0)*
- **`ConditionAdded`** - When a condition (discount, tax, fee) is added
- **`ConditionRemoved`** - When a condition is removed

## Event Configuration

Events are enabled by default but can be controlled through cart configuration:

```php
// Enable/disable events globally
$cart = new Cart(
    storage: $storage,
    events: $eventDispatcher,
    eventsEnabled: true  // Set to false to disable events
);

// Or via facade configuration
Cart::setEventsEnabled(false);
```

## Listening to Events

### Using Laravel Event Listeners

#### 1. Create Event Listeners

```bash
php artisan make:listener TrackCartAnalytics --event="MasyukAI\Cart\Events\ItemAdded"
php artisan make:listener ProcessDiscountUsage --event="MasyukAI\Cart\Events\ConditionAdded"
```

#### 2. Register in EventServiceProvider

```php
// app/Providers/EventServiceProvider.php
use MasyukAI\Cart\Events\ItemAdded;
use MasyukAI\Cart\Events\ConditionAdded;
use MasyukAI\Cart\Events\ConditionRemoved;

protected $listen = [
    ItemAdded::class => [
        TrackCartAnalytics::class,
        SendCartAbandonmentEmail::class,
    ],
    ConditionAdded::class => [
        ProcessDiscountUsage::class,
        TrackPromotionalCodes::class,
    ],
    ConditionRemoved::class => [
        LogConditionRemovals::class,
    ],
];
```

#### 3. Implement Listener Logic

```php
// app/Listeners/TrackCartAnalytics.php
class TrackCartAnalytics
{
    public function handle(ItemAdded $event): void
    {
        Analytics::track('cart_item_added', [
            'item_id' => $event->item->id,
            'item_name' => $event->item->name,
            'price' => $event->item->getRawPrice(),
            'quantity' => $event->item->quantity,
            'cart_total' => $event->cart->getRawTotal(),
            'cart_items_count' => $event->cart->countItems(),
        ]);
    }
}
```

### Using Closure-Based Listeners

```php
// app/Providers/AppServiceProvider.php
use Illuminate\Support\Facades\Event;
use MasyukAI\Cart\Events\ConditionAdded;

public function boot(): void
{
    Event::listen(ConditionAdded::class, function (ConditionAdded $event) {
        // Track promotional code usage
        if ($event->condition->getType() === 'discount') {
            Log::info('Discount applied', [
                'code' => $event->condition->getName(),
                'value' => $event->condition->getValue(),
                'impact' => $event->getConditionImpact(),
                'cart_instance' => $event->cart->getStorageInstanceName(),
            ]);
        }
    });
}
```

## Event Details

### ItemAdded Event

Dispatched when an item is added to the cart.

```php
class ItemAdded
{
    public readonly CartItem $item;    // The added item
    public readonly Cart $cart;        // Cart instance
}
```

**Example Usage:**
```php
Event::listen(ItemAdded::class, function (ItemAdded $event) {
    // Send real-time notification
    broadcast(new CartUpdatedEvent($event->cart->toArray()));
    
    // Track inventory
    Inventory::reserve($event->item->id, $event->item->quantity);
    
    // Trigger upsell recommendations
    RecommendationEngine::generateUpsells($event->cart);
});
```

### ConditionAdded Event

Dispatched when a condition is added to the cart or an item.

```php
class ConditionAdded
{
    public readonly CartCondition $condition; // The added condition
    public readonly Cart $cart;               // Cart instance
    public readonly ?string $target;          // Item ID if item-level condition
    
    public function getConditionImpact(): float;  // Monetary impact
    public function isItemCondition(): bool;      // Check if item-level
    public function toArray(): array;             // Event data for logging
}
```

**Example Usage:**
```php
Event::listen(ConditionAdded::class, function (ConditionAdded $event) {
    // Track promotional code usage
    if ($event->condition->getType() === 'discount') {
        PromoCode::incrementUsage($event->condition->getName());
        
        // Alert for high-value discounts
        if (abs($event->getConditionImpact()) > 100) {
            Notification::route('slack', config('alerts.slack_webhook'))
                ->notify(new HighValueDiscountAlert($event));
        }
    }
    
    // Log tax applications for compliance
    if ($event->condition->getType() === 'tax') {
        TaxLog::create([
            'cart_id' => $event->cart->getIdentifier(),
            'jurisdiction' => $event->condition->getAttribute('jurisdiction'),
            'rate' => $event->condition->getValue(),
            'amount' => $event->getConditionImpact(),
        ]);
    }
});
```

### ConditionRemoved Event

Dispatched when a condition is removed from the cart or an item.

```php
class ConditionRemoved
{
    public readonly CartCondition $condition; // The removed condition
    public readonly Cart $cart;               // Cart instance
    public readonly ?string $target;          // Item ID if item-level condition
    public readonly ?string $reason;          // Removal reason
    
    public function getConditionImpact(): float;  // Former monetary impact
    public function getLostSavings(): float;      // Lost savings amount
    public function isItemCondition(): bool;      // Check if item-level
    public function toArray(): array;             // Event data for logging
}
```

**Example Usage:**
```php
Event::listen(ConditionRemoved::class, function (ConditionRemoved $event) {
    // Track promotional code removals
    if ($event->condition->getType() === 'discount') {
        Analytics::track('promo_code_removed', [
            'code' => $event->condition->getName(),
            'reason' => $event->reason ?? 'user_action',
            'savings_lost' => $event->getLostSavings(),
        ]);
        
        // Offer alternative discount
        if ($event->getLostSavings() > 20) {
            AlternativeDiscountService::suggest($event->cart);
        }
    }
});
```

## Advanced Event Usage

### Cart Abandonment Tracking

```php
Event::listen([ItemAdded::class, ItemUpdated::class], function ($event) {
    // Reset abandonment timer when cart is modified
    CartAbandonmentService::resetTimer($event->cart->getIdentifier());
});

Event::listen(CartCleared::class, function (CartCleared $event) {
    // Cancel abandonment tracking when cart is cleared
    CartAbandonmentService::cancelTracking($event->cart->getIdentifier());
});
```

### Real-time Cart Synchronization

```php
Event::listen([
    ItemAdded::class,
    ItemUpdated::class,
    ItemRemoved::class,
    ConditionAdded::class,
    ConditionRemoved::class,
], function ($event) {
    // Broadcast cart updates to connected clients
    broadcast(new CartSyncEvent([
        'cart_id' => $event->cart->getIdentifier(),
        'instance' => $event->cart->getStorageInstanceName(),
        'data' => $event->cart->toArray(),
        'timestamp' => now()->toISOString(),
    ]));
});
```

### Inventory Management

```php
Event::listen(ItemAdded::class, function (ItemAdded $event) {
    InventoryService::reserve($event->item->id, $event->item->quantity);
});

Event::listen(ItemUpdated::class, function (ItemUpdated $event) {
    // Adjust inventory reservations based on quantity changes
    $oldItem = $event->cart->getItems()->get($event->item->id);
    $quantityDifference = $event->item->quantity - $oldItem->quantity;
    
    if ($quantityDifference > 0) {
        InventoryService::reserve($event->item->id, $quantityDifference);
    } else {
        InventoryService::release($event->item->id, abs($quantityDifference));
    }
});

Event::listen(ItemRemoved::class, function (ItemRemoved $event) {
    InventoryService::release($event->item->id, $event->item->quantity);
});
```

### Business Intelligence & Analytics

```php
Event::listen(ConditionAdded::class, function (ConditionAdded $event) {
    if ($event->condition->getType() === 'discount') {
        BusinessIntelligence::record([
            'event_type' => 'discount_applied',
            'discount_code' => $event->condition->getName(),
            'discount_value' => $event->condition->getValue(),
            'impact_amount' => $event->getConditionImpact(),
            'cart_value_before' => $event->cart->getRawSubtotal(),
            'cart_value_after' => $event->cart->getRawTotal(),
            'customer_segment' => CustomerSegmentation::getSegment(auth()->id()),
            'timestamp' => now(),
        ]);
    }
});
```

## Event Testing

### Testing Event Dispatching

```php
// tests/Feature/CartEventsTest.php
use Illuminate\Support\Facades\Event;
use MasyukAI\Cart\Events\ItemAdded;
use MasyukAI\Cart\Events\ConditionAdded;

class CartEventsTest extends TestCase
{
    public function test_item_added_event_is_dispatched(): void
    {
        Event::fake();
        
        Cart::add('product-123', 'Test Product', 99.99, 1);
        
        Event::assertDispatched(ItemAdded::class, function (ItemAdded $event) {
            return $event->item->id === 'product-123'
                && $event->item->name === 'Test Product';
        });
    }
    
    public function test_condition_added_event_includes_impact_data(): void
    {
        Event::fake();
        
        Cart::add('product-123', 'Test Product', 100.00, 1);
        Cart::addDiscount('test-discount', '-20%');
        
        Event::assertDispatched(ConditionAdded::class, function (ConditionAdded $event) {
            return $event->condition->getName() === 'test-discount'
                && $event->getConditionImpact() === -20.00;
        });
    }
}
```

### Testing Event Listeners

```php
// tests/Unit/Listeners/TrackCartAnalyticsTest.php
class TrackCartAnalyticsTest extends TestCase
{
    public function test_tracks_item_added_analytics(): void
    {
        Analytics::shouldReceive('track')
            ->once()
            ->with('cart_item_added', Mockery::subset([
                'item_id' => 'product-123',
                'item_name' => 'Test Product',
            ]));
        
        $listener = new TrackCartAnalytics();
        $event = new ItemAdded($item, $cart);
        
        $listener->handle($event);
    }
}
```

## Best Practices

### 1. Keep Listeners Fast
```php
// Good: Queue heavy operations
Event::listen(ItemAdded::class, function (ItemAdded $event) {
    SendCartAbandonmentEmail::dispatch($event->cart)->delay(now()->addHours(2));
});

// Avoid: Synchronous heavy operations
Event::listen(ItemAdded::class, function (ItemAdded $event) {
    // Don't do this - it slows down cart operations
    EmailService::sendComplexAnalyticsReport($event);
});
```

### 2. Handle Exceptions Gracefully
```php
Event::listen(ConditionAdded::class, function (ConditionAdded $event) {
    try {
        ExternalAnalyticsService::track($event->toArray());
    } catch (Exception $e) {
        Log::warning('Analytics tracking failed', [
            'event' => $event->toArray(),
            'error' => $e->getMessage(),
        ]);
        // Don't rethrow - don't break cart operations
    }
});
```

### 3. Use Event Data Efficiently
```php
Event::listen(ItemAdded::class, function (ItemAdded $event) {
    // Cache commonly used calculations
    $cartData = $event->cart->toArray();
    
    // Batch related operations
    DB::transaction(function () use ($event, $cartData) {
        InventoryService::reserve($event->item->id, $event->item->quantity);
        Analytics::track('item_added', $cartData);
        RecommendationService::updateProfile(auth()->id(), $cartData);
    });
});
```

## Event Troubleshooting

### Common Issues

1. **Events not firing**: Ensure `eventsEnabled` is `true` and event dispatcher is properly configured
2. **Missing event data**: Check that the event instance has access to all required cart/item data
3. **Performance issues**: Move heavy operations to queued jobs
4. **Memory leaks**: Avoid storing large objects in event listeners

### Debug Events

```php
// Log all cart events for debugging
Event::listen('MasyukAI\Cart\Events\*', function (string $eventName, array $data) {
    Log::debug('Cart event fired', [
        'event' => $eventName,
        'data' => $data[0]->toArray() ?? 'No data',
    ]);
});
```

This comprehensive event system provides powerful hooks for integrating cart operations with your application's business logic, analytics, and external services.
