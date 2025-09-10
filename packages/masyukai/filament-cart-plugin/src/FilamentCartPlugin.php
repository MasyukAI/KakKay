<?php

declare(strict_types=1);

namespace MasyukAI\FilamentCartPlugin;

use Filament\Contracts\Plugin;
use Filament\Panel;
use MasyukAI\FilamentCartPlugin\Resources\CartResource;
use MasyukAI\FilamentCartPlugin\Resources\CartConditionResource;
use MasyukAI\FilamentCartPlugin\Widgets\CartStatsWidget;

class FilamentCartPlugin implements Plugin
{
    public function getId(): string
    {
        return 'masyukai-filament-cart-plugin';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->resources([
                CartResource::class,
                CartConditionResource::class,
            ])
            ->widgets([
                CartStatsWidget::class,
            ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }
}