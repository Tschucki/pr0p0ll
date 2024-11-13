<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Filament\Pages\Login;
use Cog\Laravel\Ban\Http\Middleware\ForbidBannedUser;
use Filament\Enums\ThemeMode;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Illuminate\Contracts\View\View;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Leandrocfe\FilamentApexCharts\FilamentApexChartsPlugin;

class Pr0p0llPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        Filament::registerRenderHook('panels::global-search.after',
            static fn (): View => view('filament.header.aftersearch'),
        );

        return $panel
            ->default()
            ->id('pr0p0ll')
            ->path('pr0p0ll')
            ->login()
            ->emailVerification(isRequired: false)
            ->colors([
                'primary' => '#ee4d2e',
            ])
            ->viteTheme('resources/css/filament/pr0p0ll/theme.css')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->login(Login::class)
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->plugins([FilamentApexChartsPlugin::make()])
            ->userMenuItems([
                MenuItem::make()->label('Startseite')->url('/')->icon('heroicon-o-home'),
                MenuItem::make()->label('Impressum')->url('/impressum')->icon('heroicon-o-home-modern'),
                MenuItem::make()->label('Datenschutz')->url('/datenschutz')->icon('heroicon-o-shield-check'),
            ])
            ->darkMode(isForced: true)
            ->font('Inter')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                ForbidBannedUser::class,
            ])
            ->brandLogo(fn () => view('filament.admin.logo'))
            ->brandLogoHeight('auto')
            ->defaultThemeMode(ThemeMode::Dark)
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
