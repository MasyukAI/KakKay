# Events

Events broadcast cart state changes for auditing and downstream workflows. Toggle them globally via `config('cart.events')`.

## Event Subscriber

`MasyukAI\Cart\Listeners\DispatchCartUpdated` subscribes to all item, condition, metadata, and merge events to emit a consolidated `CartUpdated` event.

## Event Catalog

| Event | Fired When | Payload |
| --- | --- | --- |
| `CartCreated` | First item is added to an empty cart instance. | `cart` (`MasyukAI\Cart\Cart`). |
| `ItemAdded` | Item added or merged with an existing line. | `item`, `cart`. |
| `ItemUpdated` | Item quantity, price, name, or attributes change. | `item`, `cart`. |
| `ItemRemoved` | Item removed or quantity drops to zero. | `item`, `cart`. |
| `CartCleared` | Instance cleared via `clear()`. | `cart`. |
| `CartConditionAdded` | Cart-level condition attached. | `condition`, `cart`. |
| `CartConditionRemoved` | Cart-level condition removed. | `condition`, `cart`. |
| `ItemConditionAdded` | Item-level condition added. | `condition`, `cart`, `itemId`. |
| `ItemConditionRemoved` | Item-level condition removed. | `condition`, `cart`, `itemId`. |
| `MetadataAdded` | Metadata key written (including batch writes). | `key`, `value`, `cart`. |
| `MetadataRemoved` | Metadata key removed (value nullified). | `key`, `cart`. |
| `CartMerged` | Guest cart merged into user cart. | `targetCart`, `sourceCart`, `totalItemsMerged`, `mergeStrategy`, `hadConflicts`. |
| `CartUpdated` | Consolidated event dispatched after any of the above state changes. | `cart`. |

## Listening to Events

```php
Event::listen(ItemAdded::class, function (ItemAdded $event) {
    logger()->info('Item added', [
        'cart_id' => $event->cart->getIdentifier(),
        'item_id' => $event->item->id,
    ]);
});
```

Because the cart object is passed by value, access totals immediately to capture the state at event time (`$event->cart->total()`).

## Broadcasting & Queuing

- Events are regular Laravel events; broadcast or queue them like any others.
- Under Octane, enable `cart.octane.queue_events` to offload processing to the queue worker.

## Testing Events

Use Pest’s event fakes to assert emissions:

```php
Event::fake();

Cart::add('sku-1', 'Product', 10);

Event::assertDispatched(ItemAdded::class);
Event::assertDispatched(CartCreated::class);
```

Disable events in tests that focus solely on amounts via `config()->set('cart.events', false);`.
