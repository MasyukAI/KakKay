<?php

declare(strict_types=1);

namespace MasyukAI\FilamentShippingPlugin;

use Filament\Panel;
use Filament\Contracts\Plugin;
use MasyukAI\FilamentShippingPlugin\Resources\ShipmentResource;
use MasyukAI\FilamentShippingPlugin\Widgets\ShippingStatsWidget;

class FilamentShippingPlugin implements Plugin
{
    public function getId(): string
    {
        return 'masyukai-shipping';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->resources([
                ShipmentResource::class,
            ])
            ->widgets([
                ShippingStatsWidget::class,
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