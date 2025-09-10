<?php

declare(strict_types=1);

use MasyukAI\Cart\Cart;
use MasyukAI\Cart\Conditions\CartCondition;
use MasyukAI\Cart\Storage\SessionStorage;

describe('Comprehensive Condition Stacking Coverage', function () {
    beforeEach(function () {
        $sessionStore = new \Illuminate\Session\Store('testing', new \Illuminate\Session\ArraySessionHandler(120));
        $sessionStorage = new SessionStorage($sessionStore);

        $this->cart = new Cart(
            storage: $sessionStorage,
            events: new \Illuminate\Events\Dispatcher,
            instanceName: 'test_conditions',
            eventsEnabled: true
        );
        $this->cart->clear();

        // Set up test items
        $this->cart->add('item-1', 'Premium Item', 100.00, 2);  // $200 subtotal
        $this->cart->add('item-2', 'Standard Item', 50.00, 1);  // $50 subtotal
        // Total cart subtotal: $250

        // Define various condition types
        $this->itemDiscount = new CartCondition('item_discount', 'discount', 'subtotal', '-20%');
        $this->itemTax = new CartCondition('item_tax', 'tax', 'subtotal', '+10%');
        $this->itemFee = new CartCondition('handling_fee', 'fee', 'subtotal', '+5');

        $this->cartDiscount = new CartCondition('cart_discount', 'discount', 'subtotal', '-15%');
        $this->cartTax = new CartCondition('sales_tax', 'tax', 'subtotal', '+8.25%');
        $this->cartShipping = new CartCondition('shipping', 'shipping', 'subtotal', '+25');
        $this->cartInsurance = new CartCondition('insurance', 'insurance', 'subtotal', '+10');
    });

    describe('Item-Level Condition Stacking', function () {
        it('applies single item condition correctly', function () {
            $this->cart->addItemCondition('item-1', $this->itemDiscount);
            $item = $this->cart->get('item-1');

            // item-1: (100 - 20%) * 2 = 80 * 2 = 160
            expect($item->getRawPriceSum())->toBe(160.00);
            expect($item->getRawPriceSumWithoutConditions())->toBe(200.00);
            expect($item->getDiscountAmount())->toBe(40.00);
        });

        it('applies multiple item conditions in order', function () {
            $this->cart->addItemCondition('item-1', $this->itemDiscount);  // -20%
            $this->cart->addItemCondition('item-1', $this->itemTax);       // +10%
            $this->cart->addItemCondition('item-1', $this->itemFee);       // +$5

            $item = $this->cart->get('item-1');

            // Step by step calculation:
            // 1. Base: 100.00
            // 2. Discount -20%: 100 - 20 = 80.00
            // 3. Tax +10%: 80 + 8 = 88.00
            // 4. Fee +$5: 88 + 5 = 93.00
            // 5. Multiply by quantity: 93 * 2 = 186.00

            expect($item->getRawPriceSum())->toBe(186.00);
            expect($item->getRawPriceSumWithoutConditions())->toBe(200.00);
        });

        it('applies conditions to multiple items independently', function () {
            $this->cart->addItemCondition('item-1', $this->itemDiscount);  // -20% on item-1
            $this->cart->addItemCondition('item-2', $this->itemTax);       // +10% on item-2

            $item1 = $this->cart->get('item-1');
            $item2 = $this->cart->get('item-2');

            // item-1: (100 - 20%) * 2 = 160
            expect($item1->getRawPriceSum())->toBe(160.00);

            // item-2: (50 + 10%) * 1 = 55
            expect($item2->getRawPriceSum())->toBe(55.00);

            // Cart subtotal should include both
            expect($this->cart->getRawSubtotal())->toBe(215.00); // 160 + 55
        });

        it('handles condition order properly', function () {
            // Test different order of same conditions
            $condition1 = new CartCondition('first', 'discount', 'subtotal', '-20%', [], 1);
            $condition2 = new CartCondition('second', 'tax', 'subtotal', '+10%', [], 2);
            $condition3 = new CartCondition('third', 'fee', 'subtotal', '+5', [], 3);

            $this->cart->addItemCondition('item-1', $condition2);  // Add in different order
            $this->cart->addItemCondition('item-1', $condition1);
            $this->cart->addItemCondition('item-1', $condition3);

            $item = $this->cart->get('item-1');

            // Should apply in order specified (1, 2, 3) regardless of add order
            // Same calculation as above: 100 -> 80 -> 88 -> 93 -> 186
            expect($item->getRawPriceSum())->toBe(186.00);
        });
    });

    describe('Cart-Level Condition Stacking', function () {
        it('applies single cart condition correctly', function () {
            $this->cart->addCondition($this->cartDiscount);

            // Cart subtotal: 250
            // Discount -15%: 250 - 37.5 = 212.5
            expect($this->cart->getRawTotal())->toBe(212.50);
        });

        it('applies multiple cart conditions in order', function () {
            $this->cart->addCondition($this->cartDiscount);   // -15%
            $this->cart->addCondition($this->cartTax);        // +8.25%
            $this->cart->addCondition($this->cartShipping);   // +$25
            $this->cart->addCondition($this->cartInsurance);  // +$10

            // Step by step:
            // 1. Subtotal: 250.00
            // 2. Discount -15%: 250 - 37.5 = 212.50
            // 3. Tax +8.25%: 212.50 + 17.53 = 230.03
            // 4. Shipping +$25: 230.03 + 25 = 255.03
            // 5. Insurance +$10: 255.03 + 10 = 265.03

            expect(abs($this->cart->getRawTotal() - 265.03))->toBeLessThan(0.1);
        });

        it('handles cart condition order properly', function () {
            $discount = new CartCondition('discount', 'discount', 'subtotal', '-15%', [], 1);
            $tax = new CartCondition('tax', 'tax', 'subtotal', '+8.25%', [], 2);
            $shipping = new CartCondition('shipping', 'shipping', 'subtotal', '+25', [], 3);

            // Add in reverse order
            $this->cart->addCondition($shipping);
            $this->cart->addCondition($tax);
            $this->cart->addCondition($discount);

            // Should still apply in order 1, 2, 3
            expect(abs($this->cart->getRawTotal() - 255.03))->toBeLessThan(0.1); // Same as above without insurance
        });
    });

    describe('Mixed Item and Cart Condition Stacking', function () {
        it('applies item conditions first, then cart conditions', function () {
            // Add item-level conditions
            $this->cart->addItemCondition('item-1', $this->itemDiscount);  // -20% on item-1
            $this->cart->addItemCondition('item-2', $this->itemTax);       // +10% on item-2

            // Add cart-level conditions
            $this->cart->addCondition($this->cartDiscount);   // -15% on cart
            $this->cart->addCondition($this->cartTax);        // +8.25% on cart

            // Item calculations:
            // item-1: (100 - 20%) * 2 = 160
            // item-2: (50 + 10%) * 1 = 55
            // Subtotal with item conditions: 215

            expect($this->cart->getRawSubtotal())->toBe(215.00);

            // Cart calculations:
            // 1. Start with item-conditioned subtotal: 215
            // 2. Cart discount -15%: 215 - 32.25 = 182.75
            // 3. Cart tax +8.25%: 182.75 + 15.08 = 197.83

            expect(abs($this->cart->getRawTotal() - 197.83))->toBeLessThan(0.1);
        });

        it('handles complex mixed stacking scenarios', function () {
            // Multiple conditions on each item
            $this->cart->addItemCondition('item-1', $this->itemDiscount);  // -20%
            $this->cart->addItemCondition('item-1', $this->itemFee);       // +$5
            $this->cart->addItemCondition('item-2', $this->itemTax);       // +10%

            // Multiple cart conditions
            $this->cart->addCondition($this->cartDiscount);   // -15%
            $this->cart->addCondition($this->cartShipping);   // +$25
            $this->cart->addCondition($this->cartTax);        // +8.25%

            // Item calculations:
            // item-1: ((100 - 20%) + 5) * 2 = (80 + 5) * 2 = 170
            // item-2: (50 + 10%) * 1 = 55
            // Subtotal: 225

            expect($this->cart->getRawSubtotal())->toBe(225.00);

            // Cart calculations:
            // 1. Start: 225
            // 2. Discount -15%: 225 - 33.75 = 191.25
            // 3. Shipping +$25: 191.25 + 25 = 216.25
            // 4. Tax +8.25%: 216.25 + 17.84 = 234.09

            expect(abs($this->cart->getRawTotal() - 234.09))->toBeLessThan(0.1);
        });
    });

    describe('Edge Cases and Complex Scenarios', function () {
        it('handles negative prices correctly', function () {
            $heavyDiscount = new CartCondition('heavy_discount', 'discount', 'subtotal', '-150%');
            $this->cart->addItemCondition('item-1', $heavyDiscount);

            $item = $this->cart->get('item-1');

            // Price should not go below 0
            expect($item->getRawPrice())->toBe(0.00);
            expect($item->getRawPriceSum())->toBe(0.00);
        });

        it('handles zero quantity items', function () {
            // Cart doesn't allow zero quantity, so this should throw an exception
            expect(fn () => $this->cart->add('zero-qty-item', 'Zero Quantity Item', 100.00, 0))
                ->toThrow(\MasyukAI\Cart\Exceptions\InvalidCartItemException::class);
        });

        it('handles free items with conditions', function () {
            $this->cart->add('free-item', 'Free Item', 0.00, 1);
            $this->cart->addItemCondition('free-item', $this->itemTax);     // +10%
            $this->cart->addItemCondition('free-item', $this->itemFee);     // +$5

            $item = $this->cart->get('free-item');

            // 0 + 10% = 0, then 0 + $5 = $5
            expect($item->getRawPriceSum())->toBe(5.00);
        });

        it('handles percentage conditions on small amounts', function () {
            $this->cart->add('small-item', 'Small Item', 0.01, 1);
            $this->cart->addItemCondition('small-item', $this->itemDiscount); // -20%

            $item = $this->cart->get('small-item');

            // 0.01 - 20% = 0.008
            expect($item->getRawPriceSum())->toBe(0.008);
        });

        it('handles large quantities and amounts', function () {
            $this->cart->add('bulk-item', 'Bulk Item', 999.99, 1000);
            $this->cart->addItemCondition('bulk-item', $this->itemDiscount);  // -20%
            $this->cart->addCondition($this->cartTax);                       // +8.25%

            $item = $this->cart->get('bulk-item');

            // Item: (999.99 - 20%) * 1000 = 799.992 * 1000 = 799992
            expect($item->getRawPriceSum())->toBe(799992.00);

            // Cart total should include tax on this amount
            $expectedTotal = 799992 * 1.0825; // +8.25%
            expect(abs($this->cart->getRawTotal() - $expectedTotal))->toBeLessThan(500.0);
        });

        it('handles condition removal and re-application', function () {
            $this->cart->addItemCondition('item-1', $this->itemDiscount);
            $this->cart->addItemCondition('item-1', $this->itemTax);

            $item = $this->cart->get('item-1');
            $originalTotal = $item->getRawPriceSum();

            // Remove one condition
            $this->cart->removeItemCondition('item-1', 'item_discount');
            $item = $this->cart->get('item-1');
            $afterRemoval = $item->getRawPriceSum();

            // Should only have tax now: (100 + 10%) * 2 = 220
            expect($afterRemoval)->toBe(220.00);
            expect($afterRemoval)->not->toBe($originalTotal);

            // Re-add the discount
            $this->cart->addItemCondition('item-1', $this->itemDiscount);
            $item = $this->cart->get('item-1');

            // Should be back to original total
            expect($item->getRawPriceSum())->toBe($originalTotal);
        });

        it('handles condition clearing', function () {
            $this->cart->addItemCondition('item-1', $this->itemDiscount);
            $this->cart->addItemCondition('item-1', $this->itemTax);
            $this->cart->addCondition($this->cartDiscount);
            $this->cart->addCondition($this->cartTax);

            $originalSubtotal = $this->cart->getRawSubtotal();
            $originalTotal = $this->cart->getRawTotal();

            // Clear all item conditions for item-1
            $this->cart->clearItemConditions('item-1');

            // item-1 should now have no conditions
            $item = $this->cart->get('item-1');
            expect($item->getRawPriceSum())->toBe(200.00); // Back to base price

            // Clear all cart conditions
            $this->cart->clearConditions();

            // Total should equal subtotal
            expect($this->cart->getRawTotal())->toBe($this->cart->getRawSubtotal());
        });
    });

    describe('Condition Type Segregation', function () {
        it('properly handles different condition types', function () {
            $discount = new CartCondition('discount', 'discount', 'subtotal', '-10%');
            $tax = new CartCondition('tax', 'tax', 'subtotal', '+8%');
            $shipping = new CartCondition('shipping', 'shipping', 'subtotal', '+15');
            $fee = new CartCondition('fee', 'fee', 'subtotal', '+5');
            $insurance = new CartCondition('insurance', 'insurance', 'subtotal', '+3%');

            $this->cart->addCondition($discount);
            $this->cart->addCondition($tax);
            $this->cart->addCondition($shipping);
            $this->cart->addCondition($fee);
            $this->cart->addCondition($insurance);

            // All should apply regardless of type
            // 250 -> 225 (discount) -> 243 (tax) -> 258 (shipping) -> 263 (fee) -> 270.89 (insurance)
            $conditions = $this->cart->getConditions();
            expect($conditions->count())->toBe(5);

            // Verify each type is present
            expect($conditions->filter(fn ($c) => $c->getType() === 'discount')->count())->toBe(1);
            expect($conditions->filter(fn ($c) => $c->getType() === 'tax')->count())->toBe(1);
            expect($conditions->filter(fn ($c) => $c->getType() === 'shipping')->count())->toBe(1);
            expect($conditions->filter(fn ($c) => $c->getType() === 'fee')->count())->toBe(1);
            expect($conditions->filter(fn ($c) => $c->getType() === 'insurance')->count())->toBe(1);
        });
    });
});
