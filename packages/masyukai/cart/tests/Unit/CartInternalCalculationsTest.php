<?php

declare(strict_types=1);

use MasyukAI\Cart\Cart;
use MasyukAI\Cart\Conditions\CartCondition;
use MasyukAI\Cart\Storage\SessionStorage;

describe('Cart Internal Calculations', function () {
    beforeEach(function () {
        // Set up session storage for testing
        $sessionStore = new \Illuminate\Session\Store('testing', new \Illuminate\Session\ArraySessionHandler(120));
        $sessionStorage = new SessionStorage($sessionStore);

        $this->cart = new Cart(
            storage: $sessionStorage,
            events: new \Illuminate\Events\Dispatcher,
            identifier: 'test_internal_calc',
            instanceName: 'test_internal_calc',
            eventsEnabled: true
        );
        $this->cart->clear(); // Ensure clean state

        $this->cart->add('product-1', 'Product 1', 100.00, 2); // 200 total
        $this->cart->add('product-2', 'Product 2', 50.00, 1);  // 50 total
        // Cart subtotal without conditions: 250
    });

    it('calculates raw subtotal correctly', function () {
        expect($this->cart->getRawSubtotal())->toBe(250.00);
    });

    it('calculates raw subtotal without conditions correctly', function () {
        // Add item-level conditions
        $itemDiscount = new CartCondition('item_discount', 'discount', 'subtotal', '-20%');
        $this->cart->addItemCondition('product-1', $itemDiscount);
        // product-1: (100 - 20%) * 2 = 160
        // product-2: 50 * 1 = 50
        // Total with conditions: 210

        // But subtotal without conditions should still be 250
        expect($this->cart->getRawSubtotalWithoutConditions())->toBe(250.00);
        expect($this->cart->getRawSubtotal())->toBe(210.00);
    });

    it('calculates raw total with cart-level conditions correctly', function () {
        $cartTax = new CartCondition('tax', 'tax', 'subtotal', '+10%');
        $this->cart->addCondition($cartTax);

        // Subtotal: 250, Tax: +10% = 275
        expect($this->cart->getRawTotal())->toBe(275.00);
    });

    it('calculates complex totals with both item and cart conditions', function () {
        // Add item-level discount
        $itemDiscount = new CartCondition('item_discount', 'discount', 'subtotal', '-20%');
        $this->cart->addItemCondition('product-1', $itemDiscount);
        // product-1: (100 - 20%) * 2 = 160
        // product-2: 50 * 1 = 50
        // Subtotal with item conditions: 210

        // Add cart-level tax - now targets 'total' to be applied after subtotal
        $cartTax = new CartCondition('tax', 'tax', 'total', '+10%');
        $this->cart->addCondition($cartTax);
        // Subtotal: 210, Total: 210 + 10% = 231

        expect($this->cart->getRawSubtotalWithoutConditions())->toBe(250.00);
        expect($this->cart->getRawSubtotal())->toBe(210.00);
        expect($this->cart->getRawTotal())->toBe(231.00);
    });

    it('internal calculations use raw methods consistently', function () {
        // Verify that internal calculations return floats (not formatted values)
        expect($this->cart->getRawSubtotal())->toBeFloat();
        expect($this->cart->getRawSubtotalWithoutConditions())->toBeFloat();
        expect($this->cart->getRawTotal())->toBeFloat();

        // Verify formatted methods return Money objects
        expect($this->cart->subtotal())->toBeInstanceOf(\Akaunting\Money\Money::class);
        expect($this->cart->total())->toBeInstanceOf(\Akaunting\Money\Money::class);
    });

    it('savings calculation uses correct raw methods', function () {
        $itemDiscount = new CartCondition('item_discount', 'discount', 'subtotal', '-20%');
        $this->cart->addItemCondition('product-1', $itemDiscount);

        $cartDiscount = new CartCondition('cart_discount', 'discount', 'subtotal', '-10%');
        $this->cart->addCondition($cartDiscount);

        // Original: 250
        // With item conditions: 210
        // With cart conditions: 210 - 10% = 189
        // Savings: 250 - 189 = 61

        $expectedSavings = 250.00 - 189.00;
        expect($this->cart->savings()->getAmount())->toBe($expectedSavings);
    });

    it('handles empty cart internal calculations', function () {
        $sessionStore = new \Illuminate\Session\Store('testing', new \Illuminate\Session\ArraySessionHandler(120));
        $sessionStorage = new SessionStorage($sessionStore);

        $emptyCart = new Cart(
            storage: $sessionStorage,
            events: new \Illuminate\Events\Dispatcher,
            identifier: 'empty_test_cart',
            instanceName: 'empty_test_cart',
            eventsEnabled: true
        );

        expect($emptyCart->getRawSubtotal())->toBe(0.00);
        expect($emptyCart->getRawSubtotalWithoutConditions())->toBe(0.00);
        expect($emptyCart->getRawTotal())->toBe(0.00);
    });
});
