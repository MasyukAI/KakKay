# Recipes & Examples

Copy-ready snippets for common cart scenarios.

## P    return response()->json([
        'message' => 'Your cart was updated elsewhere. Please refresh.',
        'suggestions' => $e->getResolutionSuggestions(),
    ], 409);
}
```

## Track Business Metrics

Query your data directly for accurate analytics:

```php
// Conversion rate
$totalCarts = DB::table('carts')->count();
$completedOrders = Order::whereNotNull('completed_at')->count();
$conversionRate = ($completedOrders / $totalCarts) * 100;

// Abandoned carts
$abandonedCarts = DB::table('carts')
    ->where('updated_at', '<', now()->subDays(7))
    ->whereDoesntHave('orders')
    ->count();
```

## Clear Abandoned Carts Nightly

Schedule the artisan command via Laravel's scheduler:ipping Method

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

## Handle Conflicts Gracefully

```php
use MasyukAI\Cart\Exceptions\CartConflictException;

try {
    Cart::update($request->item_id, ['quantity' => $request->quantity]);
} catch (CartConflictException $e) {
    return response()->json([
        'message' => 'Your cart was updated elsewhere. Please refresh.',
        'suggestions' => $e->getResolutionSuggestions(),
    ], 409);
}
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
