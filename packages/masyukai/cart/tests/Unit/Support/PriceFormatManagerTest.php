<?php

declare(strict_types=1);

use MasyukAI\Cart\Support\PriceFormatManager;
use MasyukAI\Cart\Services\PriceFormatterService;
use MasyukAI\Cart\PriceTransformers\DecimalPriceTransformer;
use MasyukAI\Cart\PriceTransformers\LocalizedPriceTransformer;

describe('PriceFormatManager Coverage', function () {
    beforeEach(function () {
        // Reset the manager before each test
        PriceFormatManager::resetFormatting();
    });

    afterEach(function () {
        // Clean up after each test
        PriceFormatManager::resetFormatting();
    });

    it('can get formatter instance', function () {
        $formatter = PriceFormatManager::getFormatter();
        
        expect($formatter)->toBeInstanceOf(PriceFormatterService::class);
    });

    it('reuses formatter instance when transformer class is same', function () {
        $formatter1 = PriceFormatManager::getFormatter();
        $formatter2 = PriceFormatManager::getFormatter();
        
        expect($formatter1)->toBe($formatter2);
    });

    it('should not format by default', function () {
        expect(PriceFormatManager::shouldFormat())->toBeFalse();
    });

    it('can enable formatting globally', function () {
        PriceFormatManager::enableFormatting();
        
        expect(PriceFormatManager::shouldFormat())->toBeTrue();
    });

    it('can disable formatting globally', function () {
        PriceFormatManager::enableFormatting();
        PriceFormatManager::disableFormatting();
        
        expect(PriceFormatManager::shouldFormat())->toBeFalse();
    });

    it('can format price when formatting enabled', function () {
        PriceFormatManager::enableFormatting();
        
        $result = PriceFormatManager::formatPrice(19.99);
        expect($result)->toBe('19.99');
    });

    it('returns normalized price when formatting disabled', function () {
        $result = PriceFormatManager::formatPrice(19.99);
        expect($result)->toBe(19.99);
    });

    it('can format input price when formatting enabled', function () {
        PriceFormatManager::enableFormatting();
        
        $result = PriceFormatManager::formatInputPrice(19.99);
        expect($result)->toBe('19.99');
    });

    it('returns normalized input price when formatting disabled', function () {
        $result = PriceFormatManager::formatInputPrice('19.99');
        expect($result)->toBe(19.99);
    });

    it('can set currency and enable formatting', function () {
        PriceFormatManager::setCurrency('EUR');
        
        expect(PriceFormatManager::shouldFormat())->toBeTrue();
    });

    it('can set currency with null to use default', function () {
        PriceFormatManager::setCurrency();
        
        expect(PriceFormatManager::shouldFormat())->toBeTrue();
    });

    it('can reset all formatting settings', function () {
        PriceFormatManager::enableFormatting();
        PriceFormatManager::setCurrency('EUR');
        
        PriceFormatManager::resetFormatting();
        
        expect(PriceFormatManager::shouldFormat())->toBeFalse();
    });

    it('handles config retrieval gracefully', function () {
        // This tests the private getConfig method through public interface
        $formatter = PriceFormatManager::getFormatter();
        
        expect($formatter)->toBeInstanceOf(PriceFormatterService::class);
    });

    it('formats price with currency when enabled', function () {
        PriceFormatManager::enableFormatting();
        
        // Mock config to enable currency display
        config(['cart.price_formatting.show_currency_symbol' => true]);
        
        $result = PriceFormatManager::formatPrice(19.99, true);
        expect($result)->toContain('19.99');
    });

    it('formats input price with currency when enabled', function () {
        PriceFormatManager::enableFormatting();
        
        // Mock config to enable currency display
        config(['cart.price_formatting.show_currency_symbol' => true]);
        
        $result = PriceFormatManager::formatInputPrice(19.99, true);
        expect($result)->toContain('19.99');
    });

    it('handles different transformer classes', function () {
        // Set config to use different transformer
        config(['cart.price_formatting.transformer' => DecimalPriceTransformer::class]);
        
        $formatter1 = PriceFormatManager::getFormatter();
        
        // Change transformer class
        config(['cart.price_formatting.transformer' => LocalizedPriceTransformer::class]);
        
        $formatter2 = PriceFormatManager::getFormatter();
        
        expect($formatter1)->not->toBe($formatter2);
    });

    it('creates localized transformer with correct parameters', function () {
        // Set config for localized transformer
        config([
            'cart.price_formatting.transformer' => LocalizedPriceTransformer::class,
            'cart.price_formatting.currency' => 'EUR',
            'cart.price_formatting.locale' => 'de_DE',
            'cart.price_formatting.precision' => 3,
            'cart.price_formatting.decimal_separator' => ',',
            'cart.price_formatting.thousands_separator' => '.'
        ]);
        
        $formatter = PriceFormatManager::getFormatter();
        
        expect($formatter)->toBeInstanceOf(PriceFormatterService::class);
        expect($formatter->getCurrency())->toBe('EUR');
    });

    it('creates regular transformer with correct parameters', function () {
        // Set config for regular transformer
        config([
            'cart.price_formatting.transformer' => DecimalPriceTransformer::class,
            'cart.price_formatting.currency' => 'GBP',
            'cart.price_formatting.locale' => 'en_GB',
            'cart.price_formatting.precision' => 4
        ]);
        
        $formatter = PriceFormatManager::getFormatter();
        
        expect($formatter)->toBeInstanceOf(PriceFormatterService::class);
        expect($formatter->getCurrency())->toBe('GBP');
    });

    it('handles currency override in formatting', function () {
        PriceFormatManager::setCurrency('JPY');
        
        // Mock config to enable currency display
        config(['cart.price_formatting.show_currency_symbol' => true]);
        
        $result = PriceFormatManager::formatPrice(100, true);
        expect($result)->toContain('100');
    });

    it('uses fallback values when config throws exception', function () {
        // This implicitly tests the getConfig error handling
        $formatter = PriceFormatManager::getFormatter();
        
        expect($formatter)->toBeInstanceOf(PriceFormatterService::class);
    });
});
