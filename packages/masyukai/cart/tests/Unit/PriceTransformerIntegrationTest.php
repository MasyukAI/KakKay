<?php

declare(strict_types=1);

use MasyukAI\Cart\Cart;
use MasyukAI\Cart\Conditions\CartCondition;
use MasyukAI\Cart\PriceTransformers\DecimalPriceTransformer;
use MasyukAI\Cart\Storage\SessionStorage;

/**
 * Integration tests for PriceTransformer with Cart system
 * 
 * These tests verify that the Cart system correctly integrates with PriceTransformers
 * to handle price conversion between input, storage, and display formats.
 * 
 * Note: Individual transformer behavior is tested in Unit/PriceTransformers/*Test.php
 */
describe('Cart-PriceTransformer Integration', function () {
    beforeEach(function () {
        session()->flush();
        
        $storage = new SessionStorage(app('session.store'));
        $this->cart = new Cart(
            storage: $storage,
            events: null,
            instanceName: 'integration-test',
            eventsEnabled: false
        );
        $this->cart->clear();
    });

    describe('Default Integration Behavior', function () {
        it('uses the configured price transformer from service container', function () {
            // Access transformer via reflection since it's protected
            $reflection = new \ReflectionMethod($this->cart, 'getPriceTransformer');
            $reflection->setAccessible(true);
            $transformer = $reflection->invoke($this->cart);
            
            // Should use the default DecimalPriceTransformer
            expect($transformer)->toBeInstanceOf(DecimalPriceTransformer::class);
        });

        it('applies price transformation consistently across all monetary calculations', function () {
            // Add multiple items to test consistency
            $this->cart->add('product-1', 'Product A', 15.25, 2);  // 30.50
            $this->cart->add('product-2', 'Product B', 8.75, 1);   // 8.75
            
            $expectedTotal = 39.25; // 30.50 + 8.75
            
            // All monetary methods should return the same transformed values
            expect($this->cart->subtotal()->getAmount())->toBe($expectedTotal);
            expect($this->cart->subtotalWithoutConditions()->getAmount())->toBe($expectedTotal);
            expect($this->cart->total()->getAmount())->toBe($expectedTotal);
            
            // For DecimalPriceTransformer, raw values should equal display values
            expect($this->cart->getRawSubTotal())->toBe($expectedTotal);
        });
    });

    describe('Price Input Processing', function () {
        it('normalizes various price input formats through the transformer', function () {
            // The ManagesItems trait should use the transformer to normalize prices
            $this->cart->add('product-1', 'Thousands Separator', '1,999.99', 1); // Comma as thousands separator
            $this->cart->add('product-2', 'Currency Symbol', '$25.50', 1);       // With $ symbol
            $this->cart->add('product-3', 'Standard Float', 10.75, 1);           // Regular float
            
            // Should normalize correctly: 1999.99 + 25.50 + 10.75 = 2036.24
            expect($this->cart->total()->getAmount())->toBe(2036.24);
        });

        it('handles edge cases in price normalization', function () {
            $this->cart->add('free-item', 'Free Product', 0.0, 1);
            $this->cart->add('paid-item', 'Paid Product', 10.00, 1);
            
            expect($this->cart->total()->getAmount())->toBe(10.00);
            expect($this->cart->countItems())->toBe(2);
        });
    });

    describe('Integration with Cart Conditions', function () {
        it('applies price transformation to conditional calculations', function () {
            $this->cart->add('product-1', 'Test Product', 25.50, 1);
            
            // Add a percentage discount condition
            $condition = new CartCondition(
                name: 'discount',
                type: 'discount', 
                target: 'total',
                value: '-10%'
            );
            $this->cart->addCondition($condition);
            
            // Subtotal should remain unchanged by total-targeting conditions
            expect($this->cart->subtotalWithoutConditions()->getAmount())->toBe(25.50);
            
            // Total should reflect the discount: 25.50 - 10% = 22.95
            expect($this->cart->total()->getAmount())->toBe(22.95);
        });

        it('maintains precision in complex calculations with conditions', function () {
            $this->cart->add('product-1', 'Product 1', 19.99, 2);   // 39.98
            $this->cart->add('product-2', 'Product 2', 15.50, 1);   // 15.50
            // Subtotal: 55.48
            
            // Add shipping
            $this->cart->addShipping('Standard Shipping', 5.99);
            
            // Add tax
            $taxCondition = new CartCondition(
                name: 'tax',
                type: 'tax',
                target: 'total', 
                value: '+8.5%'
            );
            $this->cart->addCondition($taxCondition);
            
            // Expected: (55.48 + 5.99) * 1.085 = 66.725 → 66.73 (rounded)
            $expectedTotal = round((55.48 + 5.99) * 1.085, 2);
            
            expect($this->cart->total()->getAmount())->toBe($expectedTotal);
        });
    });

    describe('Consistency Verification', function () {
        it('maintains consistent precision across operations', function () {
            // Test floating point precision edge cases
            $this->cart->add('item-1', 'Item 1', 0.1, 1);
            $this->cart->add('item-2', 'Item 2', 0.2, 1);
            
            // Should be exactly 0.3, not 0.30000000000000004
            expect($this->cart->total()->getAmount())->toBe(0.3);
        });

        it('handles cart clearing and re-population correctly', function () {
            // Add items
            $this->cart->add('product-1', 'Product 1', 10.00, 1);
            expect($this->cart->total()->getAmount())->toBe(10.00);
            
            // Clear and re-add
            $this->cart->clear();
            expect($this->cart->total()->getAmount())->toBe(0.0);
            
            $this->cart->add('product-2', 'Product 2', 20.00, 1);
            expect($this->cart->total()->getAmount())->toBe(20.00);
        });
    });
});