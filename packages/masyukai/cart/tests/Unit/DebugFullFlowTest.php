<?php

declare(strict_types=1);

use MasyukAI\Cart\Cart;
use MasyukAI\Cart\PriceTransformers\IntegerPriceTransformer;
use MasyukAI\Cart\Storage\SessionStorage;
use MasyukAI\Cart\Support\CartMoney;

it('debug full flow with integer transformer (config-independent)', function () {
    // Ensure formatting is disabled to get raw numeric values
    CartMoney::disableFormatting();
    CartMoney::resetFormatting();

    // Create explicit integer transformer (no config dependency)
    $transformer = new IntegerPriceTransformer('USD', 'en_US', 2);

    // Test direct transformer behavior
    $stored = $transformer->toStorage(19.99);
    expect($stored)->toBe(1999);

    // Test CartMoney behavior with storage values
    $money = CartMoney::fromCents($stored, 'USD');
    expect($money->getAmount())->toBe(19.99);
    expect($money->getCents())->toBe(1999);

    // Test storage conversion
    $storageValue = CartMoney::toStorage(19.99);
    expect($storageValue)->toBe(1999);

    // Create cart with explicit dependencies (no config)
    $session = app('session')->driver();
    $storage = new SessionStorage($session, 'debug_test');
    $cart = new Cart(
        storage: $storage,
        events: new \Illuminate\Events\Dispatcher,
        instanceName: 'debug_test',
        eventsEnabled: false
    );

    // Test cart operations
    $cart->add('item-1', 'Test Item', 19.99, 1);
    $item = $cart->get('item-1');

    // Cart stores the price as given (no automatic transformation)
    expect($item->price)->toBe(19.99);
    // With formatting disabled, cart should return float values
    expect($cart->subtotal()->getAmount())->toBe(19.99);

    // But we can transform manually using CartMoney
    $itemPriceInCents = CartMoney::toStorage($item->price);
    expect($itemPriceInCents)->toBe(1999);

    $subtotalInCents = CartMoney::toStorage($cart->subtotal()->getAmount());
    expect($subtotalInCents)->toBe(1999);

    // And convert back using CartMoney
    $itemMoney = CartMoney::fromCents($itemPriceInCents, 'USD');
    $subtotalMoney = CartMoney::fromCents($subtotalInCents, 'USD');
    expect($itemMoney->getAmount())->toBe(19.99);
    expect($subtotalMoney->getAmount())->toBe(19.99);

    // This demonstrates that the CartMoney works correctly
    // for cent-based storage systems, even though the cart itself
    // stores prices in their input format
});
