<?php

declare(strict_types=1);

namespace MasyukAI\FilamentChip;

use Filament\Contracts\Plugin;
use Filament\Panel;
use MasyukAI\FilamentChip\Resources\ClientResource;
use MasyukAI\FilamentChip\Resources\PaymentResource;
use MasyukAI\FilamentChip\Resources\PurchaseResource;

final class FilamentChip implements Plugin
{
    public static function make(): static
    {
        return app(self::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(self::class)->getId());

        return $plugin;
    }

    public function getId(): string
    {
        return 'masyukai-filament-chip';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            PurchaseResource::class,
            PaymentResource::class,
            ClientResource::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
