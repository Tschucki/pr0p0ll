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
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\View\View;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Leandrocfe\FilamentApexCharts\FilamentApexChartsPlugin;

class Pr0p0llPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        Filament::registerRenderHook(
            PanelsRenderHook::USER_MENU_BEFORE,
            static fn (): View => view('filament.header.aftersearch'),
        );

        Filament::registerRenderHook(
            PanelsRenderHook::HEAD_END,
            static fn (): HtmlString => new HtmlString(<<<'HTML'
                <style>
                    .fi-sidebar, .fi-main, .fi-main-ctn { background-color: #161618 !important; }
                    .fi-theme-switcher { display: none; }
                </style>
            HTML),
        );

        return $panel
            ->default()
            ->id('pr0p0ll')
            ->path('pr0p0ll')
            ->login()
            ->databaseNotifications()
            ->emailVerification(isRequired: false)
            ->colors([
                'primary' => '#ee4d2e',
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
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
