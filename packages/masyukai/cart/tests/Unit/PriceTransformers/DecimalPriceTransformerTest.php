<?php

declare(strict_types=1);

use MasyukAI\Cart\PriceTransformers\DecimalPriceTransformer;

describe('DecimalPriceTransformer', function () {
    beforeEach(function () {
        $this->transformer = new DecimalPriceTransformer;
    });

    it('stores prices as floats with precision rounding', function () {
        expect($this->transformer->toStorage(19.99))->toBe(19.99);
        expect($this->transformer->toStorage('19.99'))->toBe(19.99);
        expect($this->transformer->toStorage(1999))->toBe(1999.0);
        expect($this->transformer->toStorage('1999'))->toBe(1999.0);
    });

    it('converts values to numeric floats correctly', function () {
        expect($this->transformer->toNumeric(1999))->toBe(1999.0);
        expect($this->transformer->toNumeric(19.99))->toBe(19.99);
        expect($this->transformer->toNumeric('19.99'))->toBe(19.99);
        expect($this->transformer->toNumeric('1999'))->toBe(1999.0);
    });

    it('displays prices correctly with precision formatting', function () {
        expect($this->transformer->toDisplay(19.99))->toBe('19.99');
        expect($this->transformer->toDisplay(19.9))->toBe('19.90');
        expect($this->transformer->toDisplay(20))->toBe('20.00');
        expect($this->transformer->toDisplay('19.99'))->toBe('19.99');
    });

    it('handles edge cases correctly', function () {
        expect($this->transformer->toStorage(0))->toBe(0.0);
        expect($this->transformer->toStorage('0'))->toBe(0.0);
        expect($this->transformer->toStorage(0.01))->toBe(0.01);
        expect($this->transformer->toDisplay(0))->toBe('0.00');
        expect($this->transformer->toDisplay(0.01))->toBe('0.01');
    });

    it('handles negative values correctly', function () {
        expect($this->transformer->toStorage(-19.99))->toBe(-19.99);
        expect($this->transformer->toStorage('-19.99'))->toBe(-19.99);
        expect($this->transformer->toDisplay(-19.99))->toBe('-19.99');
        expect($this->transformer->toNumeric(-19.99))->toBe(-19.99);
    });

    it('rounds correctly to avoid floating point precision issues', function () {
        // Test precise rounding with default 2 decimal places
        expect($this->transformer->toStorage(19.999))->toBe(20.0);
        expect($this->transformer->toStorage(19.995))->toBe(20.0);
        expect($this->transformer->toStorage(19.994))->toBe(19.99);
    });

    it('works with string inputs containing thousands separators', function () {
        expect($this->transformer->toStorage('1,999.99'))->toBe(1999.99);
        expect($this->transformer->toStorage('10,000.00'))->toBe(10000.0);
        expect($this->transformer->toNumeric('1,999.99'))->toBe(1999.99);
        expect($this->transformer->toDisplay('1,999.99'))->toBe('1999.99');
    });

    it('works with different precisions', function () {
        $transformer = new DecimalPriceTransformer('USD', 'en_US', 3);

        expect($transformer->toStorage(19.9999))->toBe(20.0);
        expect($transformer->toStorage(19.9994))->toBe(19.999);
        expect($transformer->toDisplay(19.999))->toBe('19.999');
        expect($transformer->toNumeric(19.999))->toBe(19.999);
    });

    it('handles currency and locale settings', function () {
        $transformer = new DecimalPriceTransformer('EUR', 'de_DE', 2);

        expect($transformer->getCurrency())->toBe('EUR');
        expect($transformer->formatCurrency(19.99))->toContain('19,99'); // German locale uses comma as decimal separator
        expect($transformer->formatCurrency(19.99))->toContain('€');
    });

    it('maintains consistency between storage and display', function () {
        $testPrices = [0.01, 0.99, 1.00, 19.99, 100.00, 999.99];

        foreach ($testPrices as $price) {
            $stored = $this->transformer->toStorage($price);
            $numeric = $this->transformer->toNumeric($stored);

            // Should be exactly equal for decimal transformer
            expect($numeric)->toBe($price);
        }
    });

    it('works correctly in cart calculations', function () {
        // Simulate cart-like calculations
        $price1 = 19.99; // $19.99
        $price2 = 5.50;  // $5.50

        $stored1 = $this->transformer->toStorage($price1); // 19.99
        $stored2 = $this->transformer->toStorage($price2); // 5.50

        $total = $stored1 + $stored2; // 25.49

        expect($this->transformer->toDisplay($total))->toBe('25.49');
        expect($this->transformer->toNumeric($total))->toBe(25.49);
    });

    it('handles large numbers correctly', function () {
        $largePrice = 999999.99;

        expect($this->transformer->toStorage($largePrice))->toBe(999999.99);
        expect($this->transformer->toDisplay($largePrice))->toBe('999999.99');
        expect($this->transformer->toNumeric($largePrice))->toBe(999999.99);
    });

    it('handles very small decimal values', function () {
        expect($this->transformer->toStorage(0.001))->toBe(0.00); // Rounded to 2 decimal places
        expect($this->transformer->toStorage(0.009))->toBe(0.01); // Rounded up
        expect($this->transformer->toDisplay(0.001))->toBe('0.00');
        expect($this->transformer->toDisplay(0.009))->toBe('0.01');
    });

    it('preserves precision when converting between types', function () {
        $originalFloat = 123.45;
        $asString = '123.45';
        $asInt = 123;

        // Float input
        expect($this->transformer->toStorage($originalFloat))->toBe(123.45);
        expect($this->transformer->toDisplay($originalFloat))->toBe('123.45');

        // String input
        expect($this->transformer->toStorage($asString))->toBe(123.45);
        expect($this->transformer->toDisplay($asString))->toBe('123.45');

        // Integer input
        expect($this->transformer->toStorage($asInt))->toBe(123.0);
        expect($this->transformer->toDisplay($asInt))->toBe('123.00');
    });

    it('supports chaining currency changes', function () {
        $transformer = $this->transformer
            ->setCurrency('GBP');

        expect($transformer->getCurrency())->toBe('GBP');
        expect($transformer->formatCurrency(19.99))->toContain('£');
    });
});
