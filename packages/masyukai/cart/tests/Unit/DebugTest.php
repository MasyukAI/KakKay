<?php

declare(strict_types=1);

use MasyukAI\Cart\Cart;
use MasyukAI\Cart\Conditions\CartCondition;
use MasyukAI\Cart\Storage\SessionStorage;

it('debug cart total calculation', function () {
    session()->flush();

    $storage = new SessionStorage(app('session.store'));
    $cart = new Cart(
        storage: $storage,
        events: null,
        instanceName: 'debug',
        eventsEnabled: false
    );

    // Add items
    $cart->add('product-1', 'Product 1', 100.00, 1);
    $cart->add('product-2', 'Product 2', 50.00, 2);

    dump('Items count: '.$cart->getItems()->count());
    dump('Cart subtotal: '.$cart->getSubTotal());
    dump('Cart subtotal with conditions: '.$cart->getSubTotalWithConditions());
    dump('Cart total: '.$cart->getTotal());

    // Add condition
    $condition = new CartCondition('tax', 'tax', 'subtotal', '+10%');
    $cart->addCondition($condition);

    dump('--- After adding cart condition ---');
    dump('Items count after condition: '.$cart->getItems()->count());
    dump('Cart conditions count: '.$cart->getConditions()->count());
    dump('Cart subtotal: '.$cart->getSubTotal());
    dump('Cart subtotal with conditions: '.$cart->getSubTotalWithConditions());
    dump('Cart total: '.$cart->getTotal());

    expect($cart->getTotal())->toBe(220.0);
});
