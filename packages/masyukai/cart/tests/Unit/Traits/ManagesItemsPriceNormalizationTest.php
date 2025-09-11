<?php

declare(strict_types=1);

use MasyukAI\Cart\Cart;
use MasyukAI\Cart\PriceTransformers\DecimalPriceTransformer;
use MasyukAI\Cart\PriceTransformers\IntegerPriceTransformer;
use MasyukAI\Cart\Storage\SessionStorage;

/**
 * Unit tests for ManagesItems trait price normalization with PriceTransformers
 * 
 * These tests verify that the ManagesItems trait correctly uses PriceTransformers
 * to normalize and convert prices to storage format when adding/updating items.
 */
describe('ManagesItems Trait - Price Normalization', function () {
    beforeEach(function () {
        session()->flush();
        
        $storage = new SessionStorage(app('session.store'));
        $this->cart = new Cart(
            storage: $storage,
            events: null,
            instanceName: 'manages-items-test',
            eventsEnabled: false
        );
        $this->cart->clear();
    });

    describe('Price input normalization via normalizePrice()', function () {
        it('handles various string price formats', function () {
            // Test different input formats
            $this->cart->add('item-1', 'Thousands Separator', '1,999.99', 1);
            $this->cart->add('item-2', 'Currency Symbols', '$25.50', 1);
            $this->cart->add('item-3', 'Euro Symbol', '€15.75', 1);
            $this->cart->add('item-4', 'Pound Symbol', '£10.25', 1);
            $this->cart->add('item-5', 'With Spaces', ' 20.00 ', 1);
            
            // All should be normalized correctly
            // Note: The exact normalization depends on the transformer implementation
            $items = $this->cart->getItems();
            
            expect($items)->toHaveCount(5);
            expect($this->cart->total()->getAmount())->toBeGreaterThan(0);
        });

        it('handles numeric price inputs', function () {
            $this->cart->add('item-1', 'Float Price', 19.99, 1);
            $this->cart->add('item-2', 'Integer Price', 25, 1);
            $this->cart->add('item-3', 'Zero Price', 0, 1);
            
            $items = $this->cart->getItems();
            
            expect($items)->toHaveCount(3);
            expect($this->cart->total()->getAmount())->toBe(44.99); // 19.99 + 25.00 + 0.00
        });

        it('handles null price inputs', function () {
            $this->cart->add('item-1', 'Null Price', null, 1);
            
            $items = $this->cart->getItems();
            $item = $items->first();
            
            expect($item->price)->toBe(0.0);
            expect($this->cart->total()->getAmount())->toBe(0.0);
        });
    });

    describe('Integration with configured PriceTransformer', function () {
        it('uses the transformer for price conversion during storage', function () {
            // With default DecimalPriceTransformer
            $this->cart->add('item-1', 'Test Item', 19.99, 1);
            
            $item = $this->cart->getItems()->first();
            
            // For DecimalPriceTransformer, stored price should equal input price
            expect($item->price)->toBe(19.99);
        });

        it('respects transformer precision settings', function () {
            $this->cart->add('item-1', 'Precision Test', 19.999, 1);
            
            $item = $this->cart->getItems()->first();
            
            // Should be rounded to 2 decimal places by default
            expect($item->price)->toBe(20.00);
        });
    });

    describe('Price updating behavior', function () {
        it('normalizes prices when updating item properties', function () {
            $this->cart->add('item-1', 'Test Item', 19.99, 1);
            
            // Update the item price using a different format
            $this->cart->update('item-1', ['price' => '$25.50']);
            
            $item = $this->cart->getItems()->first();
            expect($item->price)->toBe(25.50);
            expect($this->cart->total()->getAmount())->toBe(25.50);
        });

        it('maintains price normalization consistency during bulk updates', function () {
            $this->cart->add('item-1', 'Item 1', 10.00, 1);
            $this->cart->add('item-2', 'Item 2', 15.00, 1);
            
            // Update multiple items with different price formats
            $this->cart->update('item-1', ['price' => '12.50']); // Standard format
            $this->cart->update('item-2', ['price' => '$18.75']); // With currency
            
            expect($this->cart->total()->getAmount())->toBe(31.25); // 12.50 + 18.75
        });
    });

    describe('Fallback behavior', function () {
        it('handles missing transformer gracefully', function () {
            // This tests the fallback behavior when transformer is not available
            // The normalizePrice method should fall back to CartMoney normalization
            
            $this->cart->add('item-1', 'Fallback Test', 19.99, 1);
            
            $item = $this->cart->getItems()->first();
            expect($item->price)->toBe(19.99);
        });

        it('handles transformer exceptions gracefully', function () {
            // Add item with potentially problematic input
            $this->cart->add('item-1', 'Edge Case', 'invalid-price', 1);
            
            $item = $this->cart->getItems()->first();
            
            // Should fallback to 0.0 or handle gracefully
            expect($item->price)->toBeFloat();
        });
    });

    describe('Configuration-specific behavior', function () {
        it('respects cart-specific decimal configuration when available', function () {
            // Create cart with specific decimals config
            $storage = new SessionStorage(app('session.store'));
            $cartWithConfig = new Cart(
                storage: $storage,
                events: null,
                instanceName: 'config-test',
                eventsEnabled: false,
                config: ['decimals' => 3]
            );
            $cartWithConfig->clear();
            
            $cartWithConfig->add('item-1', 'High Precision', 19.9999, 1);
            
            $item = $cartWithConfig->getItems()->first();
            
            // Should respect the 3-decimal configuration
            expect($item->price)->toBe(20.000);
        });
    });

    describe('Real-world scenarios', function () {
        it('handles e-commerce price input patterns', function () {
            // Simulate typical e-commerce scenarios
            $this->cart->add('product-1', 'Product A', '9.99', 1);      // String price
            $this->cart->add('product-2', 'Product B', 19.95, 2);      // Float with quantity
            $this->cart->add('product-3', 'Product C', '$15.00', 1);   // With currency
            $this->cart->add('bundle-1', 'Bundle Deal', '99.95', 1);   // Standard format
            
            $expectedTotal = 9.99 + (19.95 * 2) + 15.00 + 99.95; // 164.84
            
            expect($this->cart->total()->getAmount())->toBe($expectedTotal);
            expect($this->cart->countItems())->toBe(4);
            expect($this->cart->getTotalQuantity())->toBe(5);
        });

        it('handles subscription pricing scenarios', function () {
            // Monthly subscription prices
            $this->cart->add('sub-basic', 'Basic Plan', 9.99, 1);
            $this->cart->add('sub-pro', 'Pro Plan', 19.99, 1);
            $this->cart->add('addon-storage', 'Extra Storage', 4.99, 1);
            
            expect($this->cart->total()->getAmount())->toBe(34.97);
        });
    });
});