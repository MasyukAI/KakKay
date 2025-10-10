<?php

declare(strict_types=1);

namespace MasyukAI\FilamentChip\Tests;

use Filament\Facades\Filament;
use Filament\Panel;
use Filament\PanelRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use MasyukAI\FilamentChip\FilamentChip;
use MasyukAI\FilamentChip\FilamentChipServiceProvider;
use Override;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    use RefreshDatabase;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $envPath = base_path('.env');

        if (! file_exists($envPath)) {
            file_put_contents($envPath, "APP_KEY=\n");
        }

        Artisan::call('key:generate', ['--force' => true]);

        $panel = Panel::make()
            ->default()
            ->id('admin')
            ->path('admin')
            ->brandName('Test Admin')
            ->plugins([
                FilamentChip::make(),
            ]);

        app(PanelRegistry::class)->register($panel);

        Filament::setCurrentPanel($panel);
        Filament::setServingStatus(true);
        Filament::bootCurrentPanel();

        Route::name('filament.admin.resources.payments.')
            ->prefix('admin/payments')
            ->group(function (): void {
                Route::get('/', fn (): null => null)->name('index');
                Route::get('/{record}', fn (): null => null)->name('view');
            });

        Route::name('filament.admin.resources.clients.')
            ->prefix('admin/clients')
            ->group(function (): void {
                Route::get('/', fn (): null => null)->name('index');
                Route::get('/{record}', fn (): null => null)->name('view');
            });

        Artisan::call('migrate', ['--force' => true]);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../vendor/masyukai/chip/database/migrations');
    }

    protected function getPackageProviders($app): array
    {
        return [
            \Filament\Support\SupportServiceProvider::class,
            \Filament\Actions\ActionsServiceProvider::class,
            \Filament\Forms\FormsServiceProvider::class,
            \Filament\Infolists\InfolistsServiceProvider::class,
            \Filament\Notifications\NotificationsServiceProvider::class,
            \Filament\Schemas\SchemasServiceProvider::class,
            \Filament\Tables\TablesServiceProvider::class,
            \Filament\Widgets\WidgetsServiceProvider::class,
            \BladeUI\Icons\BladeIconsServiceProvider::class,
            \BladeUI\Heroicons\BladeHeroiconsServiceProvider::class,
            \Filament\FilamentServiceProvider::class,
            \Livewire\LivewireServiceProvider::class,
            FilamentChipServiceProvider::class,
            \MasyukAI\Chip\ChipServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
    }
}
