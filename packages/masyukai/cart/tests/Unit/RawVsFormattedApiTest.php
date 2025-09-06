<?php

use MasyukAI\Cart\Facades\Cart;
use MasyukAI\Cart\Models\CartItem;

describe('Raw vs Formatted API Compliance', function () {
    beforeEach(function () {
        $this->cart = Cart::instance('test');
        $this->cart->clear();
        
        // Configure formatting for testing
        config([
            'cart.price_format.decimals' => 2,
            'cart.price_format.decimal_point' => '.',
            'cart.price_format.thousands_separator' => ',',
            'cart.price_format.currency_symbol' => '$',
            'cart.price_format.currency_position' => 'before',
        ]);
    });

    describe('Cart-Level API Methods', function () {
        beforeEach(function () {
            // Add test items
            $this->cart->add('item1', 'Product 1', 100.00, 2); // $200 total
            $this->cart->add('item2', 'Product 2', 150.50, 1); // $150.50 total
            
            // Add conditions
            $this->cart->addDiscount('discount10', '10%'); // 10% cart-level discount
            $this->cart->addTax('tax8', '8%'); // 8% tax
        });

        it('has consistent subtotal methods', function () {
            $rawSubtotal = $this->cart->getRawSubtotal();
            $formattedSubtotal = $this->cart->subtotal();
            
            expect($rawSubtotal)->toBeFloat();
            expect($formattedSubtotal)->toBeString();
            expect($rawSubtotal)->toBe(350.50); // No formatting
            expect($formattedSubtotal)->toBe('$350.50'); // With formatting
        });

        it('has consistent subtotal without conditions methods', function () {
            $rawSubtotal = $this->cart->getRawSubTotalWithoutConditions();
            $formattedSubtotal = $this->cart->subtotalWithoutConditions();
            
            expect($rawSubtotal)->toBeFloat();
            expect($formattedSubtotal)->toBeString();
            expect($rawSubtotal)->toBe(350.50); // Base price
            expect($formattedSubtotal)->toBe('$350.50'); // Formatted base price
        });

        it('has consistent total methods', function () {
            $rawTotal = $this->cart->getRawTotal();
            $formattedTotal = $this->cart->total();
            
            expect($rawTotal)->toBeFloat();
            expect($formattedTotal)->toBeString();
            
            // Calculate expected: $350.50 - 10% + 8% tax = $315.45 + $25.24 = $340.69
            $expectedAfterDiscount = 350.50 * 0.9; // $315.45
            $expectedAfterTax = $expectedAfterDiscount * 1.08; // $340.686
            
            expect($rawTotal)->toBe(round($expectedAfterTax, 2));
            expect($formattedTotal)->toContain('$');
        });

        it('has consistent savings methods', function () {
            $rawSavings = $this->cart->getRawSavings();
            $formattedSavings = $this->cart->savings();
            
            expect($rawSavings)->toBeFloat();
            expect($formattedSavings)->toBeString();
            expect($rawSavings)->toBeGreaterThan(0); // Should have savings from discount
            expect($formattedSavings)->toContain('$');
        });
    });

    describe('Item-Level API Methods', function () {
        beforeEach(function () {
            // Add item with conditions
            $this->cart->add('test-item', 'Test Product', 100.00, 2, [
                'color' => 'blue',
                'size' => 'large'
            ]);
            
            // Add item-level discount
            $item = $this->cart->get('test-item');
            $item = $item->addCondition(
                new \MasyukAI\Cart\Conditions\CartCondition(
                    'item-discount',
                    'discount',
                    'subtotal',
                    '15%'
                )
            );
            $this->cart->update('test-item', $item);
            $this->item = $this->cart->get('test-item');
        });

        it('has consistent single price methods', function () {
            $rawPrice = $this->item->getRawPrice();
            $formattedPrice = $this->item->getPrice();
            
            expect($rawPrice)->toBeFloat();
            expect($formattedPrice)->toBeString();
            expect($rawPrice)->toBe(85.00); // $100 - 15% = $85
            expect($formattedPrice)->toBe('$85.00');
        });

        it('has consistent single price without conditions methods', function () {
            $rawPrice = $this->item->getRawPriceWithoutConditions();
            $formattedPrice = $this->item->getPriceWithoutConditions();
            
            expect($rawPrice)->toBeFloat();
            expect($formattedPrice)->toBeString();
            expect($rawPrice)->toBe(100.00); // Original price
            expect($formattedPrice)->toBe('$100.00');
        });

        it('has consistent price sum methods', function () {
            $rawPriceSum = $this->item->getRawPriceSum();
            $formattedPriceSum = $this->item->getPriceSum();
            
            expect($rawPriceSum)->toBeFloat();
            expect($formattedPriceSum)->toBeString();
            expect($rawPriceSum)->toBe(170.00); // $85 × 2 = $170
            expect($formattedPriceSum)->toBe('$170.00');
        });

        it('has consistent price sum without conditions methods', function () {
            $rawPriceSum = $this->item->getRawPriceSumWithoutConditions();
            $formattedPriceSum = $this->item->getPriceSumWithoutConditions();
            
            expect($rawPriceSum)->toBeFloat();
            expect($formattedPriceSum)->toBeString();
            expect($rawPriceSum)->toBe(200.00); // $100 × 2 = $200
            expect($formattedPriceSum)->toBe('$200.00');
        });

        it('has consistent discount amount methods', function () {
            $rawDiscount = $this->item->getRawDiscountAmount();
            $formattedDiscount = $this->item->getDiscountAmount();
            $aliasDiscount = $this->item->discountAmount();
            
            expect($rawDiscount)->toBeFloat();
            expect($formattedDiscount)->toBeString();
            expect($aliasDiscount)->toBeString();
            expect($rawDiscount)->toBe(30.00); // $200 - $170 = $30
            expect($formattedDiscount)->toBe('$30.00');
            expect($aliasDiscount)->toBe('$30.00');
        });

        it('has consistent subtotal alias methods', function () {
            $subtotal = $this->item->subtotal();
            $priceSum = $this->item->getPriceSum();
            
            expect($subtotal)->toBe($priceSum); // Should be identical
            expect($subtotal)->toBe('$170.00');
        });

        it('has consistent total alias methods', function () {
            $total = $this->item->total();
            $subtotal = $this->item->subtotal();
            
            expect($total)->toBe($subtotal); // Should be identical for items
            expect($total)->toBe('$170.00');
        });
    });

    describe('API Method Naming Conventions', function () {
        it('follows getRaw* pattern for all raw methods', function () {
            $cart = $this->cart;
            $cart->add('test', 'Test', 100.00);
            $item = $cart->get('test');
            
            // Cart raw methods should follow getRaw* pattern
            expect(method_exists($cart, 'getRawSubtotal'))->toBeTrue();
            expect(method_exists($cart, 'getRawTotal'))->toBeTrue();
            expect(method_exists($cart, 'getRawSubTotalWithoutConditions'))->toBeTrue();
            expect(method_exists($cart, 'getRawSavings'))->toBeTrue();
            
            // Item raw methods should follow getRaw* pattern
            expect(method_exists($item, 'getRawPrice'))->toBeTrue();
            expect(method_exists($item, 'getRawPriceWithoutConditions'))->toBeTrue();
            expect(method_exists($item, 'getRawPriceSum'))->toBeTrue();
            expect(method_exists($item, 'getRawPriceSumWithoutConditions'))->toBeTrue();
            expect(method_exists($item, 'getRawDiscountAmount'))->toBeTrue();
        });

        it('has corresponding formatted methods for all raw methods', function () {
            $cart = $this->cart;
            $cart->add('test', 'Test', 100.00);
            $item = $cart->get('test');
            
            // Cart formatted methods
            expect(method_exists($cart, 'subtotal'))->toBeTrue();
            expect(method_exists($cart, 'total'))->toBeTrue();
            expect(method_exists($cart, 'subtotalWithoutConditions'))->toBeTrue();
            expect(method_exists($cart, 'savings'))->toBeTrue();
            
            // Item formatted methods
            expect(method_exists($item, 'getPrice'))->toBeTrue();
            expect(method_exists($item, 'getPriceWithoutConditions'))->toBeTrue();
            expect(method_exists($item, 'getPriceSum'))->toBeTrue();
            expect(method_exists($item, 'getPriceSumWithoutConditions'))->toBeTrue();
            expect(method_exists($item, 'getDiscountAmount'))->toBeTrue();
            expect(method_exists($item, 'discountAmount'))->toBeTrue();
        });
    });

    describe('Return Type Consistency', function () {
        beforeEach(function () {
            $this->cart->add('test-item', 'Test Product', 99.99, 3);
            $this->item = $this->cart->get('test-item');
        });

        it('ensures all raw methods return float', function () {
            // Cart raw methods
            expect($this->cart->getRawSubtotal())->toBeFloat();
            expect($this->cart->getRawTotal())->toBeFloat();
            expect($this->cart->getRawSubTotalWithoutConditions())->toBeFloat();
            expect($this->cart->getRawSavings())->toBeFloat();
            
            // Item raw methods
            expect($this->item->getRawPrice())->toBeFloat();
            expect($this->item->getRawPriceWithoutConditions())->toBeFloat();
            expect($this->item->getRawPriceSum())->toBeFloat();
            expect($this->item->getRawPriceSumWithoutConditions())->toBeFloat();
            expect($this->item->getRawDiscountAmount())->toBeFloat();
        });

        it('ensures all formatted methods return string when formatting is enabled', function () {
            // Enable formatting
            config(['cart.price_format.enabled' => true]);
            
            // Cart formatted methods
            expect($this->cart->subtotal())->toBeString();
            expect($this->cart->total())->toBeString();
            expect($this->cart->subtotalWithoutConditions())->toBeString();
            expect($this->cart->savings())->toBeString();
            
            // Item formatted methods
            expect($this->item->getPrice())->toBeString();
            expect($this->item->getPriceWithoutConditions())->toBeString();
            expect($this->item->getPriceSum())->toBeString();
            expect($this->item->getPriceSumWithoutConditions())->toBeString();
            expect($this->item->getDiscountAmount())->toBeString();
            expect($this->item->discountAmount())->toBeString();
        });
    });

    describe('Complex Calculation Scenarios', function () {
        it('maintains consistency with multiple conditions', function () {
            // Complex scenario: multiple items, multiple conditions
            $this->cart->add('expensive-item', 'Expensive Product', 1000.00, 1);
            $this->cart->add('medium-item', 'Medium Product', 500.00, 2);
            $this->cart->add('cheap-item', 'Cheap Product', 50.00, 5);
            
            // Add various conditions
            $this->cart->addDiscount('bulk-discount', '15%');
            $this->cart->addFee('processing-fee', '25.00');
            $this->cart->addTax('vat', '20%');
            
            // Verify raw calculations match formatted calculations
            $rawSubtotal = $this->cart->getRawSubtotal();
            $rawTotal = $this->cart->getRawTotal();
            $rawSavings = $this->cart->getRawSavings();
            
            $formattedSubtotal = $this->cart->subtotal();
            $formattedTotal = $this->cart->total();
            $formattedSavings = $this->cart->savings();
            
            // Raw values should be numeric
            expect($rawSubtotal)->toBeFloat();
            expect($rawTotal)->toBeFloat();
            expect($rawSavings)->toBeFloat();
            
            // Formatted values should contain currency symbol
            expect($formattedSubtotal)->toContain('$');
            expect($formattedTotal)->toContain('$');
            expect($formattedSavings)->toContain('$');
            
            // Verify mathematical relationships
            expect($rawSavings)->toBeGreaterThan(0); // Should have savings from discount
            expect($rawTotal)->toBeLessThan($rawSubtotal + 25.00); // Total should be less due to discount (before tax)
        });
    });
});