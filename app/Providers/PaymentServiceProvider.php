<?php

namespace App\Providers;

use App\Contracts\PaymentGatewayInterface;
use App\Services\ChipPaymentGateway;
use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind PaymentGatewayInterface to ChipPaymentGateway
        $this->app->bind(PaymentGatewayInterface::class, ChipPaymentGateway::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
