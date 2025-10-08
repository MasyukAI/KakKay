<?php

declare(strict_types=1);

namespace MasyukAI\Jnt;

use Illuminate\Support\ServiceProvider;
use MasyukAI\Jnt\Services\JntExpressService;

class JntServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/jnt.php',
            'jnt'
        );

        $this->app->singleton(JntExpressService::class, function ($app): JntExpressService {
            $config = config('jnt');

            return new JntExpressService(
                customerCode: $config['customer_code'],
                password: $config['password'],
                config: $config,
            );
        });

        $this->app->alias(JntExpressService::class, 'jnt-express');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/jnt.php' => config_path('jnt.php'),
            ], 'jnt-config');
        }
    }
}
