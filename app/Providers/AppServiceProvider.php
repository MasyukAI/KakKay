<?php

declare(strict_types=1);

namespace App\Providers;

use AIArmada\Orders\Events\OrderPaid;
use App\Listeners\SendOrderConfirmationEmail;
use Filament\Support\Facades\FilamentTimezone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
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

        FilamentTimezone::set('Asia/Kuala_Lumpur');

        // Register event listeners for package events
        // OrderPaid (dispatched by aiarmada/checkout CreateOrderStep) -> SendOrderConfirmationEmail
        Event::listen(OrderPaid::class, SendOrderConfirmationEmail::class);
    }
}
