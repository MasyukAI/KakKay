<?php

declare(strict_types=1);

use MasyukAI\Cart\Cart;
use MasyukAI\Cart\Conditions\CartCondition;
use MasyukAI\Cart\Storage\SessionStorage;

beforeEach(function () {
    // Initialize session storage for testing
    $sessionStore = new \Illuminate\Session\Store('testing', new \Illuminate\Session\ArraySessionHandler(120));
    $this->sessionStorage = new SessionStorage($sessionStore);

    // Initialize cart
    $this->cart = new Cart(
        storage: $this->sessionStorage,
        events: app('events'),
        instanceName: 'conditions_test',
        eventsEnabled: true
    );

    $this->cart->clear();
    
    // Add test items for condition testing
    $this->cart->add('product-1', 'Product 1', 100.00, 1);
    $this->cart->add('product-2', 'Product 2', 50.00, 2);
});

describe('Cart Global Conditions Management', function () {
    it('can add and apply global cart conditions', function () {
        $tax = new CartCondition('tax', 'tax', 'subtotal', '+10%');
        $shipping = new CartCondition('shipping', 'charge', 'subtotal', '+5.99');

        $this->cart->addCondition($tax);
        $this->cart->addCondition($shipping);

        expect($this->cart->getConditions())->toHaveCount(2);
        expect($this->cart->getCondition('tax'))->toBeInstanceOf(CartCondition::class);
        expect($this->cart->getCondition('shipping'))->toBeInstanceOf(CartCondition::class);

        // 200 * 1.1 + 5.99 = 225.99
        expect($this->cart->total()->getAmount())->toBe(225.99);
    });

    it('can remove specific conditions', function () {
        $tax = new CartCondition('tax', 'tax', 'subtotal', '+10%');
        $discount = new CartCondition('discount', 'discount', 'subtotal', '-5%');

        $this->cart->addCondition($tax);
        $this->cart->addCondition($discount);

        expect($this->cart->getConditions())->toHaveCount(2);

        $result = $this->cart->removeCondition('tax');
        expect($result)->toBeTrue();
        expect($this->cart->getConditions())->toHaveCount(1);
        expect($this->cart->getCondition('tax'))->toBeNull();

        $result = $this->cart->removeCondition('nonexistent');
        expect($result)->toBeFalse();
    });

    it('can clear all conditions', function () {
        $tax = new CartCondition('tax', 'tax', 'subtotal', '+10%');
        $discount = new CartCondition('discount', 'discount', 'subtotal', '-5%');

        $this->cart->addCondition($tax);
        $this->cart->addCondition($discount);

        expect($this->cart->getConditions())->toHaveCount(2);

        $result = $this->cart->clearConditions();
        expect($result)->toBeTrue();
        expect($this->cart->getConditions())->toHaveCount(0);
        expect($this->cart->total()->getAmount())->toBe(200.00); // Back to original total
    });

    it('calculates totals correctly with multiple condition types', function () {
        $discount = new CartCondition('discount', 'discount', 'subtotal', '-10%'); // -20
        $tax = new CartCondition('tax', 'tax', 'subtotal', '+15%'); // +27 (on discounted amount)
        $shipping = new CartCondition('shipping', 'charge', 'subtotal', '+9.99');

        $this->cart->addCondition($discount);
        $this->cart->addCondition($tax);
        $this->cart->addCondition($shipping);

        // 200 - 20 = 180, then +15% = 207, then +9.99 = 216.99
        expect($this->cart->subtotal()->getAmount())->toBe(200.00);
        expect($this->cart->total()->getAmount())->toBe(216.99);
    });
});

describe('Cart Item-Specific Conditions', function () {
    it('can add conditions to specific items', function () {
        $itemDiscount = new CartCondition('item_discount', 'discount', 'subtotal', '-20%');

        $result = $this->cart->addItemCondition('product-1', $itemDiscount);
        expect($result)->toBeTrue();

        $item = $this->cart->get('product-1');
        expect($item->getConditions())->toHaveCount(1);
        expect($item->getRawPriceSum())->toBe(80.00); // 100 - 20%

        // Adding to non-existent item should fail
        $result = $this->cart->addItemCondition('nonexistent', $itemDiscount);
        expect($result)->toBeFalse();
    });

    it('can remove item-specific conditions', function () {
        $itemDiscount = new CartCondition('item_discount', 'discount', 'subtotal', '-20%');

        $this->cart->addItemCondition('product-1', $itemDiscount);
        expect($this->cart->get('product-1')->getConditions())->toHaveCount(1);

        $result = $this->cart->removeItemCondition('product-1', 'item_discount');
        expect($result)->toBeTrue();
        expect($this->cart->get('product-1')->getConditions())->toHaveCount(0);

        // Removing non-existent condition should fail
        $result = $this->cart->removeItemCondition('product-1', 'nonexistent');
        expect($result)->toBeFalse();
    });

    it('can clear all conditions from specific items', function () {
        $discount1 = new CartCondition('discount1', 'discount', 'subtotal', '-10%');
        $discount2 = new CartCondition('discount2', 'discount', 'subtotal', '-5%');

        $this->cart->addItemCondition('product-1', $discount1);
        $this->cart->addItemCondition('product-1', $discount2);

        expect($this->cart->get('product-1')->getConditions())->toHaveCount(2);

        $result = $this->cart->clearItemConditions('product-1');
        expect($result)->toBeTrue();
        expect($this->cart->get('product-1')->getConditions())->toHaveCount(0);
    });
});