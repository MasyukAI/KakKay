<?php

declare(strict_types=1);

namespace MasyukAI\FilamentCart;

use Filament\Contracts\Plugin;
use Filament\Panel;
use MasyukAI\FilamentCart\Resources\CartConditionResource;
use MasyukAI\FilamentCart\Resources\CartItemResource;
use MasyukAI\FilamentCart\Resources\CartResource;
use MasyukAI\FilamentCart\Resources\ConditionResource;
use MasyukAI\FilamentCart\Widgets\CartStatsWidget;

class FilamentCart implements Plugin
{
    public function getId(): string
    {
        return 'masyukai-filament-cart';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->resources([
                CartResource::class,
                CartItemResource::class,
                CartConditionResource::class,
                ConditionResource::class,
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
