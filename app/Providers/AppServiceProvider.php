<?php

declare(strict_types=1);

namespace App\Providers;

use AIArmada\Chip\Events\PurchasePaid;
use App\Contracts\PaymentGatewayInterface;
use App\Listeners\HandlePaymentSuccess;
use App\Services\ChipPaymentGateway;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind PaymentGatewayInterface to ChipPaymentGateway
        $this->app->bind(PaymentGatewayInterface::class, ChipPaymentGateway::class);

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
