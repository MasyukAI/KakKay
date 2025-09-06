<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use MasyukAI\Cart\CartServiceProvider;
use MasyukAI\Cart\Services\CartMigrationService;
use MasyukAI\Cart\Storage\StorageInterface;

describe('CartServiceProvider Enhanced Coverage', function () {
    beforeEach(function () {
        $this->app = new Application;
        $this->app->instance('path.config', __DIR__.'/../../config');
        $this->app->instance('config', new \Illuminate\Config\Repository);
        $this->provider = new CartServiceProvider($this->app);
    });

    it('registers service provider without errors', function () {
        expect(fn () => $this->provider->register())->not->toThrow(\Exception::class);
    });

    it('registers cart service correctly', function () {
        $this->provider->register();

        expect($this->app->bound('cart'))->toBeTrue();
        expect($this->app->bound(\MasyukAI\Cart\CartManager::class))->toBeTrue();
    });

    it('registers price transformers correctly', function () {
        $this->provider->register();

        expect($this->app->bound('cart.price.transformer.decimal'))->toBeTrue();
        expect($this->app->bound('cart.price.transformer.integer'))->toBeTrue();
        // Localized transformer was removed as redundant
    });

    it('registers price transformer interface binding', function () {
        config(['cart.price_formatting.transformer' => \MasyukAI\Cart\PriceTransformers\DecimalPriceTransformer::class]);

        $this->provider->register();

        $transformer = $this->app->make(\MasyukAI\Cart\Contracts\PriceTransformerInterface::class);
        expect($transformer)->toBeInstanceOf(\MasyukAI\Cart\PriceTransformers\DecimalPriceTransformer::class);
    });

    it('provides correct services list', function () {
        $services = $this->provider->provides();

        $expectedServices = [
            'cart',
            \MasyukAI\Cart\Cart::class,
            StorageInterface::class,
            CartMigrationService::class,
            'cart.storage.session',
            'cart.storage.cache',
            'cart.storage.database',
        ];

        expect($services)->toBe($expectedServices);
    });

    it('registers migration service as singleton', function () {
        $this->provider->register();

        $service1 = $this->app->make(CartMigrationService::class);
        $service2 = $this->app->make(CartMigrationService::class);

        expect($service1)->toBe($service2); // Should be the same instance
    });

    it('loads migrations from correct path without errors', function () {
        expect(fn () => $this->provider->boot())->not->toThrow(\Exception::class);
    });

    it('handles different config values for price transformers', function () {
        config([
            'cart.price_formatting.currency' => 'EUR',
            'cart.price_formatting.locale' => 'de_DE',
            'cart.price_formatting.precision' => 3,
            'cart.price_formatting.decimal_separator' => ',',
            'cart.price_formatting.thousands_separator' => '.',
        ]);

        $this->provider->register();

        $transformer = $this->app->make('cart.price.transformer.integer');
        expect($transformer)->toBeInstanceOf(\MasyukAI\Cart\PriceTransformers\IntegerPriceTransformer::class);
    });

    it('boots provider without errors', function () {
        expect(fn () => $this->provider->boot())->not->toThrow(\Exception::class);
    });

    it('handles storage registration gracefully', function () {
        $this->provider->register();

        // These might throw exceptions in test environment, but provider should handle gracefully
        expect($this->app->bound('cart.storage.session'))->toBeTrue();
        expect($this->app->bound('cart.storage.cache'))->toBeTrue();
        expect($this->app->bound('cart.storage.database'))->toBeTrue();
    });
});
