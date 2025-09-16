<?php

namespace App\Providers;

use App\Listeners\HandlePaymentSuccess;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use MasyukAI\Chip\Events\PurchasePaid;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::unguard();

        // Register CHIP payment success listener
        Event::listen(
            PurchasePaid::class,
            HandlePaymentSuccess::class
        );

        // Livewire::addPersistentMiddleware([
        //     SetCartSessionKey::class,
        // ]);
    }
}
