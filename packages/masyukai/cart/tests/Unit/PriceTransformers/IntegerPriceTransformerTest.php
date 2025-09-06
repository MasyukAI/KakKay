<?php

declare(strict_types=1);

use MasyukAI\Cart\PriceTransformers\IntegerPriceTransformer;

describe('IntegerPriceTransformer', function () {
    beforeEach(function () {
        $this->transformer = new IntegerPriceTransformer;
    });

    it('stores prices as integers in cents', function () {
        expect($this->transformer->toStorage(19.99))->toBe(1999);
        expect($this->transformer->toStorage('19.99'))->toBe(1999);
        expect($this->transformer->toStorage(1999))->toBe(199900); // Integer treated as display value
        expect($this->transformer->toStorage('1999'))->toBe(199900); // String treated as display value
    });

    it('converts integers to numeric correctly', function () {
        expect($this->transformer->toNumeric(1999))->toBe(19.99);
        expect($this->transformer->toNumeric('1999'))->toBe(19.99);
        expect($this->transformer->toNumeric(0))->toBe(0.0);
        expect($this->transformer->toNumeric(100))->toBe(1.0);
    });

    it('displays prices correctly', function () {
        expect($this->transformer->toDisplay(1999))->toBe('19.99');
        expect($this->transformer->toDisplay('1999'))->toBe('19.99');
        expect($this->transformer->toDisplay(0))->toBe('0.00');
        expect($this->transformer->toDisplay(100))->toBe('1.00');
        expect($this->transformer->toDisplay(50))->toBe('0.50');
    });

    it('handles edge cases', function () {
        // Zero values
        expect($this->transformer->toStorage(0))->toBe(0);
        expect($this->transformer->toDisplay(0))->toBe('0.00');

        // Small amounts
        expect($this->transformer->toStorage(0.01))->toBe(1);
        expect($this->transformer->toDisplay(1))->toBe('0.01');

        // Large amounts
        expect($this->transformer->toStorage(999.99))->toBe(99999);
        expect($this->transformer->toDisplay(99999))->toBe('999.99');
    });

    it('handles negative values', function () {
        expect($this->transformer->toStorage(-19.99))->toBe(-1999);
        expect($this->transformer->toDisplay(-1999))->toBe('-19.99');
        expect($this->transformer->toNumeric(-1999))->toBe(-19.99);
    });

    it('rounds correctly to avoid floating point issues', function () {
        // Test cases that might cause floating point precision issues
        expect($this->transformer->toStorage(19.995))->toBe(2000); // Should round up
        expect($this->transformer->toStorage(19.994))->toBe(1999); // Should round down

        // Display should always show exactly 2 decimal places
        expect($this->transformer->toDisplay(1999.5))->toBe('19.99'); // Truncated in display
    });

    it('works with string inputs containing decimals', function () {
        expect($this->transformer->toStorage('19.99'))->toBe(1999);
        expect($this->transformer->toStorage('0.50'))->toBe(50);
        expect($this->transformer->toStorage('1000.00'))->toBe(100000);
    });

    it('works with different precisions', function () {
        $transformer = new IntegerPriceTransformer('USD', 'en_US', 3);

        expect($transformer->toStorage(19.999))->toBe(19999);
        expect($transformer->toDisplay(19999))->toBe('19.999');
        expect($transformer->toNumeric(19999))->toBe(19.999);
    });

    it('handles currency and locale settings', function () {
        $transformer = new IntegerPriceTransformer('EUR', 'de_DE', 2);

        expect($transformer->getCurrency())->toBe('EUR');
        // Note: locale and precision are protected properties, not accessible via getters
    });

    it('maintains consistency between storage and display', function () {
        $testPrices = [0.01, 0.99, 1.00, 19.99, 100.00, 999.99];

        foreach ($testPrices as $price) {
            $stored = $this->transformer->toStorage($price);
            $numeric = $this->transformer->toNumeric($stored);

            // Should round-trip correctly (within floating point precision)
            expect(abs($numeric - $price))->toBeLessThan(0.01);
        }
    });

    it('works correctly in cart calculations', function () {
        // Simulate cart-like calculations
        $price1 = 19.99; // $19.99
        $price2 = 5.50;  // $5.50

        $stored1 = $this->transformer->toStorage($price1); // 1999
        $stored2 = $this->transformer->toStorage($price2); // 550

        $total = $stored1 + $stored2; // 2549

        expect($this->transformer->toDisplay($total))->toBe('25.49');
        expect($this->transformer->toNumeric($total))->toBe(25.49);
    });
});
