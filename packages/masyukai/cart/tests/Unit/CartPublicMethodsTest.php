<?php

declare(strict_types=1);

use MasyukAI\Cart\Cart;
use MasyukAI\Cart\Conditions\CartCondition;
use MasyukAI\Cart\Models\CartItem;
use MasyukAI\Cart\Storage\SessionStorage;

describe('Public Formatted Methods', function () {
    beforeEach(function () {
        // Set up session storage for testing
        $sessionStore = new \Illuminate\Session\Store('testing', new \Illuminate\Session\ArraySessionHandler(120));
        $sessionStorage = new SessionStorage($sessionStore);

        $this->cart = new Cart(
            storage: $sessionStorage,
            events: new \Illuminate\Events\Dispatcher,
            instanceName: 'formatted_test',
            eventsEnabled: true
        );
        $this->cart->clear();

        $this->item = new CartItem('product-1', 'Test Product', 100.00, 2);
        $this->discount = new CartCondition('discount', 'discount', 'subtotal', '-20%');
    });

    describe('CartItem public methods', function () {
        it('returns formatted prices when formatting is enabled', function () {
            Cart::enableFormatting();

            expect($this->item->getPrice())->toBeString();
            expect($this->item->getPriceWithoutConditions())->toBeString();
            expect($this->item->getPriceSum())->toBeString();
            expect($this->item->getPriceSumWithoutConditions())->toBeString();
            expect($this->item->subtotal())->toBeString();

            Cart::disableFormatting();
        });

        it('returns numeric values when formatting is disabled', function () {
            Cart::disableFormatting();

            expect($this->item->getPrice())->toBeFloat();
            expect($this->item->getPriceWithoutConditions())->toBeFloat();
            expect($this->item->getPriceSum())->toBeFloat();
            expect($this->item->getPriceSumWithoutConditions())->toBeFloat();
            expect($this->item->subtotal())->toBeFloat();
        });

        it('applies conditions correctly in public methods', function () {
            Cart::disableFormatting();

            $itemWithDiscount = $this->item->addCondition($this->discount);

            // Without conditions: raw price
            expect($itemWithDiscount->getPriceWithoutConditions())->toBe(100.00);
            expect($itemWithDiscount->getPriceSumWithoutConditions())->toBe(200.00);

            // With conditions: calculated price
            expect($itemWithDiscount->getPrice())->toBe(80.00); // 100 - 20%
            expect($itemWithDiscount->getPriceSum())->toBe(160.00); // 80 * 2
            expect($itemWithDiscount->subtotal())->toBe(160.00); // alias for getPriceSum
        });

        it('calculates discount amount correctly', function () {
            Cart::disableFormatting();

            $itemWithDiscount = $this->item->addCondition($this->discount);

            // Original: 200, With conditions: 160, Discount: 40
            expect($itemWithDiscount->getDiscountAmount())->toBe(40.00);
        });
    });

    describe('Cart public methods', function () {
        beforeEach(function () {
            $this->cart->add('product-1', 'Product 1', 100.00, 2); // 200
            $this->cart->add('product-2', 'Product 2', 50.00, 1);  // 50
            // Total: 250
        });

        it('returns formatted values when formatting is enabled', function () {
            Cart::enableFormatting();

            expect($this->cart->subtotal())->toBeString();
            expect($this->cart->subtotalWithoutConditions())->toBeString();
            expect($this->cart->total())->toBeString();
            expect($this->cart->totalWithoutConditions())->toBeString();
            expect($this->cart->savings())->toBeString();

            Cart::disableFormatting();
        });

        it('returns numeric values when formatting is disabled', function () {
            Cart::disableFormatting();

            expect($this->cart->subtotal())->toBeFloat();
            expect($this->cart->subtotalWithoutConditions())->toBeFloat();
            expect($this->cart->total())->toBeFloat();
            expect($this->cart->totalWithoutConditions())->toBeFloat();
            expect($this->cart->savings())->toBeFloat();
        });

        it('calculates public totals correctly with item conditions', function () {
            Cart::disableFormatting();

            $itemDiscount = new CartCondition('item_discount', 'discount', 'subtotal', '-20%');
            $this->cart->addItemCondition('product-1', $itemDiscount);

            // product-1: (100 - 20%) * 2 = 160
            // product-2: 50 * 1 = 50
            // Total: 210

            expect($this->cart->subtotalWithoutConditions())->toBe(250.00);
            expect($this->cart->subtotal())->toBe(210.00);
            expect($this->cart->total())->toBe(210.00); // No cart conditions
            expect($this->cart->totalWithoutConditions())->toBe(250.00);
            expect($this->cart->savings())->toBe(40.00); // 250 - 210
        });

        it('calculates public totals correctly with cart conditions', function () {
            Cart::disableFormatting();

            $cartTax = new CartCondition('tax', 'tax', 'subtotal', '+10%');
            $this->cart->addCondition($cartTax);

            // Subtotal: 250, Tax: +10% = 275

            expect($this->cart->subtotal())->toBe(250.00); // Item-level only
            expect($this->cart->total())->toBe(275.00); // With cart conditions
            expect($this->cart->totalWithoutConditions())->toBe(250.00);
            expect($this->cart->savings())->toBe(0.00); // No item discounts, just tax
        });

        it('calculates public totals correctly with both conditions', function () {
            Cart::disableFormatting();

            $itemDiscount = new CartCondition('item_discount', 'discount', 'subtotal', '-20%');
            $this->cart->addItemCondition('product-1', $itemDiscount);

            $cartTax = new CartCondition('tax', 'tax', 'subtotal', '+10%');
            $this->cart->addCondition($cartTax);

            // Item conditions: 250 -> 210
            // Cart conditions: 210 + 10% = 231

            expect($this->cart->subtotalWithoutConditions())->toBe(250.00);
            expect($this->cart->subtotal())->toBe(210.00);
            expect($this->cart->total())->toBe(231.00);
            expect($this->cart->totalWithoutConditions())->toBe(250.00);
            expect($this->cart->savings())->toBe(19.00); // 250 - 231
        });
    });

    describe('Formatting consistency', function () {
        it('maintains consistency between raw and formatted methods', function () {
            Cart::disableFormatting();

            $this->cart->add('test-item', 'Test', 99.99, 1);

            // When formatting is disabled, public methods should return same as raw
            expect($this->cart->subtotal())->toBe($this->cart->getRawSubtotal());
            expect($this->cart->total())->toBe($this->cart->getRawTotal());
            expect($this->cart->subtotalWithoutConditions())->toBe($this->cart->getRawSubTotalWithoutConditions());
        });

        it('formats values correctly when enabled', function () {
            Cart::enableFormatting();

            $this->cart->add('test-item', 'Test', 99.99, 1);

            // When formatting is enabled, public methods should return formatted strings
            expect($this->cart->subtotal())->toBeString();
            expect($this->cart->total())->toBeString();

            // But raw methods should still return floats
            expect($this->cart->getRawSubtotal())->toBeFloat();
            expect($this->cart->getRawTotal())->toBeFloat();

            Cart::disableFormatting();
        });
    });
});
