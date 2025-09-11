<?php

declare(strict_types=1);

namespace MasyukAI\Cart;

use Illuminate\Auth\Events\Attempting;
use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Events\Dispatcher;
use MasyukAI\Cart\Cart;
use MasyukAI\Cart\Listeners\HandleUserLogin;
use MasyukAI\Cart\Listeners\HandleUserLoginAttempt;
use MasyukAI\Cart\Services\CartMigrationService;
use MasyukAI\Cart\Storage\CacheStorage;
use MasyukAI\Cart\Storage\DatabaseStorage;
use MasyukAI\Cart\Storage\SessionStorage;
use MasyukAI\Cart\Storage\StorageInterface;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class CartServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('cart')
            ->hasConfigFile()
            ->hasMigrations(['create_carts_table'])
            ->hasViews()
            ->hasCommands([
                \MasyukAI\Cart\Console\Commands\ClearAbandonedCartsCommand::class,
                \MasyukAI\Cart\Console\Commands\MigrateGuestCartCommand::class,
                \MasyukAI\Cart\Console\Commands\CartMetricsCommand::class,
            ]);
    }

    public function registeringPackage(): void
    {
        $this->registerStorageDrivers();
        $this->registerCartManager();
        $this->registerMigrationService();
        $this->registerPriceTransformers();
        $this->registerEnhancedServices();
    }

    public function bootingPackage(): void
    {
        $this->registerEventListeners();
        $this->registerOctaneCompatibility();
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
                eventsEnabled: config('cart.events', true),
                config: config('cart', [])
            );
        });

        $this->app->alias('cart', CartManager::class);
    }

    /**
     * Register cart migration service
     */
    protected function registerMigrationService(): void
    {
        $this->app->singleton(\MasyukAI\Cart\Services\CartMigrationService::class, function (\Illuminate\Contracts\Foundation\Application $app): \MasyukAI\Cart\Services\CartMigrationService {
            return new \MasyukAI\Cart\Services\CartMigrationService;
        });
    }

    /**
     * Register event listeners for cart migration
     */
    protected function registerEventListeners(): void
    {
        if (config('cart.migration.auto_migrate_on_login', true)) {
            // Register login attempt listener to capture session ID before regeneration
            $this->app->make(\Illuminate\Contracts\Events\Dispatcher::class)->listen(Attempting::class, HandleUserLoginAttempt::class);
            // Register login listener to handle cart migration
            $this->app->make(\Illuminate\Contracts\Events\Dispatcher::class)->listen(Login::class, HandleUserLogin::class);
        }
    }

    /**
     * Register price transformers
     */
    protected function registerPriceTransformers(): void
    {
        $this->app->bind('cart.display.transformer.decimal', function (\Illuminate\Contracts\Foundation\Application $app) {
            return new \MasyukAI\Cart\PriceTransformers\DecimalPriceTransformer(
                precision: config('cart.money.default_precision', 2)
            );
        });

        $this->app->bind('cart.display.transformer.integer', function (\Illuminate\Contracts\Foundation\Application $app) {
            return new \MasyukAI\Cart\PriceTransformers\IntegerPriceTransformer(
                precision: config('cart.money.default_precision', 2)
            );
        });

        // Register the configured transformer
        $this->app->bind(\MasyukAI\Cart\Contracts\PriceTransformerInterface::class, function (\Illuminate\Contracts\Foundation\Application $app): \MasyukAI\Cart\Contracts\PriceTransformerInterface {
            $transformerClass = config('cart.display.transformer');

            return $app->make($transformerClass);
        });
    }

    /**
     * Register enhanced cart services
     */
    protected function registerEnhancedServices(): void
    {
        $this->app->singleton(\MasyukAI\Cart\Services\CartMetricsService::class, function ($app) {
            return new \MasyukAI\Cart\Services\CartMetricsService;
        });

        $this->app->singleton(\MasyukAI\Cart\Services\CartRetryService::class, function ($app) {
            return new \MasyukAI\Cart\Services\CartRetryService;
        });
    }

    /**
     * Register Octane compatibility listeners
     */
    protected function registerOctaneCompatibility(): void
    {
        // Auto-detect Octane and register necessary listeners
        if (class_exists('\Laravel\Octane\Contracts\OperationTerminated')) {
            $this->app->booted(function () {
                if ($this->app->bound('events')) {
                    $events = $this->app->make('events');

                    // Register state reset listener for Octane
                    $events->listen(
                        '\Laravel\Octane\Contracts\OperationTerminated',
                        \MasyukAI\Cart\Listeners\ResetCartState::class
                    );
                }
            });
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            'cart',
            Cart::class,
            StorageInterface::class,
            CartMigrationService::class,
            \MasyukAI\Cart\Services\CartMetricsService::class,
            \MasyukAI\Cart\Services\CartRetryService::class,
            'cart.storage.session',
            'cart.storage.cache',
            'cart.storage.database',
        ];
    }
}
