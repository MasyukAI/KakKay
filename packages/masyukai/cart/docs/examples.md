# Recipes & Examples

Copy-ready snippets for common cart scenarios.

## Persist a Selected Shipping Method

```php
// Controller
Cart::removeShipping();
Cart::addShipping(
    name: 'Express',
    value: '25.00',
    method: 'express',
    attributes: ['eta' => '1-2 days']
);

Cart::setMetadata('shipping_method', 'express');
```

Retrieve the selection later:

```php
$method = Cart::getMetadata('shipping_method', 'standard');
$shipping = Cart::getShipping();
```

## Build a Wishlist Instance

```php
Cart::setInstance('wishlist');

collect($request->wishlist_items)->each(function ($product) {
    Cart::add($product['id'], $product['name'], $product['price']);
});

return Cart::content();
```

## Custom Condition for Tiered Discounts

```php
$condition = new CartCondition(
    'loyalty-tier',
    'discount',
    'subtotal',
    '-10%',
    attributes: ['tier' => 'gold'],
    rules: [
        fn ($cart) => $user->isGoldMember(),
        fn ($cart) => $cart->getRawSubtotalWithoutConditions() >= 5000,
    ],
);

Cart::getCurrentCart()->registerDynamicCondition($condition);
```

## Swap Guest Cart to User After Checkout

```php
app(CartMigrationService::class)->swapGuestCartToUser(
    userId: $order->user_id,
    instance: 'default',
    guestSessionId: $order->session_id,
);
```

## Wrap High-Value Operations with Retry

```php
app(CartRetryService::class)->executeWithSmartRetry(function () use ($request) {
    Cart::update($request->item_id, ['quantity' => $request->quantity]);
});
```

## Record Conversion with Context

```php
Cart::recordConversion([
    'order_id' => $order->id,
    'value' => $order->total,
    'channel' => 'web',
]);
```

## Clear Abandoned Carts Nightly

Schedule the artisan command via Laravel’s scheduler:

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule): void
{
    $schedule->command('cart:clear-abandoned --days=14')->dailyAt('02:30');
}
```

## Test Cart Merge Logic

```php
it('merges guest cart into user cart', function () {
    $guestSession = Str::uuid()->toString();

    Cart::setInstance('default');
    Cart::storage()->putItems($guestSession, 'default', [
        'sku-1' => ['id' => 'sku-1', 'name' => 'Shirt', 'price' => 20, 'quantity' => 1],
    ]);

    $migrator = app(CartMigrationService::class);
    $migrator->migrateGuestCartToUser(user()->id, 'default', $guestSession);

    expect(Cart::countItems())->toBe(1);
});
```
