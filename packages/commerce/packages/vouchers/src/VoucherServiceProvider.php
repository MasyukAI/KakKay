<?php

declare(strict_types=1);

namespace AIArmada\Vouchers;

use AIArmada\Vouchers\Services\VoucherService;
use AIArmada\Vouchers\Services\VoucherValidator;
use Illuminate\Support\ServiceProvider;

class VoucherServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/vouchers.php',
            'vouchers'
        );

        // Register services as singletons
        $this->app->singleton(VoucherService::class);
        $this->app->singleton(VoucherValidator::class);

        // Bind facade accessor
        $this->app->bind('voucher', VoucherService::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            // Publish configuration
            $this->publishes([
                __DIR__.'/../config/vouchers.php' => config_path('vouchers.php'),
            ], 'vouchers-config');

            // Publish migrations
            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'vouchers-migrations');
        }

        // Load migrations automatically
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
