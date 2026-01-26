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
use Filament\Support\Facades\FilamentTimezone;

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

        FilamentTimezone::set('Asia/Kuala_Lumpur');

        // Register CHIP payment success listener
        Event::listen(
            PurchasePaid::class, /** @phpstan-ignore-line class.notFound */
            HandlePaymentSuccess::class /** @phpstan-ignore-line class.notFound */
        );

        // Livewire::addPersistentMiddleware([
        //     SetCartSessionKey::class,
        // ]);
    }
}
