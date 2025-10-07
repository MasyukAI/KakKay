<?php

declare(strict_types=1);

namespace MasyukAI\FilamentChip;

use Filament\Facades\Filament;
use Filament\Schemas\Components\Fieldset;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class FilamentChipServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-chip')
            ->hasConfigFile('filament-chip');
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(FilamentChip::class);
    }

    public function packageBooted(): void
    {
        Filament::serving(function (): void {
            Filament::registerPlugin(FilamentChip::make());
        });

        $this->registerMacros();
    }

    private function registerMacros(): void
    {
        if (! Panel::hasMacro('softShadow')) {
            Panel::macro('softShadow', function (string $color = 'gray-200') {
                /** @var Panel $this */
                return $this->extraAttributes([
                    'class' => "shadow-lg shadow-{$color}/40 ring-1 ring-black/5",
                ]);
            });
        }

        if (! Split::hasMacro('glow')) {
            Split::macro('glow', function (string $glowColor = 'primary') {
                /** @var Split $this */
                return $this->extraAttributes([
                    'class' => "after:absolute after:inset-0 after:-z-10 after:rounded-2xl after:bg-gradient-to-r after:from-{$glowColor}-500/20 after:to-transparent",
                ]);
            });
        }

        if (! Stack::hasMacro('carded')) {
            Stack::macro('carded', function () {
                /** @var Stack $this */
                return $this->extraAttributes([
                    'class' => 'rounded-2xl border border-white/60 bg-white/80 p-6 shadow-sm backdrop-blur dark:border-white/10 dark:bg-white/5',
                ]);
            });
        }

        if (! Fieldset::hasMacro('inlineLabelled')) {
            Fieldset::macro('inlineLabelled', function () {
                /** @var Fieldset $this */
                return $this->columns(2)->extraAttributes([
                    'class' => 'gap-x-8',
                ]);
            });
        }
    }
}
