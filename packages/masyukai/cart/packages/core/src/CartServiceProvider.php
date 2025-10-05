<?php

declare(strict_types=1);

namespace MasyukAI\Cart;

use Illuminate\Auth\Events\Attempting;
use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Events\Dispatcher;
use MasyukAI\Cart\Listeners\DispatchCartUpdated;
use MasyukAI\Cart\Listeners\HandleUserLogin;
use MasyukAI\Cart\Listeners\HandleUserLoginAttempt;
use MasyukAI\Cart\Services\CartMigrationService;
use MasyukAI\Cart\Storage\CacheStorage;
use MasyukAI\Cart\Storage\DatabaseStorage;
use MasyukAI\Cart\Storage\SessionStorage;
use MasyukAI\Cart\Storage\StorageInterface;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class CartServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('cart')
            ->hasConfigFile()
            ->discoversMigrations()
            ->runsMigrations()
            ->hasCommands([
                Console\Commands\ClearAbandonedCartsCommand::class,
            ]);
    }

    public function registeringPackage(): void
    {
        $this->registerStorageDrivers();
        $this->registerCartManager();
        $this->registerMigrationService();
    }

    public function bootingPackage(): void
    {
        $this->registerEventListeners();
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<string>
     */
    public function provides(): array
    {
        return [
            'cart',
            Cart::class,
            StorageInterface::class,
            CartMigrationService::class,
            'cart.storage.session',
            'cart.storage.cache',
            'cart.storage.database',
        ];
    }

    /**
     * Register storage drivers
     */
    protected function registerStorageDrivers(): void
    {
        $this->app->bind('cart.storage.session', function (\Illuminate\Contracts\Foundation\Application $app) {
            return new SessionStorage(
                $app->make(\Illuminate\Contracts\Session\Session::class),
                config('cart.session.key', 'cart')
            );
        });

        $this->app->bind('cart.storage.cache', function (\Illuminate\Contracts\Foundation\Application $app) {
            return new CacheStorage(
                $app->make(\Illuminate\Contracts\Cache\Repository::class),
                config('cart.cache.prefix', 'cart'),
                config('cart.cache.ttl', 86400)
            );
        });

        $this->app->bind('cart.storage.database', function (\Illuminate\Contracts\Foundation\Application $app) {
            $connection = $app->make(\Illuminate\Database\ConnectionResolverInterface::class)->connection();

            return new DatabaseStorage(
                $connection,
                config('cart.database.table', 'carts')
            );
        });
    }

    /**
     * Register cart manager
     */
    protected function registerCartManager(): void
    {
        $this->app->singleton('cart', function (\Illuminate\Contracts\Foundation\Application $app) {
            $driver = config('cart.storage', 'session');
            $storage = $app->make("cart.storage.{$driver}");

            return new CartManager(
                storage: $storage,
                events: $app->make(Dispatcher::class),
                eventsEnabled: config('cart.events', true)
            );
        });

        $this->app->alias('cart', CartManager::class);
    }

    /**
     * Register cart migration service
     */
    protected function registerMigrationService(): void
    {
        $this->app->singleton(function (\Illuminate\Contracts\Foundation\Application $app): CartMigrationService {
            return new CartMigrationService;
        });
    }

    /**
     * Register event listeners for cart migration
     */
    protected function registerEventListeners(): void
    {
        $dispatcher = $this->app->make(Dispatcher::class);

        // Register cart update event subscriber
        $dispatcher->subscribe(DispatchCartUpdated::class);

        if (config('cart.migration.auto_migrate_on_login', true)) {
            // Register login attempt listener to capture session ID before regeneration
            $dispatcher->listen(Attempting::class, HandleUserLoginAttempt::class);
            // Register login listener to handle cart migration
            $dispatcher->listen(Login::class, HandleUserLogin::class);
        }
    }
}
