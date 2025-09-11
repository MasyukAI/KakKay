<?php

declare(strict_types=1);

use MasyukAI\Cart\PriceTransformers\DecimalPriceTransformer;

/**
 * Unit tests for DecimalPriceTransformer
 * This transformer stores and retrieves prices as decimal floating point values
 */
describe('DecimalPriceTransformer', function () {
    beforeEach(function () {
        $this->transformer = new DecimalPriceTransformer();
    });

    describe('Storage Conversion (toStorage)', function () {
        it('converts numeric prices to float with default precision', function () {
            expect($this->transformer->toStorage(19.99))->toBe(19.99);
            expect($this->transformer->toStorage(100))->toBe(100.0);
            expect($this->transformer->toStorage(0))->toBe(0.0);
        });

        it('handles string prices with currency symbols', function () {
            expect($this->transformer->toStorage('19.99'))->toBe(19.99);
            expect($this->transformer->toStorage('$25.50'))->toBe(25.50);
            expect($this->transformer->toStorage('€15.75'))->toBe(15.75);
            expect($this->transformer->toStorage('£10.25'))->toBe(10.25);
        });

        it('handles string prices with thousands separators', function () {
            expect($this->transformer->toStorage('1,999.99'))->toBe(1999.99);
            expect($this->transformer->toStorage('10,000.50'))->toBe(10000.50);
        });

        it('applies precision rounding correctly', function () {
            $transformer = new DecimalPriceTransformer(precision: 2);
            
            expect($transformer->toStorage(19.999))->toBe(20.0);
            expect($transformer->toStorage(19.991))->toBe(19.99);
            expect($transformer->toStorage(19.995))->toBe(20.0);
        });

        it('handles edge cases', function () {
            expect($this->transformer->toStorage(0.1))->toBe(0.1);
            expect($this->transformer->toStorage(0.01))->toBe(0.01);
            expect($this->transformer->toStorage(999999.99))->toBe(999999.99);
        });
    });

    describe('Display Conversion (fromStorage)', function () {
        it('returns storage values as-is for decimal transformer', function () {
            expect($this->transformer->fromStorage(19.99))->toBe(19.99);
            expect($this->transformer->fromStorage(100.0))->toBe(100.0);
            expect($this->transformer->fromStorage(0.0))->toBe(0.0);
        });

        it('handles integer input correctly', function () {
            expect($this->transformer->fromStorage(100))->toBe(100.0);
            expect($this->transformer->fromStorage(0))->toBe(0.0);
        });

        it('maintains precision from storage', function () {
            expect($this->transformer->fromStorage(19.99))->toBe(19.99);
            expect($this->transformer->fromStorage(0.01))->toBe(0.01);
            expect($this->transformer->fromStorage(999999.99))->toBe(999999.99);
        });
    });

    describe('Round-trip Conversion', function () {
        it('maintains value integrity through full conversion cycle', function () {
            $originalValues = [19.99, 0.01, 100.0, 0.0, 999.99];
            
            foreach ($originalValues as $original) {
                $stored = $this->transformer->toStorage($original);
                $retrieved = $this->transformer->fromStorage($stored);
                
                expect($retrieved)->toBe($original);
            }
        });

        it('handles string input round-trip correctly', function () {
            $stringInputs = ['19.99', '$25.50', '1,999.99'];
            $expectedOutputs = [19.99, 25.50, 1999.99];
            
            foreach ($stringInputs as $index => $input) {
                $stored = $this->transformer->toStorage($input);
                $retrieved = $this->transformer->fromStorage($stored);
                
                expect($retrieved)->toBe($expectedOutputs[$index]);
            }
        });
    });

    describe('Configuration', function () {
        it('uses specified precision correctly', function () {
            $transformer = new DecimalPriceTransformer(precision: 4);
            
            expect($transformer->getPrecision())->toBe(4);
            expect($transformer->toStorage(19.99999))->toBe(20.0);
            expect($transformer->toStorage(19.99994))->toBe(19.9999);
        });

        it('handles zero precision', function () {
            $transformer = new DecimalPriceTransformer(precision: 0);
            
            expect($transformer->toStorage(19.99))->toBe(20.0);
            expect($transformer->toStorage(19.49))->toBe(19.0);
        });
    });
});
