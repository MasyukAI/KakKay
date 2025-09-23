<?php

declare(strict_types=1);

namespace MasyukAI\FilamentCart;

use Illuminate\Support\ServiceProvider;

class FilamentCartServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/filament-cart.php',
            'filament-cart'
        );
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/filament-cart.php' => config_path('filament-cart.php'),
        ], 'filament-cart-config');

        $this->publishes([
            __DIR__.'/../database/factories' => database_path('factories'),
        ], 'filament-cart-factories');

        $this->publishes([
            __DIR__.'/../database/seeders' => database_path('seeders'),
        ], 'filament-cart-seeders');
    }
}
