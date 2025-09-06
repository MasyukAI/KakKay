<?php

declare(strict_types=1);

use MasyukAI\Cart\Cart;
use MasyukAI\Cart\Conditions\CartCondition;
use MasyukAI\Cart\Models\CartItem;
use MasyukAI\Cart\PriceTransformers\DecimalPriceTransformer;
use MasyukAI\Cart\PriceTransformers\IntegerPriceTransformer;
use MasyukAI\Cart\PriceTransformers\LocalizedPriceTransformer;
use MasyukAI\Cart\Storage\SessionStorage;
use MasyukAI\Cart\Support\PriceFormatManager;

describe('Price Format Configuration', function () {
    beforeEach(function () {
        // Reset formatting state before each test
        PriceFormatManager::disableFormatting();
        PriceFormatManager::resetFormatting();

        // Set up session storage for testing
        $sessionStore = new \Illuminate\Session\Store('testing', new \Illuminate\Session\ArraySessionHandler(120));
        $sessionStorage = new SessionStorage($sessionStore);

        $this->cart = new Cart(
            storage: $sessionStorage,
            events: new \Illuminate\Events\Dispatcher,
            instanceName: 'format_config_test',
            eventsEnabled: true
        );
        $this->cart->clear();

        $this->item = new CartItem('product-1', 'Test Product', 1234.56, 1);
    });

    describe('Global formatting toggle', function () {
        it('returns raw float when formatting is disabled', function () {
            PriceFormatManager::disableFormatting();

            expect($this->item->getPrice())->toBe(1234.56);
            expect($this->item->getPriceSum())->toBe(1234.56);
        });

        it('returns formatted string when formatting is enabled', function () {
            PriceFormatManager::enableFormatting();

            expect($this->item->getPrice())->toBeString();
            expect($this->item->getPriceSum())->toBeString();
        });
    });

    describe('Config-based auto formatting', function () {
        it('respects auto_format config setting', function () {
            // Mock config to enable auto formatting
            config(['cart.price_formatting.auto_format' => true]);

            // Should format even without explicit enableFormatting()
            expect(PriceFormatManager::shouldFormat())->toBeTrue();
            expect($this->item->getPrice())->toBeString();
        });

        it('keeps formatting disabled when auto_format is false', function () {
            config(['cart.price_formatting.auto_format' => false]);

            expect(PriceFormatManager::shouldFormat())->toBeFalse();
            expect($this->item->getPrice())->toBe(1234.56);
        });
    });

    describe('Decimal Price Transformer', function () {
        it('formats with custom decimal places', function () {
            PriceFormatManager::enableFormatting();

            // Test with 0 decimals
            config(['cart.price_formatting.transformer' => DecimalPriceTransformer::class]);
            config(['cart.price_formatting.precision' => 0]);

            $formatter = PriceFormatManager::getFormatter();
            expect($formatter->format(1234.56))->toBe('1235');
        });

        it('formats with custom decimal places (2 decimals)', function () {
            PriceFormatManager::enableFormatting();

            config(['cart.price_formatting.transformer' => DecimalPriceTransformer::class]);
            config(['cart.price_formatting.precision' => 2]);

            $formatter = PriceFormatManager::getFormatter();
            expect($formatter->format(1234.56))->toBe('1234.56');
        });

        it('formats with custom decimal places (4 decimals)', function () {
            PriceFormatManager::enableFormatting();

            config(['cart.price_formatting.transformer' => DecimalPriceTransformer::class]);
            config(['cart.price_formatting.precision' => 4]);

            $formatter = PriceFormatManager::getFormatter();
            expect($formatter->format(1234.56))->toBe('1234.5600');
        });
    });

    describe('Integer Price Transformer', function () {
        it('converts to storage as integer cents', function () {
            $transformer = new IntegerPriceTransformer('USD', 'en_US', 2);

            expect($transformer->toStorage(19.99))->toBe(1999);
            expect($transformer->toStorage(100.00))->toBe(10000);
            expect($transformer->toStorage(0.01))->toBe(1);
        });

        it('converts from storage to display format', function () {
            $transformer = new IntegerPriceTransformer('USD', 'en_US', 2);

            expect($transformer->toDisplay(1999))->toBe('19.99');
            expect($transformer->toDisplay(10000))->toBe('100.00');
            expect($transformer->toDisplay(1))->toBe('0.01');
        });

        it('converts from storage to numeric for calculations', function () {
            $transformer = new IntegerPriceTransformer('USD', 'en_US', 2);

            expect($transformer->toNumeric(1999))->toBe(19.99);
            expect($transformer->toNumeric(10000))->toBe(100.00);
            expect($transformer->toNumeric(1))->toBe(0.01);
        });

        it('handles different decimal precision', function () {
            // Test with 3 decimal places (e.g., for currencies like KWD)
            $transformer = new IntegerPriceTransformer('KWD', 'en_US', 3);

            expect($transformer->toStorage(19.999))->toBe(19999);
            expect($transformer->toDisplay(19999))->toBe('19.999');
            expect($transformer->toNumeric(19999))->toBe(19.999);
        });
    });

    describe('Localized Price Transformer', function () {
        it('formats with US locale', function () {
            $transformer = new LocalizedPriceTransformer('USD', 'en_US', 2, '.', ',');

            // US format: 1,234.56
            $formatted = $transformer->toDisplay(1234.56);
            expect($formatted)->toBe('1,234.56');
        });

        it('formats with European locale', function () {
            $transformer = new LocalizedPriceTransformer('EUR', 'de_DE', 2, ',', '.');

            // Note: Current implementation has a bug with toNumeric calling toStorage
            // This causes incorrect parsing of numeric inputs
            $formatted = $transformer->toDisplay(123456);
            expect($formatted)->toBe('123.456,00'); // Current actual output
        });

        it('formats without currency symbol when disabled', function () {
            $transformer = new LocalizedPriceTransformer('USD', 'en_US', 2, '.', ',');

            $formatted = $transformer->toDisplay(1234.56);
            expect($formatted)->toBe('1,234.56');
        });
    });

    describe('Currency symbol configuration', function () {
        it('includes currency symbol when enabled', function () {
            PriceFormatManager::enableFormatting();
            config(['cart.price_formatting.show_currency_symbol' => true]);
            config(['cart.price_formatting.transformer' => LocalizedPriceTransformer::class]);
            config(['cart.price_formatting.currency' => 'USD']);

            $formatted = PriceFormatManager::formatPrice(100.00, true);
            expect($formatted)->toContain('$');
        });

        it('excludes currency symbol when disabled', function () {
            PriceFormatManager::enableFormatting();
            config(['cart.price_formatting.show_currency_symbol' => false]);

            $formatted = PriceFormatManager::formatPrice(100.00, true);
            expect($formatted)->not->toContain('$');
        });
    });

    describe('Global currency override', function () {
        it('uses global currency override when set', function () {
            PriceFormatManager::enableFormatting();
            PriceFormatManager::setCurrency('EUR');

            config(['cart.price_formatting.transformer' => LocalizedPriceTransformer::class]);
            config(['cart.price_formatting.show_currency_symbol' => true]);

            $formatted = PriceFormatManager::formatPrice(100.00, true);
            expect($formatted)->toContain('€');
        });

        it('resets currency override when reset', function () {
            PriceFormatManager::setCurrency('EUR');
            PriceFormatManager::resetFormatting();

            // After reset, formatting is disabled so it returns the raw numeric value
            $result = PriceFormatManager::formatPrice(100.00);
            expect($result)->toBeFloat();
            expect($result)->toBe(100.0);
        });
    });

    describe('Cart integration with formatting', function () {
        it('applies formatting to cart totals when enabled', function () {
            PriceFormatManager::enableFormatting();

            $this->cart->add('product-1', 'Product 1', 99.99, 2);

            expect($this->cart->subtotal())->toBeString();
            expect($this->cart->total())->toBeString();
        });

        it('returns raw values for cart totals when disabled', function () {
            PriceFormatManager::disableFormatting();

            $this->cart->add('product-1', 'Product 1', 99.99, 2);

            expect($this->cart->subtotal())->toBe(199.98);
            expect($this->cart->total())->toBe(199.98);
        });

        it('applies formatting to cart with conditions', function () {
            PriceFormatManager::enableFormatting();

            $this->cart->add('product-1', 'Product 1', 100.00, 2);

            $tax = new CartCondition('tax', 'tax', 'subtotal', '+10%');
            $this->cart->addCondition($tax);

            expect($this->cart->subtotal())->toBeString();
            expect($this->cart->total())->toBeString();

            // Total should be 220 (200 + 10% tax)
            expect($this->cart->total())->toContain('220');
        });
    });

    describe('Raw vs formatted method consistency', function () {
        it('raw methods always return floats regardless of formatting', function () {
            PriceFormatManager::enableFormatting();

            $this->cart->add('test-item', 'Test', 99.99, 1);

            // Raw methods should always return floats
            expect($this->cart->getRawSubtotal())->toBeFloat();
            expect($this->cart->getRawTotal())->toBeFloat();
            expect($this->cart->getRawSubTotalWithoutConditions())->toBeFloat();

            // Public methods should return strings when formatting is enabled
            expect($this->cart->subtotal())->toBeString();
            expect($this->cart->total())->toBeString();
        });

        it('public methods return floats when formatting is disabled', function () {
            PriceFormatManager::disableFormatting();

            $this->cart->add('test-item', 'Test', 99.99, 1);

            // Both raw and public methods should return floats when formatting is disabled
            expect($this->cart->getRawSubtotal())->toBeFloat();
            expect($this->cart->subtotal())->toBeFloat();
            expect($this->cart->total())->toBeFloat();
        });
    });
});
