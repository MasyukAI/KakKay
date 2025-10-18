<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use AIArmada\FilamentCart\FilamentCart;
use AIArmada\FilamentChip\FilamentChip;
use App\Filament\Pages\Auth\Login;
use Asmit\ResizedColumn\ResizedColumnPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

final class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('')
            ->domain('admin.kakkay.test')
            ->login(Login::class)
            ->darkMode(isForced: true)
            ->colors([
                'primary' => Color::hex('#6A00F4'), // Purple from theme
                'danger' => Color::hex('#E0115F'),   // Ruby
                'success' => Color::hex('#10b981'),
                'warning' => Color::hex('#f59e0b'),
                'info' => Color::hex('#D100D1'),     // Magenta
            ])
            ->brandName('Kak Kay')
            ->topNavigation()
            ->maxContentWidth(Width::Full)
            ->font('Poppins')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([

            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                // AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->plugins([
                ResizedColumnPlugin::make(),
                FilamentCart::make(),
                FilamentChip::make(),
            ])
            ->spa(hasPrefetching: true)
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
