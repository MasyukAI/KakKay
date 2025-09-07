<?php

declare(strict_types=1);

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use MasyukAI\Cart\CartServiceProvider;
use Mockery;

describe('CartServiceProvider Missing Coverage', function () {
    afterEach(function () {
        Mockery::close();
    });

    it('covers database storage environment exception paths', function () {
        $app = Mockery::mock(Application::class);
        
        // Mock the bind calls for session and cache storage first
        $app->shouldReceive('bind')->with('cart.storage.session', Mockery::type('callable'))->once();
        $app->shouldReceive('bind')->with('cart.storage.cache', Mockery::type('callable'))->once();
        
        // Mock database storage registration with environment exception
        $app->shouldReceive('bind')->with('cart.storage.database', Mockery::type('callable'))->once()
            ->andReturnUsing(function ($key, $callback) use ($app) {
                // When the callback is executed, it should trigger the environment exception path
                $callback($app);
                return null;
            });
        
        // Mock environment method to throw exception (line 76-78)
        $app->shouldReceive('environment')->with('testing')->andThrow(new Exception('Environment not available'));
        
        // Mock bound check to return false for db (line 83-86)
        $app->shouldReceive('bound')->with('db')->andReturn(false);
        
        $provider = new CartServiceProvider($app);
        $reflection = new ReflectionClass($provider);
        $method = $reflection->getMethod('registerStorageDrivers');
        $method->setAccessible(true);
        
        // This should trigger the exception path and then the database storage unavailable exception
        expect(function() use ($method, $provider) {
            $method->invoke($provider);
        })->toThrow(Exception::class, 'Database storage not available in test environment');
    });

    it('covers database storage testing environment paths', function () {
        $app = Mockery::mock(Application::class);
        
        // Mock the bind calls for session and cache storage first
        $app->shouldReceive('bind')->with('cart.storage.session', Mockery::type('callable'))->once();
        $app->shouldReceive('bind')->with('cart.storage.cache', Mockery::type('callable'))->once();
        
        // Mock database storage registration
        $app->shouldReceive('bind')->with('cart.storage.database', Mockery::type('callable'))->once()
            ->andReturnUsing(function ($key, $callback) use ($app) {
                $callback($app);
                return null;
            });
        
        // Mock environment method to return true for testing (line 79)
        $app->shouldReceive('environment')->with('testing')->andReturn(true);
        
        // Mock bound check to return true for db (line 94-95)
        $app->shouldReceive('bound')->with('db')->andReturn(true);
        
        // Mock bound check for db.connection to return true (line 100)
        $app->shouldReceive('bound')->with('db.connection')->andReturn(true);
        
        // Mock make method for DatabaseInterface (line 107)
        $mockConnection = Mockery::mock(\Illuminate\Database\ConnectionInterface::class);
        $app->shouldReceive('make')->with(\Illuminate\Database\ConnectionInterface::class)->andReturn($mockConnection);
        
        $provider = new CartServiceProvider($app);
        $reflection = new ReflectionClass($provider);
        $method = $reflection->getMethod('registerStorageDrivers');
        $method->setAccessible(true);
        
        expect(function() use ($method, $provider) {
            $method->invoke($provider);
        })->not->toThrow(Exception::class);
    });

    it('covers event listener registration when config is disabled', function () {
        // Mock config to return false for auto_migrate_on_login (line 182)
        config(['cart.migration.auto_migrate_on_login' => false]);
        config(['cart.migration.backup_on_logout' => false]);
        
        $app = Mockery::mock(Application::class);
        $provider = new CartServiceProvider($app);
        
        $reflection = new ReflectionClass($provider);
        $method = $reflection->getMethod('registerEventListeners');
        $method->setAccessible(true);
        
        // Should not try to register any event listeners
        expect(function() use ($method, $provider) {
            $method->invoke($provider);
        })->not->toThrow(Exception::class);
        
        expect(true)->toBeTrue();
    });

    it('covers Livewire exception handling path', function () {
        $app = Mockery::mock(Application::class);
        $provider = new CartServiceProvider($app);
        
        $reflection = new ReflectionClass($provider);
        $method = $reflection->getMethod('registerLivewireComponents');
        $method->setAccessible(true);
        
        // This should trigger the exception path (line 227) since Livewire facade won't be available
        expect(function() use ($method, $provider) {
            $method->invoke($provider);
        })->not->toThrow(Exception::class);
        
        expect(true)->toBeTrue();
    });

    it('covers price transformer registration paths', function () {
        $app = Mockery::mock(Application::class);
        
        // Mock the bind calls for decimal and integer transformers (lines 239-242)
        $app->shouldReceive('bind')->with('cart.price.transformer.decimal', Mockery::type('callable'))->once();
        $app->shouldReceive('bind')->with('cart.price.transformer.integer', Mockery::type('callable'))->once();
        
        // Mock the bind call for the interface (line 243)
        $app->shouldReceive('bind')->with(\MasyukAI\Cart\Contracts\PriceTransformerInterface::class, Mockery::type('callable'))->once()
            ->andReturnUsing(function ($interface, $callback) use ($app) {
                // Execute the callback to test the make call
                $callback($app);
                return null;
            });
        
        // Mock config call to get transformer class
        config(['cart.price_formatting.transformer' => 'MasyukAI\Cart\PriceTransformers\DecimalPriceTransformer']);
        
        // Mock the make call for the configured transformer
        $mockTransformer = Mockery::mock(\MasyukAI\Cart\Contracts\PriceTransformerInterface::class);
        $app->shouldReceive('make')->with('MasyukAI\Cart\PriceTransformers\DecimalPriceTransformer')->andReturn($mockTransformer);
        
        $provider = new CartServiceProvider($app);
        $reflection = new ReflectionClass($provider);
        $method = $reflection->getMethod('registerPriceTransformers');
        $method->setAccessible(true);
        
        expect(function() use ($method, $provider) {
            $method->invoke($provider);
        })->not->toThrow(Exception::class);
    });

    it('covers event listener registration with logout enabled', function () {
        // Set config for backup on logout
        config(['cart.migration.backup_on_logout' => true]);
        
        $dispatcher = Mockery::mock(\Illuminate\Contracts\Events\Dispatcher::class);
        $app = Mockery::mock(Application::class);
        
        // Should make dispatcher 3 times (lines 176, 178, 182)
        $app->shouldReceive('make')->with(\Illuminate\Contracts\Events\Dispatcher::class)->times(3)->andReturn($dispatcher);
        
        // Should listen to 3 events
        $dispatcher->shouldReceive('listen')->times(3);
        
        $provider = new CartServiceProvider($app);
        $reflection = new ReflectionClass($provider);
        $method = $reflection->getMethod('registerEventListeners');
        $method->setAccessible(true);
        
        expect(function() use ($method, $provider) {
            $method->invoke($provider);
        })->not->toThrow(Exception::class);
    });

    it('covers storage driver registration callbacks', function () {
        $session = Mockery::mock(\Illuminate\Contracts\Session\Session::class);
        $cache = Mockery::mock(\Illuminate\Contracts\Cache\Repository::class);
        $connection = Mockery::mock(\Illuminate\Database\ConnectionInterface::class);
        $connectionResolver = Mockery::mock(\Illuminate\Database\ConnectionResolverInterface::class);
        $connectionResolver->shouldReceive('connection')->andReturn($connection);
        
        $app = Mockery::mock(Application::class);
        
        // Mock the make calls that happen inside the closures (lines 76-78, 83-86, 93-107)
        $app->shouldReceive('make')->with(\Illuminate\Contracts\Session\Session::class)->andReturn($session);
        $app->shouldReceive('make')->with(\Illuminate\Contracts\Cache\Repository::class)->andReturn($cache);
        $app->shouldReceive('environment')->with('testing')->andReturn(false);
        $app->shouldReceive('make')->with(\Illuminate\Database\ConnectionResolverInterface::class)->andReturn($connectionResolver);
        
        // Capture the callbacks to test them
        $sessionCallback = null;
        $cacheCallback = null;
        $databaseCallback = null;
        
        $app->shouldReceive('bind')->with('cart.storage.session', Mockery::type('callable'))
            ->andReturnUsing(function ($name, $callback) use (&$sessionCallback) {
                $sessionCallback = $callback;
            });
            
        $app->shouldReceive('bind')->with('cart.storage.cache', Mockery::type('callable'))
            ->andReturnUsing(function ($name, $callback) use (&$cacheCallback) {
                $cacheCallback = $callback;
            });
            
        $app->shouldReceive('bind')->with('cart.storage.database', Mockery::type('callable'))
            ->andReturnUsing(function ($name, $callback) use (&$databaseCallback) {
                $databaseCallback = $callback;
            });
        
        $provider = new CartServiceProvider($app);
        $reflection = new ReflectionClass($provider);
        $method = $reflection->getMethod('registerStorageDrivers');
        $method->setAccessible(true);
        $method->invoke($provider);
        
        // Test the callbacks execute properly (covering lines 76-78, 83-86, 93-107)
        expect($sessionCallback)->not->toBeNull();
        expect($cacheCallback)->not->toBeNull();
        expect($databaseCallback)->not->toBeNull();
        
        $sessionStorage = $sessionCallback($app);
        $cacheStorage = $cacheCallback($app);
        $databaseStorage = $databaseCallback($app);
        
        expect($sessionStorage)->toBeInstanceOf(\MasyukAI\Cart\Storage\SessionStorage::class);
        expect($cacheStorage)->toBeInstanceOf(\MasyukAI\Cart\Storage\CacheStorage::class);
        expect($databaseStorage)->toBeInstanceOf(\MasyukAI\Cart\Storage\DatabaseStorage::class);
    });

    it('covers database storage testing environment exception path', function () {
        $app = Mockery::mock(Application::class);
        
        // Mock environment to return testing and bound to return false for 'db'
        $app->shouldReceive('environment')->with('testing')->andReturn(true);
        $app->shouldReceive('bound')->with('db')->andReturn(false);
        
        // Capture the database callback
        $databaseCallback = null;
        $app->shouldReceive('bind')->with('cart.storage.session', Mockery::type('callable'));
        $app->shouldReceive('bind')->with('cart.storage.cache', Mockery::type('callable'));
        $app->shouldReceive('bind')->with('cart.storage.database', Mockery::type('callable'))
            ->andReturnUsing(function ($name, $callback) use (&$databaseCallback) {
                $databaseCallback = $callback;
            });
        
        $provider = new CartServiceProvider($app);
        $reflection = new ReflectionClass($provider);
        $method = $reflection->getMethod('registerStorageDrivers');
        $method->setAccessible(true);
        $method->invoke($provider);
        
        // Test that the database callback throws exception when db not bound in testing (line 100)
        expect($databaseCallback)->not->toBeNull();
        expect(fn() => $databaseCallback($app))->toThrow(\Exception::class, 'Database storage not available in test environment');
    });

    it('covers database storage testing environment with db.connection', function () {
        $connection = Mockery::mock(\Illuminate\Database\ConnectionInterface::class);
        $app = Mockery::mock(Application::class);
        
        // Mock environment to return testing and bound to return true for 'db.connection'
        $app->shouldReceive('environment')->with('testing')->andReturn(true);
        $app->shouldReceive('bound')->with('db')->andReturn(true);
        $app->shouldReceive('bound')->with('db.connection')->andReturn(true);
        $app->shouldReceive('make')->with(\Illuminate\Database\ConnectionInterface::class)->andReturn($connection);
        
        // Capture the database callback
        $databaseCallback = null;
        $app->shouldReceive('bind')->with('cart.storage.session', Mockery::type('callable'));
        $app->shouldReceive('bind')->with('cart.storage.cache', Mockery::type('callable'));
        $app->shouldReceive('bind')->with('cart.storage.database', Mockery::type('callable'))
            ->andReturnUsing(function ($name, $callback) use (&$databaseCallback) {
                $databaseCallback = $callback;
            });
        
        $provider = new CartServiceProvider($app);
        $reflection = new ReflectionClass($provider);
        $method = $reflection->getMethod('registerStorageDrivers');
        $method->setAccessible(true);
        $method->invoke($provider);
        
        // Test that the database callback works in testing environment with db.connection (line 105)
        expect($databaseCallback)->not->toBeNull();
        $databaseStorage = $databaseCallback($app);
        expect($databaseStorage)->toBeInstanceOf(\MasyukAI\Cart\Storage\DatabaseStorage::class);
    });

    it('covers price transformer callback execution', function () {
        $app = Mockery::mock(Application::class);
        
        // Capture the callbacks
        $decimalCallback = null;
        $integerCallback = null;
        $interfaceCallback = null;
        
        $app->shouldReceive('bind')->with('cart.price.transformer.decimal', Mockery::type('callable'))
            ->andReturnUsing(function ($name, $callback) use (&$decimalCallback) {
                $decimalCallback = $callback;
            });
            
        $app->shouldReceive('bind')->with('cart.price.transformer.integer', Mockery::type('callable'))
            ->andReturnUsing(function ($name, $callback) use (&$integerCallback) {
                $integerCallback = $callback;
            });
        
        $app->shouldReceive('bind')->with(\MasyukAI\Cart\Contracts\PriceTransformerInterface::class, Mockery::type('callable'))
            ->andReturnUsing(function ($name, $callback) use (&$interfaceCallback) {
                $interfaceCallback = $callback;
            });
        
        // Mock for interface callback
        $mockTransformer = Mockery::mock(\MasyukAI\Cart\Contracts\PriceTransformerInterface::class);
        config(['cart.price_formatting.transformer' => 'test.transformer']);
        $app->shouldReceive('make')->with('test.transformer')->andReturn($mockTransformer);
        
        $provider = new CartServiceProvider($app);
        $reflection = new ReflectionClass($provider);
        $method = $reflection->getMethod('registerPriceTransformers');
        $method->setAccessible(true);
        $method->invoke($provider);
        
        // Test the callbacks execute properly to cover lines 239-242, 243
        expect($decimalCallback)->not->toBeNull();
        expect($integerCallback)->not->toBeNull();
        expect($interfaceCallback)->not->toBeNull();
        
        $decimalTransformer = $decimalCallback($app);
        $integerTransformer = $integerCallback($app);
        $interfaceTransformer = $interfaceCallback($app);
        
        expect($decimalTransformer)->toBeInstanceOf(\MasyukAI\Cart\PriceTransformers\DecimalPriceTransformer::class);
        expect($integerTransformer)->toBeInstanceOf(\MasyukAI\Cart\PriceTransformers\IntegerPriceTransformer::class);
        expect($interfaceTransformer)->toBe($mockTransformer);
    });

    it('covers Livewire registration exception handling', function () {
        $app = Mockery::mock(Application::class);
        $app->shouldReceive('make')->with(\Illuminate\Contracts\Events\Dispatcher::class)->andReturn(Mockery::mock(\Illuminate\Contracts\Events\Dispatcher::class));
        
        // Create mock Livewire class that throws exception on component registration (to hit line 227)
        if (!class_exists('MockLivewire')) {
            eval('
                class MockLivewire {
                    public static function component($name, $class) {
                        throw new \Exception("Livewire registration failed");
                    }
                }
            ');
        }
        
        // Create an alias to make it exist for the class_exists check but still throw
        if (!class_exists('Livewire\Livewire')) {
            class_alias('MockLivewire', 'Livewire\Livewire');
        }
        
        $provider = new CartServiceProvider($app);
        $reflection = new ReflectionClass($provider);
        $method = $reflection->getMethod('registerLivewireComponents');
        $method->setAccessible(true);
        
        // Should not throw even when Livewire::component throws (covers line 227 catch)
        expect(function() use ($method, $provider) {
            $method->invoke($provider);
        })->not->toThrow(\Exception::class);
    });
});
