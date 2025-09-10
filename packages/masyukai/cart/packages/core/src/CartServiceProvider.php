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
        $this->mergeConfigFrom(__DIR__.'/config/cart.php', 'cart');

        $this->registerStorageDrivers();
        $this->registerCartManager();
        $this->registerMigrationService();
        $this->registerPriceTransformers();
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
            // Check environment safely
            try {
                $isTesting = $app->environment('testing');
            } catch (\Exception $e) {
                $isTesting = true;
            }

            // Skip database storage in test environment if db is not properly bound
            if ($isTesting && ! $app->bound('db')) {
                throw new \Exception('Database storage not available in test environment. Use session or cache storage instead.');
            }

            // Handle test environment properly
            if ($isTesting && $app->bound('db.connection')) {
                $connection = $app->make(\Illuminate\Database\ConnectionInterface::class);
            } else {
                $connection = $app->make(\Illuminate\Database\ConnectionResolverInterface::class)->connection();
            }

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
     * Publish configuration file
     */
    protected function publishConfig(): void
    {
        $this->publishes([
            __DIR__.'/config/cart.php' => config_path('cart.php'),
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

        if (config('cart.migration.backup_on_logout', false)) {
            $this->app->make(\Illuminate\Contracts\Events\Dispatcher::class)->listen(Logout::class, HandleUserLogout::class);
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
     * Register price transformers
     */
    protected function registerPriceTransformers(): void
    {
        $this->app->bind('cart.price.transformer.decimal', function (\Illuminate\Contracts\Foundation\Application $app) {
            return new \MasyukAI\Cart\PriceTransformers\DecimalPriceTransformer(
                config('cart.price_formatting.currency', 'USD'),
                config('cart.price_formatting.locale', 'en_US'),
                config('cart.price_formatting.precision', 2)
            );
        });

        $this->app->bind('cart.price.transformer.integer', function (\Illuminate\Contracts\Foundation\Application $app) {
            return new \MasyukAI\Cart\PriceTransformers\IntegerPriceTransformer(
                config('cart.price_formatting.currency', 'USD'),
                config('cart.price_formatting.locale', 'en_US'),
                config('cart.price_formatting.precision', 2)
            );
        });

        // Register the configured transformer
        $this->app->bind(\MasyukAI\Cart\Contracts\PriceTransformerInterface::class, function (\Illuminate\Contracts\Foundation\Application $app): \MasyukAI\Cart\Contracts\PriceTransformerInterface {
            $transformerClass = config('cart.price_formatting.transformer');

            return $app->make($transformerClass);
        });
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
