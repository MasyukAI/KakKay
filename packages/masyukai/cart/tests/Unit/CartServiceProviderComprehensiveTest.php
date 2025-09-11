<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use MasyukAI\Cart\CartManager;
use MasyukAI\Cart\CartServiceProvider;
use MasyukAI\Cart\Contracts\PriceTransformerInterface;
use MasyukAI\Cart\PriceTransformers\DecimalPriceTransformer;
use MasyukAI\Cart\PriceTransformers\IntegerPriceTransformer;
use MasyukAI\Cart\Services\CartMetricsService;
use MasyukAI\Cart\Services\CartMigrationService;
use MasyukAI\Cart\Services\CartRetryService;
use MasyukAI\Cart\Storage\CacheStorage;
use MasyukAI\Cart\Storage\SessionStorage;

describe('CartServiceProvider Comprehensive Integration', function () {
    beforeEach(function () {
        // Set minimal required config
        Config::set('cart.storage', 'session');
        Config::set('cart.session.key', 'cart');
        Config::set('cart.cache.prefix', 'cart');
        Config::set('cart.cache.ttl', 86400);
        Config::set('cart.display.transformer', DecimalPriceTransformer::class);
        Config::set('cart.money.default_currency', 'USD');
        Config::set('cart.display.locale', 'en_US');
        Config::set('cart.money.default_precision', 2);
        Config::set('cart.events', true);
        Config::set('cart.migration.auto_migrate_on_login', true);
    });

    it('registers all core services without errors', function () {
        $app = app();
        $provider = new CartServiceProvider($app);

        // Register all services
        $provider->register();

        // Verify core cart service
        expect($app->bound('cart'))->toBeTrue();
        expect($app->make('cart'))->toBeInstanceOf(CartManager::class);
        expect($app->make(CartManager::class))->toBeInstanceOf(CartManager::class);
    });

    it('registers all storage drivers correctly', function () {
        $app = app();
        $provider = new CartServiceProvider($app);
        $provider->register();

        // Test session storage
        expect($app->bound('cart.storage.session'))->toBeTrue();
        expect($app->make('cart.storage.session'))->toBeInstanceOf(SessionStorage::class);

        // Test cache storage
        expect($app->bound('cart.storage.cache'))->toBeTrue();
        expect($app->make('cart.storage.cache'))->toBeInstanceOf(CacheStorage::class);

        // Database storage is tested elsewhere due to test environment constraints
        expect($app->bound('cart.storage.database'))->toBeTrue();
    });

    it('registers price transformers with proper configuration', function () {
        $app = app();
        $provider = new CartServiceProvider($app);
        $provider->register();

        // Test decimal transformer
        expect($app->bound('cart.price.transformer.decimal'))->toBeTrue();
        $decimalTransformer = $app->make('cart.price.transformer.decimal');
        expect($decimalTransformer)->toBeInstanceOf(DecimalPriceTransformer::class);

        // Test integer transformer
        expect($app->bound('cart.price.transformer.integer'))->toBeTrue();
        $integerTransformer = $app->make('cart.price.transformer.integer');
        expect($integerTransformer)->toBeInstanceOf(IntegerPriceTransformer::class);

        // Test interface binding
        expect($app->bound(PriceTransformerInterface::class))->toBeTrue();
        $interfaceTransformer = $app->make(PriceTransformerInterface::class);
        expect($interfaceTransformer)->toBeInstanceOf(DecimalPriceTransformer::class);
    });

    it('registers enhanced services correctly', function () {
        $app = app();
        $provider = new CartServiceProvider($app);
        $provider->register();

        // Test cart migration service
        expect($app->bound(CartMigrationService::class))->toBeTrue();
        expect($app->make(CartMigrationService::class))->toBeInstanceOf(CartMigrationService::class);

        // Test cart metrics service
        expect($app->bound(CartMetricsService::class))->toBeTrue();
        expect($app->make(CartMetricsService::class))->toBeInstanceOf(CartMetricsService::class);

        // Test cart retry service
        expect($app->bound(CartRetryService::class))->toBeTrue();
        expect($app->make(CartRetryService::class))->toBeInstanceOf(CartRetryService::class);
    });

    it('provides complete services list', function () {
        $app = app();
        $provider = new CartServiceProvider($app);
        $services = $provider->provides();

        // Test each service individually
        expect($services)->toContain('cart');
        expect($services)->toContain('MasyukAI\Cart\Cart');
        expect($services)->toContain('MasyukAI\Cart\Storage\StorageInterface');
        expect($services)->toContain('MasyukAI\Cart\Services\CartMigrationService');
        expect($services)->toContain('MasyukAI\Cart\Services\CartMetricsService');
        expect($services)->toContain('MasyukAI\Cart\Services\CartRetryService');
        expect($services)->toContain('cart.storage.session');
        expect($services)->toContain('cart.storage.cache');
        expect($services)->toContain('cart.storage.database');
    });

    it('registers and boots without exceptions', function () {
        $app = app();
        $provider = new CartServiceProvider($app);

        expect(fn () => $provider->register())->not->toThrow(\Exception::class);
        expect(fn () => $provider->boot())->not->toThrow(\Exception::class);
    });

    it('supports different price transformer configurations', function () {
        $app = app();

        // Test with integer transformer
        Config::set('cart.display.transformer', IntegerPriceTransformer::class);

        $provider = new CartServiceProvider($app);
        $provider->register();

        $transformer = $app->make(PriceTransformerInterface::class);
        expect($transformer)->toBeInstanceOf(IntegerPriceTransformer::class);
    });

    it('handles different storage configurations', function () {
        $app = app();

        // Test cache storage configuration
        Config::set('cart.storage', 'cache');

        $provider = new CartServiceProvider($app);
        $provider->register();

        $cartManager = $app->make('cart');
        expect($cartManager)->toBeInstanceOf(CartManager::class);

        // Verify it uses cache storage (through reflection if needed)
        $reflection = new ReflectionClass($cartManager);
        $storageProperty = $reflection->getProperty('storage');
        $storageProperty->setAccessible(true);
        $storage = $storageProperty->getValue($cartManager);
        expect($storage)->toBeInstanceOf(CacheStorage::class);
    });
});
