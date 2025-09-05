<?php

declare(strict_types=1);

use MasyukAI\Cart\PriceTransformers\LocalizedPriceTransformer;

describe('LocalizedPriceTransformer', function () {
    it('can be instantiated with default parameters', function () {
        $transformer = new LocalizedPriceTransformer();
        
        expect($transformer)->toBeInstanceOf(LocalizedPriceTransformer::class);
    });

    it('can be instantiated with custom parameters', function () {
        $transformer = new LocalizedPriceTransformer(
            currency: 'EUR',
            locale: 'de_DE',
            precision: 3,
            decimalSeparator: ',',
            thousandsSeparator: '.'
        );
        
        expect($transformer)->toBeInstanceOf(LocalizedPriceTransformer::class);
    });

    it('can format prices for display with US formatting', function () {
        $transformer = new LocalizedPriceTransformer();
        
        expect($transformer->toDisplay(1999.50))->toBe('1,999.50');
        expect($transformer->toDisplay(1234567.89))->toBe('1,234,567.89');
        expect($transformer->toDisplay(99))->toBe('99.00');
    });

    it('can format prices for display with German formatting', function () {
        $transformer = new LocalizedPriceTransformer(
            currency: 'EUR',
            locale: 'de_DE',
            precision: 2,
            decimalSeparator: ',',
            thousandsSeparator: '.'
        );
        
        expect($transformer->toDisplay(1999.50))->toBe('1.999,50');
        expect($transformer->toDisplay(1234567.89))->toBe('1.234.567,89');
        expect($transformer->toDisplay(99))->toBe('99,00');
    });

    it('can convert localized prices to storage format', function () {
        $transformer = new LocalizedPriceTransformer();
        
        expect($transformer->toStorage('1,999.50'))->toBe(1999.50);
        expect($transformer->toStorage(1999.50))->toBe(1999.50);
    });

    it('can convert US formatted prices to storage format', function () {
        $transformer = new LocalizedPriceTransformer(
            decimalSeparator: '.',
            thousandsSeparator: ','
        );
        
        expect($transformer->toStorage('1,999.50'))->toBe(1999.50);
        expect($transformer->toStorage('12,345.67'))->toBe(12345.67);
    });

    it('can handle numeric input for storage conversion', function () {
        $transformer = new LocalizedPriceTransformer();
        
        expect($transformer->toStorage(1999.50))->toBe(1999.50);
        expect($transformer->toStorage(123))->toBe(123.0);
    });

    it('can convert prices to numeric format', function () {
        $transformer = new LocalizedPriceTransformer();
        
        expect($transformer->toNumeric('1,999.50'))->toBe(1999.50);
        expect($transformer->toNumeric(1999.50))->toBe(1999.50);
    });

    it('formats currency correctly', function () {
        $transformer = new LocalizedPriceTransformer();
        
        expect($transformer->formatCurrency(19.99))->toContain('19.99');
    });

    it('can set and get currency', function () {
        $transformer = new LocalizedPriceTransformer();
        
        $transformer->setCurrency('EUR');
        expect($transformer->getCurrency())->toBe('EUR');
    });

    it('respects precision settings', function () {
        $transformer = new LocalizedPriceTransformer(precision: 0);
        
        expect($transformer->toDisplay(19.99))->toBe('20');
    });

    it('respects precision settings', function () {
        $transformer = new LocalizedPriceTransformer(precision: 3);
        
        expect($transformer->toDisplay(19.999))->toBe('19.999');
    });

    it('handles edge cases with empty or special values', function () {
        $transformer = new LocalizedPriceTransformer();
        
        expect($transformer->toDisplay(0))->toBe('0.00');
        expect($transformer->toDisplay(''))->toBe('0.00');
        expect($transformer->toStorage('0'))->toBe(0.0);
    });
});
