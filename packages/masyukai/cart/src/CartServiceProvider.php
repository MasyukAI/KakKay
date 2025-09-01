<?php

declare(strict_types=1);

namespace MasyukAI\Cart;

use Illuminate\Auth\Events\Attempting;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Session\Session;
use Illuminate\Database\ConnectionInterface as Database;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use MasyukAI\Cart\Http\Livewire\AddToCart;
use MasyukAI\Cart\Http\Livewire\CartSummary;
use MasyukAI\Cart\Http\Livewire\CartTable;
use MasyukAI\Cart\Http\Middleware\AutoSwitchCartInstance;
use MasyukAI\Cart\Listeners\HandleUserLogin;
use MasyukAI\Cart\Listeners\HandleUserLoginAttempt;
use MasyukAI\Cart\Listeners\HandleUserLogout;
use MasyukAI\Cart\Services\CartMigrationService;
use MasyukAI\Cart\Storage\CacheStorage;
use MasyukAI\Cart\Storage\DatabaseStorage;
use MasyukAI\Cart\Storage\SessionStorage;
use MasyukAI\Cart\Storage\StorageInterface;

class CartServiceProvider extends ServiceProvider
{
    /**
     * Register any package services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/cart.php', 'cart');

        $this->registerStorageDrivers();
        $this->registerCartManager();
        $this->registerMigrationService();
        $this->registerEventDispatcher();
    }

    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        $this->publishConfig();
        $this->publishMigrations();
        $this->publishViews();
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'cart');
        $this->registerEventListeners();
        $this->registerLivewireComponents();
        $this->loadDemoRoutes();
        $this->registerMiddleware();
    }

    protected function registerMiddleware(): void
    {
        $this->app->booted(function () {
            $middleware = $this->app->make('Illuminate\Foundation\Configuration\Middleware');

            // Always alias for manual use
            $middleware->alias(['cart.middleware' => AutoSwitchCartInstance::class]);

            // Auto-apply to web routes if config enabled
            if (config('cart.migration.auto_switch_instances', true)) {
                $middleware->web(append: [AutoSwitchCartInstance::class]);
            }
        });
    }

    /**
     * Register event dispatcher for testing environments
     */
    protected function registerEventDispatcher(): void
    {
        // Ensure events binding for test environments
        if ($this->app->environment('testing') && ! $this->app->bound('events')) {
            $this->app->singleton('events', function ($app) {
                return new \Illuminate\Events\Dispatcher($app);
            });
        }
    }

    /**
     * Register storage drivers
     */
    protected function registerStorageDrivers(): void
    {
        $this->app->bind('cart.storage.session', function ($app) {
            return new SessionStorage(
                $app->make(\Illuminate\Contracts\Session\Session::class),
                config('cart.session.key', 'cart')
            );
        });

        $this->app->bind('cart.storage.cache', function ($app) {
            return new CacheStorage(
                $app->make(\Illuminate\Contracts\Cache\Repository::class),
                config('cart.cache.prefix', 'cart'),
                config('cart.cache.ttl', 86400)
            );
        });

        $this->app->bind('cart.storage.database', function ($app) {
            // Skip database storage in test environment if db is not properly bound
            if ($app->environment('testing') && ! $app->bound('db')) {
                throw new \Exception('Database storage not available in test environment. Use session or cache storage instead.');
            }

            // Handle test environment properly
            if ($app->environment('testing') && $app->bound('db.connection')) {
                $connection = $app->make('db.connection');
            } else {
                $connection = $app->make('db')->connection();
            }

            return new DatabaseStorage(
                $connection,
                config('cart.database.table', 'cart_storage')
            );
        });
    }

    /**
     * Register cart manager
     */
    protected function registerCartManager(): void
    {
        $this->app->singleton('cart', function ($app) {
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
     * Publish configuration file
     */
    protected function publishConfig(): void
    {
        $this->publishes([
            __DIR__.'/../config/cart.php' => config_path('cart.php'),
        ], 'cart-config');
    }

    /**
     * Publish migration files
     */
    protected function publishMigrations(): void
    {
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'cart-migrations');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    /**
     * Register cart migration service
     */
    protected function registerMigrationService(): void
    {
        $this->app->singleton(CartMigrationService::class, function ($app) {
            return new CartMigrationService;
        });
    }

    /**
     * Register event listeners for cart migration
     */
    protected function registerEventListeners(): void
    {
        if (config('cart.migration.auto_migrate_on_login', true)) {
            // Register login attempt listener to capture session ID before regeneration
            $this->app['events']->listen(Attempting::class, HandleUserLoginAttempt::class);
            // Register login listener to handle cart migration
            $this->app['events']->listen(Login::class, HandleUserLogin::class);
        }

        if (config('cart.migration.backup_on_logout', false)) {
            $this->app['events']->listen(Logout::class, HandleUserLogout::class);
        }
    }

    /**
     * Publish view files
     */
    protected function publishViews(): void
    {
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/cart'),
        ], 'cart-views');
    }

    /**
     * Load demo routes for development and testing
     */
    protected function loadDemoRoutes(): void
    {
        if (config('cart.demo.enabled', app()->environment(['local', 'testing']))) {
            $this->loadRoutesFrom(__DIR__.'/../routes/demo.php');
        }
    }

    /**
     * Register Livewire components
     */
    protected function registerLivewireComponents(): void
    {
        if (class_exists(Livewire::class)) {
            Livewire::component('add-to-cart', AddToCart::class);
            Livewire::component('cart-summary', CartSummary::class);
            Livewire::component('cart-table', CartTable::class);
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
            'cart.storage.session',
            'cart.storage.cache',
            'cart.storage.database',
        ];
    }
}
