# Cart Operations

The `MasyukAI\Cart\Cart` object (available through the `Cart` facade) orchestrates items, totals, metadata, and instances. This guide covers the daily API surface you’ll reach for most.

## Working with Items

### Adding Items

```php
Cart::add(
    id: 'sku-123',
    name: 'Laptop Stand',
    price: 59.99,
    quantity: 2,
    attributes: [
        'material' => 'aluminium',
        'color' => 'space-gray',
    ],
    conditions: null,
    associatedModel: App\Models\Product::class,
);
```

- `id` is stored as a string and must be unique per instance.
- `price` may be `int`, `float`, or a sanitized numeric string (symbols and spaces are stripped).
- `attributes` become an `Illuminate\Support\Collection` for quick lookups.
- `associatedModel` may be an Eloquent class name or instance; it’s stored as metadata and restored lazily.

### Adding Multiple Items

Pass an array of item payloads:

```php
Cart::add([
    ['id' => 'sku-1', 'name' => 'Notebook', 'price' => 7.50, 'quantity' => 3],
    ['id' => 'sku-2', 'name' => 'Pen Set', 'price' => '12.00', 'quantity' => 1],
]);
```

### Updating Items

```php
Cart::update('sku-1', [
    'quantity' => ['value' => 5], // absolute quantity
    'price' => 6.95,
    'attributes' => ['color' => 'navy'],
]);

Cart::update('sku-2', [
    'quantity' => 1, // relative, adds 1 to the current quantity
]);
```

If an update drives quantity ≤ 0, the item is removed automatically.

### Removing Items

```php
Cart::remove('sku-2');     // Removes the line item
Cart::clear();             // Clears the entire instance
```

`Cart::isEmpty()` reports when an instance no longer holds items.

## Totals & Quantities

| Method | Description |
| --- | --- |
| `Cart::subtotal()` | Money with item-level and subtotal-level conditions applied. |
| `Cart::total()` | Money with all conditions (item, subtotal, total). |
| `Cart::subtotalWithoutConditions()` | Raw sum of base prices. |
| `Cart::totalWithoutConditions()` | Alias of `subtotalWithoutConditions()`; totals are recalculated lazily. |
| `Cart::savings()` | Money representing the positive delta between raw subtotal and total. |
| `Cart::count()` | Total quantity across all items. |
| `Cart::countItems()` | Unique line items count. |

Internally these methods return `Money` objects. Use `->getAmount()` for numeric comparisons and `->format()` for localized strings.

## Inspecting Content

```php
Cart::get('sku-123');                 // MasyukAI\Cart\Models\CartItem|null
Cart::has('sku-123');                 // bool
Cart::getItems();                     // CartCollection, extends Illuminate\Support\Collection
Cart::content();                      // Structured array (items, conditions, totals, etc.)
Cart::toArray();                      // Alias of content()
```

The `CartCollection` adds convenience helpers:

```php
Cart::getItems()
    ->filterByAttribute('color', 'space-gray')
    ->sortByPrice('desc')
    ->getStatistics();
```

See [CartCollection](api-reference.md#cartcollection) for the full helper list.

## Metadata

Metadata pairs contextual state with a cart instance (e.g., selected shipping method).

```php
Cart::setMetadata('shipping_method', 'express');
Cart::setMetadataBatch([
    'coupon_code' => 'SPRING24',
    'notes' => 'Deliver after 6pm',
]);

Cart::getMetadata('coupon_code');           // "SPRING24"
Cart::hasMetadata('shipping_method');       // true
Cart::removeMetadata('notes');              // nullifies the key
```

Metadata persists through the configured storage driver and triggers events (see [Events](events.md)).

## Instances

Instances let a single identifier (user/session) keep multiple carts side-by-side (e.g., `default`, `wishlist`, `quote`).

```php
Cart::setInstance('wishlist');              // Switch the facade globally for this request
Cart::add('dream-camera', 'Mirrorless', 1999.00);

$quotes = Cart::getCartInstance('quotes'); // Detached cart instance without flipping globals
$quotes->add('proposal-1', 'Annual plan', 499.00);
```

Use `Cart::getCurrentCart()` to operate on the active instance object and avoid surprises when using dependency injection.

## Searching & Filtering

```php
$premium = Cart::search(fn ($item) => $item->price > 1000);

Cart::getItems()
    ->filterByConditionType('discount')
    ->groupByAttribute('vendor');
```

Search callbacks receive `MasyukAI\Cart\Models\CartItem` objects, enabling access to conditions and attributes.

## Associating Domain Models

Item payloads accept an Eloquent model class or instance via `associatedModel`. When stored, the cart retains the class name. You can recover the class name through `CartItem::getAssociatedModel()` and resolve it manually or with your own repository.

> The cart does **not** automatically rehydrate full Eloquent models on read; this keeps storage drivers lean and deterministic.

## Error Handling & Validation

- Missing `id`, `name`, or invalid pricing/quantity throw `InvalidCartItemException`.
- Unknown model classes trigger `UnknownModelException`.
- Supply `config('cart.strict_validation', true)` to keep validation active in production.

For a complete list of exceptions, see [API Reference](api-reference.md#exceptions).
