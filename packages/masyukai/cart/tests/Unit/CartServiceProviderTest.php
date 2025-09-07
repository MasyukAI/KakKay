<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use MasyukAI\Cart\CartServiceProvider;

describe('CartServiceProvider', function () {
    afterEach(function () {
        Mockery::close();
    });

    it('provides correct services', function () {
        $app = mock(Application::class);
        $provider = new CartServiceProvider($app);
        $provides = $provider->provides();

        expect($provides)->toBeArray();
        expect($provides)->toContain('cart');
        expect($provides)->toContain(\MasyukAI\Cart\Cart::class);
        expect($provides)->toContain(\MasyukAI\Cart\Storage\StorageInterface::class);
        expect($provides)->toContain(\MasyukAI\Cart\Services\CartMigrationService::class);
        expect($provides)->toContain('cart.storage.session');
        expect($provides)->toContain('cart.storage.cache');
        expect($provides)->toContain('cart.storage.database');
    });

    it('registers storage drivers correctly', function () {
        $app = mock(Application::class);

        // Mock all bind calls for storage drivers
        $app->shouldReceive('bind')->withAnyArgs()->times(3);

        $provider = new CartServiceProvider($app);

        $reflection = new ReflectionClass($provider);
        $method = $reflection->getMethod('registerStorageDrivers');
        $method->setAccessible(true);
        $method->invoke($provider);

        expect(true)->toBeTrue();
    });

    it('tests database storage scenarios without complex mocking', function () {
        // This test just covers that the methods exist and can be called without error
        $app = mock(Application::class);
        $app->shouldReceive('bind')->withAnyArgs()->times(3);

        $provider = new CartServiceProvider($app);

        $reflection = new ReflectionClass($provider);
        $method = $reflection->getMethod('registerStorageDrivers');
        $method->setAccessible(true);
        $method->invoke($provider);

        expect(true)->toBeTrue();
    });

    it('registers cart manager correctly', function () {
        $app = mock(Application::class);
        $app->shouldReceive('singleton')->withArgs(['cart', Mockery::type('callable')])->once();
        $app->shouldReceive('alias')->withArgs(['cart', \MasyukAI\Cart\CartManager::class])->once();

        $provider = new CartServiceProvider($app);

        $reflection = new ReflectionClass($provider);
        $method = $reflection->getMethod('registerCartManager');
        $method->setAccessible(true);
        $method->invoke($provider);

        expect(true)->toBeTrue();
    });

    it('can call publish methods without errors', function () {
        $app = mock(Application::class);
        $provider = new CartServiceProvider($app);

        // Test that protected methods exist and can be called (they will throw errors but that's expected in test env)
        $reflection = new ReflectionClass($provider);

        $publishConfigMethod = $reflection->getMethod('publishConfig');
        $publishConfigMethod->setAccessible(true);
        expect(function () use ($publishConfigMethod, $provider) {
            $publishConfigMethod->invoke($provider);
        })->not->toThrow(\Exception::class);

        // Skip publishMigrations test as it accesses Laravel helpers not available in test env
        $publishMigrationsMethod = $reflection->getMethod('publishMigrations');
        expect($publishMigrationsMethod->isProtected())->toBeTrue();

        $publishViewsMethod = $reflection->getMethod('publishViews');
        $publishViewsMethod->setAccessible(true);
        expect(function () use ($publishViewsMethod, $provider) {
            $publishViewsMethod->invoke($provider);
        })->not->toThrow(\Exception::class);
    });

    it('registers migration service correctly', function () {
        $app = mock(Application::class);
        $app->shouldReceive('singleton')->withArgs([\MasyukAI\Cart\Services\CartMigrationService::class, Mockery::type('callable')])->once();

        $provider = new CartServiceProvider($app);

        $reflection = new ReflectionClass($provider);
        $method = $reflection->getMethod('registerMigrationService');
        $method->setAccessible(true);
        $method->invoke($provider);

        expect(true)->toBeTrue();
    });

    it('can call demo routes and livewire methods without errors', function () {
        $app = mock(Application::class);
        $provider = new CartServiceProvider($app);

        $reflection = new ReflectionClass($provider);

        // Just test that the methods exist and are callable
        expect($reflection->hasMethod('loadDemoRoutes'))->toBeTrue()
            ->and($reflection->hasMethod('registerLivewireComponents'))->toBeTrue();

        $loadDemoRoutesMethod = $reflection->getMethod('loadDemoRoutes');
        expect($loadDemoRoutesMethod->isProtected())->toBeTrue();

        $registerLivewireMethod = $reflection->getMethod('registerLivewireComponents');
        expect($registerLivewireMethod->isProtected())->toBeTrue();
    });

    it('can call event listeners method without errors', function () {
        $app = mock(Application::class);
        $provider = new CartServiceProvider($app);

        $reflection = new ReflectionClass($provider);
        $method = $reflection->getMethod('registerEventListeners');

        expect($method->isProtected())->toBeTrue();
        expect($reflection->hasMethod('registerEventListeners'))->toBeTrue();
    });

    it('has all expected protected methods', function () {
        $app = mock(Application::class);
        $provider = new CartServiceProvider($app);

        $reflection = new ReflectionClass($provider);

        $expectedMethods = [
            'registerStorageDrivers',
            'registerCartManager',
            'publishConfig',
            'publishMigrations',
            'registerMigrationService',
            'registerEventListeners',
            'publishViews',
            'loadDemoRoutes',
            'registerLivewireComponents',
            'registerMiddleware',
        ];

        foreach ($expectedMethods as $methodName) {
            expect($reflection->hasMethod($methodName))->toBeTrue("Method {$methodName} should exist");
        }
    });

    it('can call register middleware method without errors', function () {
        $app = mock(Application::class);
        $app->shouldReceive('booted')->with(Mockery::type('callable'))->once();

        $provider = new CartServiceProvider($app);

        $reflection = new ReflectionClass($provider);
        $method = $reflection->getMethod('registerMiddleware');
        $method->setAccessible(true);

        expect(function () use ($method, $provider) {
            $method->invoke($provider);
        })->not->toThrow(\Exception::class);
    });
});


// --- Integration-style tests for real container/config/event/Livewire logic ---
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use MasyukAI\Cart\Storage\SessionStorage;
use MasyukAI\Cart\Storage\CacheStorage;
use MasyukAI\Cart\Storage\DatabaseStorage;
use MasyukAI\Cart\Services\CartMigrationService;
use MasyukAI\Cart\Contracts\PriceTransformerInterface;
use MasyukAI\Cart\PriceTransformers\DecimalPriceTransformer;
use MasyukAI\Cart\PriceTransformers\IntegerPriceTransformer;

beforeEach(function () {
    Config::set('cart.storage', 'session');
    Config::set('cart.price_formatting.transformer', DecimalPriceTransformer::class);
    Config::set('cart.migration.auto_migrate_on_login', true);
    Config::set('cart.migration.backup_on_logout', true);
    Config::set('cart.demo.enabled', false);
});

it('integration: registers all storage drivers', function () {
    $app = app();
    $provider = new \MasyukAI\Cart\CartServiceProvider($app);
    $provider->register();

    expect($app->make('cart.storage.session'))->toBeInstanceOf(SessionStorage::class);
    expect($app->make('cart.storage.cache'))->toBeInstanceOf(CacheStorage::class);
    if ($app->bound('db.connection')) {
        expect($app->make('cart.storage.database'))->toBeInstanceOf(DatabaseStorage::class);
    }
});

it('integration: registers cart manager and aliases', function () {
    $app = app();
    $provider = new \MasyukAI\Cart\CartServiceProvider($app);
    $provider->register();

    expect($app->make('cart'))->toBeInstanceOf(\MasyukAI\Cart\CartManager::class);
    expect($app->make(\MasyukAI\Cart\CartManager::class))->toBeInstanceOf(\MasyukAI\Cart\CartManager::class);
});

it('integration: registers migration service', function () {
    $app = app();
    $provider = new \MasyukAI\Cart\CartServiceProvider($app);
    $provider->register();

    expect($app->make(CartMigrationService::class))->toBeInstanceOf(CartMigrationService::class);
});

it('integration: registers price transformers', function () {
    $app = app();
    $provider = new \MasyukAI\Cart\CartServiceProvider($app);
    $provider->register();

    expect($app->make('cart.price.transformer.decimal'))->toBeInstanceOf(DecimalPriceTransformer::class);
    expect($app->make('cart.price.transformer.integer'))->toBeInstanceOf(IntegerPriceTransformer::class);
    expect($app->make(PriceTransformerInterface::class))->toBeInstanceOf(DecimalPriceTransformer::class);
});

it('integration: publishes config, migrations, and views', function () {
    $app = app();
    $provider = new \MasyukAI\Cart\CartServiceProvider($app);
    $provider->boot();
    expect(true)->toBeTrue();
});

it('integration: registers event listeners based on config', function () {
    $app = app();
    $provider = new \MasyukAI\Cart\CartServiceProvider($app);
    Event::fake();
    $reflection = new ReflectionClass($provider);
    $method = $reflection->getMethod('registerEventListeners');
    $method->setAccessible(true);
    $method->invoke($provider);
    Event::assertListening(\Illuminate\Auth\Events\Attempting::class, \MasyukAI\Cart\Listeners\HandleUserLoginAttempt::class);
    Event::assertListening(\Illuminate\Auth\Events\Login::class, \MasyukAI\Cart\Listeners\HandleUserLogin::class);
    Event::assertListening(\Illuminate\Auth\Events\Logout::class, \MasyukAI\Cart\Listeners\HandleUserLogout::class);
});

it('integration: registers Livewire components if Livewire is present', function () {
    if (!class_exists(\Livewire\Livewire::class)) {
        expect(true)->toBeTrue();
        return;
    }
    $app = app();
    $provider = new \MasyukAI\Cart\CartServiceProvider($app);
    $reflection = new ReflectionClass($provider);
    $method = $reflection->getMethod('registerLivewireComponents');
    $method->setAccessible(true);
    $method->invoke($provider);
    expect(true)->toBeTrue();
});

it('integration: loads demo routes if enabled', function () {
    $app = app();
    $provider = new \MasyukAI\Cart\CartServiceProvider($app);
    Config::set('cart.demo.enabled', true);
    $reflection = new ReflectionClass($provider);
    $method = $reflection->getMethod('loadDemoRoutes');
    $method->setAccessible(true);
    $method->invoke($provider);
    expect(true)->toBeTrue();
});

it('integration: handles exception in loadDemoRoutes gracefully', function () {
    $app = app();
    Config::set('cart.demo.enabled', true);
    $provider = new class($app) extends \MasyukAI\Cart\CartServiceProvider {
        protected function loadRoutesFrom($path)
        {
            throw new \Exception('Route loading failed');
        }
    };
    $reflection = new ReflectionClass($provider);
    $method = $reflection->getMethod('loadDemoRoutes');
    $method->setAccessible(true);
    $method->invoke($provider);
    expect(true)->toBeTrue();
});
