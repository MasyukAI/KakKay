<?php

declare(strict_types=1);

use MasyukAI\Cart\Facades\Cart;
use MasyukAI\Cart\Support\CartMoney;

describe('Laravel Money Integration', function () {
    beforeEach(function () {
        Cart::clear();
    });

    it('returns CartMoney objects for all price operations', function () {
        // Add items to cart
        Cart::add('item1', 'Test Item 1', 25.99, 2);
        Cart::add('item2', 'Test Item 2', 15.50, 3);

        // All cart methods should return CartMoney objects
        expect(Cart::subtotal())->toBeInstanceOf(CartMoney::class);
        expect(Cart::total())->toBeInstanceOf(CartMoney::class);
        expect(Cart::subtotalWithoutConditions())->toBeInstanceOf(CartMoney::class);
        expect(Cart::totalWithoutConditions())->toBeInstanceOf(CartMoney::class);
        expect(Cart::savings())->toBeInstanceOf(CartMoney::class);
    });

    it('allows chainable operations on returned money objects', function () {
        Cart::add('chainable', 'Chainable Item', 100.00, 1);

        $subtotal = Cart::subtotal();

        // Test chaining operations
        $withTax = $subtotal->multiply(1.08);
        expect($withTax)->toBeInstanceOf(CartMoney::class);
        expect($withTax->getAmount())->toBe(108.0);

        $discounted = $subtotal->multiply(0.9);
        expect($discounted->getAmount())->toBe(90.0);

        // Test another multiplication operation
        $multiplied = $subtotal->multiply(1.085);
        expect($multiplied)->toBeInstanceOf(CartMoney::class);
        expect($multiplied->getAmount())->toBe(108.5);
    });

    it('provides locale-aware formatting through configuration', function () {
        config(['cart.display.locale' => 'en_US']);
        config(['cart.display.currency_symbol' => true]);

        Cart::add('format-test', 'Format Test Item', 1234.56, 1);

        $total = Cart::total();

        // Test different formatting options
        expect($total->format())->toBeString();
        expect($total->formatSimple())->toBeString();

        // Test string casting (uses default formatting)
        expect((string) $total)->toBeString();
    });

    it('supports different currencies through configuration', function () {
        config(['cart.money.default_currency' => 'EUR']);

        Cart::add('euro-item', 'Euro Item', 50.00, 1);

        $total = Cart::total();
        expect($total->getCurrency())->toBe('EUR');

        // Change back to USD for other tests
        config(['cart.money.default_currency' => 'USD']);
    });

    it('provides backward compatibility through formatted methods', function () {
        Cart::add('compat-item', 'Compatibility Item', 25.99, 2);

        // These methods return formatted strings for backward compatibility
        expect(Cart::subtotalFormatted())->toBeString();
        expect(Cart::totalFormatted())->toBeString();
        expect(Cart::savingsFormatted())->toBeString();

        // Raw methods return floats for backward compatibility
        expect(Cart::getRawSubtotal())->toBeFloat();
        expect(Cart::getRawTotal())->toBeFloat();
    });

    it('handles Money comparisons correctly', function () {
        Cart::add('compare1', 'Compare Item 1', 25.00, 1);
        Cart::add('compare2', 'Compare Item 2', 30.00, 1);

        $total1 = CartMoney::fromAmount(25.00);
        $total2 = CartMoney::fromAmount(30.00);
        $total3 = CartMoney::fromAmount(25.00);

        // Test comparison methods
        expect($total2->greaterThan($total1))->toBeTrue();
        expect($total1->lessThan($total2))->toBeTrue();
        expect($total1->equals($total3))->toBeTrue();
        expect($total1->equals($total2))->toBeFalse();
    });

    it('calculates savings correctly with Money objects', function () {
        Cart::add('savings-item', 'Savings Item', 100.00, 1);

        // Add a discount condition
        Cart::addCondition(new \MasyukAI\Cart\Conditions\CartCondition(
            'discount',
            'discount',
            'subtotal',
            '-20%'
        ));

        $savings = Cart::savings();
        expect($savings)->toBeInstanceOf(CartMoney::class);
        expect($savings->getAmount())->toBe(20.0); // 20% of 100

        $subtotalWithoutConditions = Cart::subtotalWithoutConditions();
        $total = Cart::total();

        // Verify savings calculation: without conditions - with conditions
        $expectedSavings = $subtotalWithoutConditions->subtract($total);
        expect($savings->equals($expectedSavings))->toBeTrue();
    });

    it('supports arithmetic operations between cart totals', function () {
        Cart::add('math1', 'Math Item 1', 50.00, 1);
        Cart::add('math2', 'Math Item 2', 30.00, 1);

        $subtotal = Cart::subtotal(); // Should be 80.00

        // Test arithmetic with other Money objects
        $additionalAmount = CartMoney::fromAmount(20.00);
        $combined = $subtotal->add($additionalAmount);

        expect($combined->getAmount())->toBe(100.0);

        $reduced = $subtotal->subtract($additionalAmount);
        expect($reduced->getAmount())->toBe(60.0);

        $doubled = $subtotal->multiply(2);
        expect($doubled->getAmount())->toBe(160.0);

        $halved = $subtotal->divide(2);
        expect($halved->getAmount())->toBe(40.0);
    });

    it('maintains precision in complex calculations', function () {
        // Add items with problematic floating point values
        Cart::add('precision1', 'Precision Item 1', 0.1, 3);
        Cart::add('precision2', 'Precision Item 2', 0.2, 1);

        $subtotal = Cart::subtotal();

        // This should be exactly 0.5 without floating point errors
        expect($subtotal->getAmount())->toBe(0.5);

        // Test with percentage calculations
        $withTax = $subtotal->multiply(1.08625); // Complex tax rate
        expect($withTax)->toBeInstanceOf(CartMoney::class);

        // Verify the result maintains precision
        expect($withTax->getAmount())->toBeFloat();
        expect($withTax->getAmount())->toBeGreaterThan(0.5);
    });

    it('integrates seamlessly with cart conditions', function () {
        Cart::add('condition-item', 'Condition Item', 100.00, 1);

        // Add multiple conditions
        Cart::addCondition(new \MasyukAI\Cart\Conditions\CartCondition(
            'tax',
            'tax',
            'subtotal',
            '+8.25%'
        ));

        Cart::addCondition(new \MasyukAI\Cart\Conditions\CartCondition(
            'shipping',
            'shipping',
            'subtotal',
            '+15.00'
        ));

        $subtotal = Cart::subtotal();
        $total = Cart::total();

        // Verify both are Money objects
        expect($subtotal)->toBeInstanceOf(CartMoney::class);
        expect($total)->toBeInstanceOf(CartMoney::class);

        // Total should be greater than subtotal due to conditions
        expect($total->greaterThan($subtotal))->toBeTrue();

        // Test the actual calculation
        // 100 + 8.25% + 15 = 100 + 8.25 + 15 = 123.25
        expect($total->getAmount())->toBe(123.25);
    });
});
