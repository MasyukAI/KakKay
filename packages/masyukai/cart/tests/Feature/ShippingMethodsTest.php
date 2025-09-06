<?php

declare(strict_types=1);

use MasyukAI\Cart\Cart;
use MasyukAI\Cart\Storage\SessionStorage;

it('can add shipping conditions using addShipping method', function () {
    session()->flush();

    $storage = new SessionStorage(app('session.store'));
    $cart = new Cart(
        storage: $storage,
        events: null,
        instanceName: 'shipping-test',
        eventsEnabled: false
    );

    // Add items to cart
    $cart->add('product-1', 'Product 1', 100.00, 1);

    // Test addShipping method
    $cart->addShipping('Express Shipping', 15.99, 'express', [
        'estimated_days' => 1,
        'carrier' => 'UPS',
    ]);

    expect($cart->getShipping())->not->toBeNull()
        ->and($cart->getShippingMethod())->toBe('express')
        ->and($cart->getShippingValue())->toBe(15.99)
        ->and($cart->getShipping()->getAttribute('estimated_days'))->toBe(1)
        ->and($cart->getShipping()->getAttribute('carrier'))->toBe('UPS')
        ->and($cart->total())->toBe(115.99); // 100 + 15.99
});

it('can remove shipping conditions using removeShipping method', function () {
    session()->flush();

    $storage = new SessionStorage(app('session.store'));
    $cart = new Cart(
        storage: $storage,
        events: null,
        instanceName: 'shipping-remove-test',
        eventsEnabled: false
    );

    // Add items and shipping
    $cart->add('product-1', 'Product 1', 100.00, 1);
    $cart->addShipping('Standard Shipping', 9.99);

    expect($cart->getShipping())->not->toBeNull()
        ->and($cart->total())->toBe(109.99);

    // Remove shipping
    $cart->removeShipping();

    expect($cart->getShipping())->toBeNull()
        ->and($cart->getShippingMethod())->toBeNull()
        ->and($cart->getShippingValue())->toBeNull()
        ->and($cart->total())->toBe(100.00);
});

it('replaces existing shipping when adding new shipping', function () {
    session()->flush();

    $storage = new SessionStorage(app('session.store'));
    $cart = new Cart(
        storage: $storage,
        events: null,
        instanceName: 'shipping-replace-test',
        eventsEnabled: false
    );

    // Add items and first shipping
    $cart->add('product-1', 'Product 1', 100.00, 1);
    $cart->addShipping('Standard Shipping', 9.99, 'standard');

    expect($cart->getShippingMethod())->toBe('standard')
        ->and($cart->getShippingValue())->toBe(9.99);

    // Add new shipping - should replace the old one
    $cart->addShipping('Express Shipping', 19.99, 'express');

    expect($cart->getShippingMethod())->toBe('express')
        ->and($cart->getShippingValue())->toBe(19.99)
        ->and($cart->getConditions()->count())->toBe(1) // Only one shipping condition
        ->and($cart->total())->toBe(119.99); // 100 + 19.99
});

it('handles string and numeric shipping values correctly', function () {
    session()->flush();

    $storage = new SessionStorage(app('session.store'));
    $cart = new Cart(
        storage: $storage,
        events: null,
        instanceName: 'shipping-values-test',
        eventsEnabled: false
    );

    // Add items
    $cart->add('product-1', 'Product 1', 100.00, 1);

    // Test numeric value
    $cart->addShipping('Numeric Shipping', 15.50);
    expect($cart->getShippingValue())->toBe(15.50);

    // Test string value
    $cart->addShipping('String Shipping', '12.99');
    expect($cart->getShippingValue())->toBe(12.99);

    // Test string with + prefix
    $cart->addShipping('Plus Shipping', '+8.75');
    expect($cart->getShippingValue())->toBe(8.75);
});
