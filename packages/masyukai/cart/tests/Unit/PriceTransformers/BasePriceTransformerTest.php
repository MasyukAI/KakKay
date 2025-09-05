<?php

declare(strict_types=1);

use MasyukAI\Cart\PriceTransformers\BasePriceTransformer;
use MasyukAI\Cart\Contracts\PriceTransformerInterface;

class TestBasePriceTransformer extends BasePriceTransformer
{
    public function toDisplay(int|float|string $price): string
    {
        return number_format($this->toNumeric($price), $this->precision);
    }

    public function toStorage(int|float|string $price): float
    {
        return $this->roundToPrecision((float) $price);
    }

    public function toNumeric(int|float|string $price): float
    {
        return (float) $price;
    }

    // Expose protected method for testing
    public function testRoundToPrecision(float $value): float
    {
        return $this->roundToPrecision($value);
    }
}

describe('BasePriceTransformer', function () {
    it('can be instantiated with default parameters', function () {
        $transformer = new TestBasePriceTransformer();
        
        expect($transformer)->toBeInstanceOf(PriceTransformerInterface::class);
        expect($transformer->getCurrency())->toBe('USD');
    });

    it('can be instantiated with custom parameters', function () {
        $transformer = new TestBasePriceTransformer('EUR', 'de_DE', 3);
        
        expect($transformer->getCurrency())->toBe('EUR');
    });

    it('can format currency with default currency', function () {
        $transformer = new TestBasePriceTransformer();
        
        $result = $transformer->formatCurrency(19.99);
        expect($result)->toContain('19.99');
        expect($result)->toContain('$'); // USD symbol
    });

    it('can format currency with custom currency', function () {
        $transformer = new TestBasePriceTransformer('USD', 'en_US');
        
        $result = $transformer->formatCurrency(19.99, 'EUR');
        expect($result)->toContain('19.99');
        expect($result)->toContain('€'); // EUR symbol
    });

    it('can set and get currency', function () {
        $transformer = new TestBasePriceTransformer();
        
        $result = $transformer->setCurrency('GBP');
        expect($result)->toBe($transformer); // fluent interface
        expect($transformer->getCurrency())->toBe('GBP');
    });

    it('can handle different price formats in formatCurrency', function () {
        $transformer = new TestBasePriceTransformer();
        
        expect($transformer->formatCurrency(19))->toContain('19.00');
        expect($transformer->formatCurrency('19.99'))->toContain('19.99');
        expect($transformer->formatCurrency(19.999))->toContain('20.00'); // Rounded to 2 precision
    });

    it('rounds to precision correctly', function () {
        $transformer = new TestBasePriceTransformer('USD', 'en_US', 2);
        
        expect($transformer->testRoundToPrecision(19.999))->toBe(20.0);
        expect($transformer->testRoundToPrecision(19.994))->toBe(19.99);
        expect($transformer->testRoundToPrecision(19.995))->toBe(20.0);
    });

    it('rounds to custom precision correctly', function () {
        $transformer = new TestBasePriceTransformer('USD', 'en_US', 3);
        
        expect($transformer->testRoundToPrecision(19.9999))->toBe(20.0);
        expect($transformer->testRoundToPrecision(19.9994))->toBe(19.999);
        expect($transformer->testRoundToPrecision(19.9995))->toBe(20.0);
    });

    it('handles zero precision', function () {
        $transformer = new TestBasePriceTransformer('USD', 'en_US', 0);
        
        expect($transformer->testRoundToPrecision(19.999))->toBe(20.0);
        expect($transformer->testRoundToPrecision(19.4))->toBe(19.0);
        expect($transformer->testRoundToPrecision(19.5))->toBe(20.0);
    });

    it('formats currency with different locales', function () {
        $transformer = new TestBasePriceTransformer('EUR', 'de_DE');
        
        $result = $transformer->formatCurrency(1234.56);
        expect($result)->toContain('1.234,56'); // German formatting
        expect($result)->toContain('€');
    });
});
