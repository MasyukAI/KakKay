<?php

declare(strict_types=1);

use MasyukAI\Cart\Support\PriceFormatManager;
use MasyukAI\Cart\Traits\ManagesPricing;

class TestManagesPricingClass
{
    use ManagesPricing;

    public function testFormatPriceValue(int|float|string $value, bool $withCurrency = false): string|int|float
    {
        return $this->formatPriceValue($value, $withCurrency);
    }
}

describe('ManagesPricing Trait Coverage', function () {
    beforeEach(function () {
        // Reset pricing settings before each test
        PriceFormatManager::resetFormatting();
    });

    afterEach(function () {
        // Clean up after each test
        PriceFormatManager::resetFormatting();
    });

    it('can format price value without currency', function () {
        $class = new TestManagesPricingClass;

        TestManagesPricingClass::enableFormatting();

        $result = $class->testFormatPriceValue(19.99);
        expect($result)->toBe('19.99');
    });

    it('can format price value with currency', function () {
        $class = new TestManagesPricingClass;

        TestManagesPricingClass::enableFormatting();

        // Mock config to enable currency display
        config(['cart.price_formatting.show_currency_symbol' => true]);

        $result = $class->testFormatPriceValue(19.99, true);
        expect($result)->toContain('19.99');
    });

    it('returns raw value when formatting disabled', function () {
        $class = new TestManagesPricingClass;

        $result = $class->testFormatPriceValue(19.99);
        expect($result)->toBe(19.99);
    });

    it('can enable formatting statically', function () {
        TestManagesPricingClass::enableFormatting();

        expect(PriceFormatManager::shouldFormat())->toBeTrue();
    });

    it('can disable formatting statically', function () {
        TestManagesPricingClass::enableFormatting();
        TestManagesPricingClass::disableFormatting();

        expect(PriceFormatManager::shouldFormat())->toBeFalse();
    });

    it('can set currency statically', function () {
        TestManagesPricingClass::setCurrency('EUR');

        expect(PriceFormatManager::shouldFormat())->toBeTrue();
    });

    it('can set currency to null statically', function () {
        TestManagesPricingClass::setCurrency();

        expect(PriceFormatManager::shouldFormat())->toBeTrue();
    });

    it('can reset formatting statically', function () {
        TestManagesPricingClass::enableFormatting();
        TestManagesPricingClass::setCurrency('EUR');

        TestManagesPricingClass::resetFormatting();

        expect(PriceFormatManager::shouldFormat())->toBeFalse();
    });

    it('handles different value types in formatPriceValue', function () {
        $class = new TestManagesPricingClass;

        TestManagesPricingClass::enableFormatting();

        expect($class->testFormatPriceValue(19))->toBe('19.00');
        expect($class->testFormatPriceValue('19.99'))->toBe('19.99');
        expect($class->testFormatPriceValue(19.999))->toBe('20.00');
    });

    it('preserves formatting state across multiple calls', function () {
        $class = new TestManagesPricingClass;

        TestManagesPricingClass::enableFormatting();
        TestManagesPricingClass::setCurrency('GBP');

        $result1 = $class->testFormatPriceValue(19.99);
        $result2 = $class->testFormatPriceValue(29.99);

        expect($result1)->toBe('19.99');
        expect($result2)->toBe('29.99');
        expect(PriceFormatManager::shouldFormat())->toBeTrue();
    });

    it('works with zero and negative values', function () {
        $class = new TestManagesPricingClass;

        TestManagesPricingClass::enableFormatting();

        expect($class->testFormatPriceValue(0))->toBe('0.00');
        expect($class->testFormatPriceValue(-19.99))->toBe('-19.99');
    });

    it('handles large numbers correctly', function () {
        $class = new TestManagesPricingClass;

        TestManagesPricingClass::enableFormatting();

        expect($class->testFormatPriceValue(1234567.89))->toBe('1234567.89');
    });

    it('maintains currency setting across instances', function () {
        $class1 = new TestManagesPricingClass;
        $class2 = new TestManagesPricingClass;

        TestManagesPricingClass::setCurrency('JPY');

        // Both instances should use the same global setting
        expect(PriceFormatManager::shouldFormat())->toBeTrue();

        $result1 = $class1->testFormatPriceValue(100);
        $result2 = $class2->testFormatPriceValue(200);

        expect($result1)->toBe('100.00');
        expect($result2)->toBe('200.00');
    });
});
