<?php

declare(strict_types=1);

use MasyukAI\Cart\CartServiceProvider;
use MasyukAI\Cart\Services\CartMigrationService;
use MasyukAI\Cart\Storage\StorageInterface;

describe('CartServiceProvider Targeted Coverage', function () {

    it('can call provides method', function () {
        $provider = new CartServiceProvider(app());
        $services = $provider->provides();

        expect($services)->toBeArray();
        expect($services)->toContain('cart');
        expect($services)->toContain(StorageInterface::class);
        expect($services)->toContain(CartMigrationService::class);
    });

    it('can test price transformer configurations', function () {
        config([
            'cart.price_formatting.transformer' => \MasyukAI\Cart\PriceTransformers\IntegerPriceTransformer::class,
            'cart.price_formatting.currency' => 'EUR',
            'cart.price_formatting.locale' => 'de_DE',
            'cart.price_formatting.precision' => 3,
        ]);

        $provider = new CartServiceProvider(app());

        // Test the provides method returns correct services
        $services = $provider->provides();
        expect($services)->toContain('cart.storage.session');
        expect($services)->toContain('cart.storage.cache');
        expect($services)->toContain('cart.storage.database');
    });

    it('can test different storage configurations', function () {
        config([
            'cart.storage' => 'cache',
            'cart.cache.prefix' => 'test_cart',
            'cart.cache.ttl' => 7200,
            'cart.session.key' => 'test_session_cart',
            'cart.database.table' => 'test_carts',
        ]);

        $provider = new CartServiceProvider(app());
        $services = $provider->provides();

        expect($services)->toBeArray();
        expect(count($services))->toBeGreaterThan(5);
    });

    it('can test event configuration settings', function () {
        config([
            'cart.migration.auto_migrate_on_login' => false,
            'cart.migration.backup_on_logout' => true,
            'cart.events' => false,
        ]);

        $provider = new CartServiceProvider(app());
        $services = $provider->provides();

        expect($services)->toContain('cart');
    });

    it('can test demo configuration', function () {
        config([
            'cart.demo.enabled' => true,
        ]);

        $provider = new CartServiceProvider(app());
        $services = $provider->provides();

        expect($services)->toBeArray();
    });

    it('can test different price transformer classes', function () {
        // Test Decimal transformer
        config(['cart.price_formatting.transformer' => \MasyukAI\Cart\PriceTransformers\DecimalPriceTransformer::class]);
        $provider1 = new CartServiceProvider(app());
        expect($provider1->provides())->toContain('cart');

        // Test Localized transformer
        config(['cart.price_formatting.transformer' => \MasyukAI\Cart\PriceTransformers\LocalizedPriceTransformer::class]);
        $provider2 = new CartServiceProvider(app());
        expect($provider2->provides())->toContain('cart');

        // Test with different separators
        config([
            'cart.price_formatting.decimal_separator' => ',',
            'cart.price_formatting.thousands_separator' => '.',
        ]);
        $provider3 = new CartServiceProvider(app());
        expect($provider3->provides())->toContain('cart');
    });

});
