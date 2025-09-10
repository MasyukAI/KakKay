<?php

declare(strict_types=1);

namespace MasyukAI\FilamentCartPlugin;

use Illuminate\Support\ServiceProvider;

class FilamentCartServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}