<?php

declare(strict_types=1);

use MasyukAI\Cart\PriceTransformers\IntegerPriceTransformer;

/**
 * Unit tests for IntegerPriceTransformer
 * This transformer stores prices as integers (cents) and converts them back to decimals for display
 */
describe('IntegerPriceTransformer', function () {
    beforeEach(function () {
        $this->transformer = new IntegerPriceTransformer();
    });

    describe('Storage Conversion (toStorage)', function () {
        it('converts decimal prices to cents (integer storage)', function () {
            expect($this->transformer->toStorage(19.99))->toBe(1999);
            expect($this->transformer->toStorage(0.99))->toBe(99);
            expect($this->transformer->toStorage(100.00))->toBe(10000);
            expect($this->transformer->toStorage(0.01))->toBe(1);
        });

        it('handles string prices with currency symbols', function () {
            expect($this->transformer->toStorage('19.99'))->toBe(1999);
            expect($this->transformer->toStorage('$25.50'))->toBe(2550);
            expect($this->transformer->toStorage('€15.75'))->toBe(1575);
            expect($this->transformer->toStorage('£10.25'))->toBe(1025);
        });

        it('handles string prices with thousands separators', function () {
            expect($this->transformer->toStorage('1,999.99'))->toBe(199999);
            expect($this->transformer->toStorage('10,000.00'))->toBe(1000000);
            expect($this->transformer->toStorage('1,234.56'))->toBe(123456);
        });

        it('rounds correctly when converting to cents', function () {
            expect($this->transformer->toStorage(19.999))->toBe(2000); // Rounds up
            expect($this->transformer->toStorage(19.991))->toBe(1999); // Rounds down
            expect($this->transformer->toStorage(19.995))->toBe(2000); // Rounds up
        });

        it('handles integer input', function () {
            expect($this->transformer->toStorage(20))->toBe(2000);
            expect($this->transformer->toStorage(0))->toBe(0);
            expect($this->transformer->toStorage(1))->toBe(100);
        });

        it('handles edge cases', function () {
            expect($this->transformer->toStorage(0.001))->toBe(0); // Less than 1 cent
            expect($this->transformer->toStorage(0.005))->toBe(1); // Rounds to 1 cent
            expect($this->transformer->toStorage(0.004))->toBe(0); // Rounds to 0 cents
        });
    });

    describe('Display Conversion (fromStorage)', function () {
        it('converts cents back to decimal prices', function () {
            expect($this->transformer->fromStorage(1999))->toBe(19.99);
            expect($this->transformer->fromStorage(99))->toBe(0.99);
            expect($this->transformer->fromStorage(10000))->toBe(100.00);
            expect($this->transformer->fromStorage(1))->toBe(0.01);
        });

        it('handles zero and large values', function () {
            expect($this->transformer->fromStorage(0))->toBe(0.00);
            expect($this->transformer->fromStorage(1000000))->toBe(10000.00);
            expect($this->transformer->fromStorage(999999))->toBe(9999.99);
        });

        it('handles float storage values (edge case)', function () {
            expect($this->transformer->fromStorage(1999.0))->toBe(19.99);
            expect($this->transformer->fromStorage(100.0))->toBe(1.00);
        });
    });

    describe('Precision Configuration', function () {
        it('uses default precision of 2 decimal places', function () {
            expect($this->transformer->getPrecision())->toBe(2);
        });

        it('can be configured with custom precision', function () {
            $transformer = new IntegerPriceTransformer(precision: 3);
            
            expect($transformer->getPrecision())->toBe(3);
            
            // With 3 decimal places, multiplier is 1000
            expect($transformer->toStorage(19.999))->toBe(20000); // 19.999 * 1000 rounded
            expect($transformer->fromStorage(19999))->toBe(19.999);
        });

        it('handles zero precision (whole numbers only)', function () {
            $transformer = new IntegerPriceTransformer(precision: 0);
            
            expect($transformer->getPrecision())->toBe(0);
            expect($transformer->toStorage(19.99))->toBe(20); // Rounded to whole number
            expect($transformer->fromStorage(20))->toBe(20.0);
        });

        it('handles high precision scenarios', function () {
            $transformer = new IntegerPriceTransformer(precision: 4);
            
            expect($transformer->toStorage(19.9999))->toBe(199999); // 19.9999 * 10000
            expect($transformer->fromStorage(199999))->toBe(19.9999);
        });
    });

    describe('Round-trip Conversion', function () {
        it('maintains precision through storage and retrieval cycle', function () {
            $prices = [9.99, 19.95, 99.00, 0.99, 149.50, 0.01, 999.99];
            
            foreach ($prices as $price) {
                $stored = $this->transformer->toStorage($price);
                $retrieved = $this->transformer->fromStorage($stored);
                expect($retrieved)->toBe($price);
            }
        });

        it('handles edge cases in round-trip conversion', function () {
            $edgeCases = [0.00, 0.01, 0.99, 1.00, 9.99, 10.00, 99.99, 100.00];
            
            foreach ($edgeCases as $price) {
                $stored = $this->transformer->toStorage($price);
                $retrieved = $this->transformer->fromStorage($stored);
                expect($retrieved)->toBe($price);
            }
        });
    });

    describe('Real-world Scenarios', function () {
        it('handles typical e-commerce pricing', function () {
            // Test common price points
            $commonPrices = [
                ['input' => 9.99, 'storage' => 999, 'display' => 9.99],
                ['input' => 19.95, 'storage' => 1995, 'display' => 19.95],
                ['input' => 99.00, 'storage' => 9900, 'display' => 99.00],
                ['input' => 149.50, 'storage' => 14950, 'display' => 149.50],
            ];
            
            foreach ($commonPrices as $test) {
                expect($this->transformer->toStorage($test['input']))->toBe($test['storage']);
                expect($this->transformer->fromStorage($test['storage']))->toBe($test['display']);
            }
        });

        it('handles subscription pricing accurately', function () {
            // Monthly subscription prices that need exact cent precision
            $subscriptionPrices = [4.99, 9.99, 19.99, 49.99, 99.99];
            
            foreach ($subscriptionPrices as $price) {
                $stored = $this->transformer->toStorage($price);
                $retrieved = $this->transformer->fromStorage($stored);
                expect($retrieved)->toBe($price);
            }
        });

        it('handles bulk pricing calculations', function () {
            $unitPrice = 0.99;
            $quantity = 1000;
            $totalPrice = $unitPrice * $quantity; // 990.00
            
            $stored = $this->transformer->toStorage($totalPrice);
            expect($stored)->toBe(99000); // 990.00 * 100
            
            $retrieved = $this->transformer->fromStorage($stored);
            expect($retrieved)->toBe(990.00);
        });

        it('prevents floating point precision errors in storage', function () {
            // These calculations might cause floating point issues with decimal storage
            $price1 = 0.1;
            $price2 = 0.2;
            $sum = $price1 + $price2; // Might be 0.30000000000000004 in floating point
            
            $stored = $this->transformer->toStorage($sum);
            expect($stored)->toBe(30); // Exactly 30 cents
            
            $retrieved = $this->transformer->fromStorage($stored);
            expect($retrieved)->toBe(0.30); // Exactly 0.30
        });
    });

    describe('Error Handling', function () {
        it('handles very large numbers appropriately', function () {
            $largePrice = 999999.99;
            $stored = $this->transformer->toStorage($largePrice);
            $retrieved = $this->transformer->fromStorage($stored);
            
            expect($retrieved)->toBe($largePrice);
        });

        it('handles negative values if they occur', function () {
            // While negative prices are unusual, the transformer should handle them
            $stored = $this->transformer->toStorage(-10.50);
            expect($stored)->toBe(-1050);
            
            $retrieved = $this->transformer->fromStorage(-1050);
            expect($retrieved)->toBe(-10.50);
        });
    });
});