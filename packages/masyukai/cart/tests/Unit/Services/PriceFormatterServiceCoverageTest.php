<?php

declare(strict_types=1);

use MasyukAI\Cart\PriceTransformers\DecimalPriceTransformer;
use MasyukAI\Cart\PriceTransformers\IntegerPriceTransformer;
use MasyukAI\Cart\Services\PriceFormatterService;

describe('PriceFormatterService Additional Coverage', function () {
    it('can be instantiated with default transformer', function () {
        $service = new PriceFormatterService;

        expect($service)->toBeInstanceOf(PriceFormatterService::class);
    });

    it('can be instantiated with custom transformer', function () {
        $transformer = new IntegerPriceTransformer;
        $service = new PriceFormatterService($transformer);

        expect($service)->toBeInstanceOf(PriceFormatterService::class);
    });

    it('can set and use different transformers', function () {
        $service = new PriceFormatterService;
        $transformer = new IntegerPriceTransformer;

        $service->setTransformer($transformer);

        expect($service->format(19.99))->toBe('19.99'); // PriceFormatterService doesn't change transformer behavior
    });

    it('can format prices using format method', function () {
        $service = new PriceFormatterService;

        expect($service->format(19.99))->toBe('19.99');
        expect($service->format('19.99'))->toBe('19.99');
        expect($service->format(0))->toBe('0.00');
    });

    it('can format input prices using formatInput method', function () {
        $service = new PriceFormatterService;

        expect($service->formatInput(19.99))->toBe('19.99');
        expect($service->formatInput('19.99'))->toBe('19.99');
        expect($service->formatInput('19,99'))->toBe('1999.00'); // Comma treated as thousands separator
    });

    it('can format currency using formatCurrency method', function () {
        $service = new PriceFormatterService;

        expect($service->formatCurrency(19.99))->toContain('19.99');
        expect($service->formatCurrency(19.99, 'EUR'))->toContain('19.99');
    });

    it('can format currency with null currency parameter', function () {
        $service = new PriceFormatterService;

        expect($service->formatCurrency(19.99, null))->toContain('19.99');
    });

    it('can normalize prices using normalize method', function () {
        $service = new PriceFormatterService;

        expect($service->normalize('19.99'))->toBe(19.99);
        expect($service->normalize(19.99))->toBe(19.99);
        expect($service->normalize('19,99'))->toBe(1999.0); // Comma treated as thousands separator
    });

    it('can calculate prices using calculate method', function () {
        $service = new PriceFormatterService;

        expect($service->calculate('19.99'))->toBe(19.99);
        expect($service->calculate(19.99))->toBe(19.99);
        expect($service->calculate(0))->toBe(0.0);
    });

    it('can set and get currency', function () {
        $service = new PriceFormatterService;

        $service->setCurrency('EUR');
        expect($service->getCurrency())->toBe('EUR');

        $service->setCurrency('USD');
        expect($service->getCurrency())->toBe('USD');
    });

    it('works with integer transformer correctly', function () {
        $transformer = new IntegerPriceTransformer;
        $service = new PriceFormatterService($transformer);

        expect($service->format(19.99))->toBe('19.99');
        expect($service->normalize('20'))->toBe(2000); // Integer transformer stores as cents
    });

    it('works with decimal transformer correctly', function () {
        $transformer = new DecimalPriceTransformer(
            currency: 'EUR',
            locale: 'de_DE',
            precision: 2
        );
        $service = new PriceFormatterService($transformer);

        expect($service->format(1999.50))->toBe('1999.50');
        expect($service->normalize('1999.50'))->toBe(1999.50);
    });

    it('handles formatInput with complex values', function () {
        $service = new PriceFormatterService;

        expect($service->formatInput('1,999.99'))->toBe('1999.99');
        expect($service->formatInput('1999,99'))->toBe('199999.00'); // Comma thousands separator
        expect($service->formatInput(1999.999))->toBe('2000.00');
    });

    it('handles zero and negative values', function () {
        $service = new PriceFormatterService;

        expect($service->format(0))->toBe('0.00');
        expect($service->format(-19.99))->toBe('-19.99');
        expect($service->normalize('-19.99'))->toBe(-19.99);
    });

    it('maintains transformer state across calls', function () {
        $transformer = new DecimalPriceTransformer('EUR');
        $service = new PriceFormatterService($transformer);

        expect($service->getCurrency())->toBe('EUR');
        $service->setCurrency('USD');
        expect($service->getCurrency())->toBe('USD');

        // Should maintain the same transformer instance
        expect($service->format(19.99))->toBe('19.99');
    });
});
