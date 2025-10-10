<?php

declare(strict_types=1);

use MasyukAI\Cart\Cart;
use MasyukAI\Cart\Conditions\CartCondition;
use MasyukAI\Cart\Exceptions\InvalidCartConditionException;
use MasyukAI\Cart\Storage\SessionStorage;

beforeEach(function () {
    $sessionStore = new Illuminate\Session\Store('testing', new Illuminate\Session\ArraySessionHandler(120));
    $this->storage = new SessionStorage($sessionStore);
    $this->cart = new Cart($this->storage, 'dynamic_guard_test');
});

afterEach(function () {
    $this->cart->clear();
});

it('prevents adding dynamic conditions using addCondition', function () {
    // Create a dynamic condition (with rules)
    $dynamicCondition = new CartCondition(
        name: 'dynamic_discount',
        type: 'discount',
        target: 'subtotal',
        value: '-10%',
        rules: [
            fn ($cart) => $cart->subtotalWithoutConditions()->getAmount() >= 100,
        ]
    );

    // Attempting to add it via addCondition should throw exception
    expect(fn () => $this->cart->addCondition($dynamicCondition))
        ->toThrow(
            InvalidCartConditionException::class,
            'Cannot add dynamic condition "dynamic_discount" using addCondition(). Dynamic conditions (with validation rules) must be registered using registerDynamicCondition() instead.'
        );
});

it('allows adding static conditions using addCondition', function () {
    // Create a static condition (no rules)
    $staticCondition = new CartCondition(
        name: 'static_discount',
        type: 'discount',
        target: 'subtotal',
        value: '-10',
        rules: null
    );

    // Should work fine
    $this->cart->addCondition($staticCondition);

    expect($this->cart->getConditions()->has('static_discount'))->toBeTrue();
});

it('correctly registers dynamic conditions using registerDynamicCondition', function () {
    $this->cart->add('product-1', 'Test Product', 100, 1);

    // Create a dynamic condition
    $dynamicCondition = new CartCondition(
        name: 'min_order_discount',
        type: 'discount',
        target: 'subtotal',
        value: '-10%',
        rules: [
            fn ($cart) => $cart->subtotalWithoutConditions()->getAmount() >= 100,
        ]
    );

    // Should work via registerDynamicCondition
    $this->cart->registerDynamicCondition($dynamicCondition);

    // Dynamic condition should be in dynamic conditions collection
    expect($this->cart->getDynamicConditions()->has('min_order_discount'))->toBeTrue();

    // A static copy should be added to cart (rules met)
    expect($this->cart->getConditions()->has('min_order_discount'))->toBeTrue();

    // The cart condition should NOT have rules (it's the static copy)
    $cartCondition = $this->cart->getCondition('min_order_discount');
    expect($cartCondition)->not->toBeNull();
    expect($cartCondition->isDynamic())->toBeFalse();
});

it('provides helpful error message about using withoutRules', function () {
    $dynamicCondition = new CartCondition(
        name: 'dynamic_fee',
        type: 'fee',
        target: 'total',
        value: '+5',
        rules: [fn ($cart) => true]
    );

    try {
        $this->cart->addCondition($dynamicCondition);
        expect(false)->toBeTrue('Exception should have been thrown');
    } catch (InvalidCartConditionException $e) {
        expect($e->getMessage())->toContain('withoutRules()');
        expect($e->getMessage())->toContain('registerDynamicCondition()');
    }
});

it('allows adding static copy of dynamic condition via withoutRules', function () {
    // Create a dynamic condition
    $dynamicCondition = new CartCondition(
        name: 'bypass_validation',
        type: 'discount',
        target: 'subtotal',
        value: '-15%',
        rules: [
            fn ($cart) => $cart->subtotalWithoutConditions()->getAmount() >= 200,
        ]
    );

    // Create static copy
    $staticCopy = $dynamicCondition->withoutRules();

    // Should work
    $this->cart->addCondition($staticCopy);

    expect($this->cart->getConditions()->has('bypass_validation'))->toBeTrue();
    expect($this->cart->getCondition('bypass_validation')->isDynamic())->toBeFalse();
});

it('handles array of conditions with mixed static and dynamic', function () {
    $staticCondition = new CartCondition(
        name: 'static_tax',
        type: 'tax',
        target: 'subtotal',
        value: '10%',
        rules: null
    );

    $dynamicCondition = new CartCondition(
        name: 'dynamic_discount',
        type: 'discount',
        target: 'subtotal',
        value: '-5',
        rules: [fn ($cart) => true]
    );

    // Attempting to add array with dynamic condition should fail
    expect(fn () => $this->cart->addCondition([$staticCondition, $dynamicCondition]))
        ->toThrow(
            InvalidCartConditionException::class,
            'Cannot add dynamic condition "dynamic_discount"'
        );

    // The static condition WAS added before the exception was thrown
    // This is expected behavior (not transactional)
    expect($this->cart->getConditions()->has('static_tax'))->toBeTrue();
    expect($this->cart->getConditions()->has('dynamic_discount'))->toBeFalse();
});

it('works correctly with VoucherCondition pattern', function () {
    // Simulating VoucherCondition's $dynamic parameter pattern

    // Static voucher (simple "$5 off")
    $staticVoucher = new CartCondition(
        name: 'voucher_SIMPLE5',
        type: 'discount',
        target: 'subtotal',
        value: '-5',
        rules: null  // Static mode
    );

    $this->cart->addCondition($staticVoucher);
    expect($this->cart->getConditions()->has('voucher_SIMPLE5'))->toBeTrue();

    // Dynamic voucher (with minimum order)
    $dynamicVoucher = new CartCondition(
        name: 'voucher_MIN100',
        type: 'discount',
        target: 'subtotal',
        value: '-10%',
        rules: [
            fn ($cart) => $cart->subtotalWithoutConditions()->getAmount() >= 100,
        ]
    );

    $this->cart->registerDynamicCondition($dynamicVoucher);
    expect($this->cart->getDynamicConditions()->has('voucher_MIN100'))->toBeTrue();
});
