<?php

declare(strict_types=1);

namespace MasyukAI\Shipping;

use Illuminate\Support\ServiceProvider;
use MasyukAI\Shipping\Contracts\TrackingServiceInterface;
use MasyukAI\Shipping\Services\TrackingService;

class ShippingServiceProvider extends ServiceProvider
{
    /**
     * Register any package services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/shipping.php', 'shipping');

        $this->app->singleton('shipping', function ($app) {
            return new ShippingManager($app);
        });

        $this->app->bind(TrackingServiceInterface::class, TrackingService::class);
    }

    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        $this->publishConfig();
        $this->publishMigrations();
        $this->publishViews();
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'shipping');
    }

    /**
     * Publish the configuration file.
     */
    protected function publishConfig(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/shipping.php' => config_path('shipping.php'),
            ], 'shipping-config');
        }
    }

    /**
     * Publish and load the migration files.
     */
    protected function publishMigrations(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'shipping-migrations');

            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }
    }

    /**
     * Publish the view files.
     */
    protected function publishViews(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/shipping'),
            ], 'shipping-views');
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            'shipping',
            ShippingManager::class,
            TrackingServiceInterface::class,
        ];
    }
}