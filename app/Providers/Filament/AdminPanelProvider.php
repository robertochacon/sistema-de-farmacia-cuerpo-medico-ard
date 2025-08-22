<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use GeoSot\FilamentEnvEditor\FilamentEnvEditorPlugin;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use pxlrbt\FilamentEnvironmentIndicator\EnvironmentIndicatorPlugin;
use Swis\Filament\Backgrounds\FilamentBackgroundsPlugin;
use Swis\Filament\Backgrounds\ImageProviders\MyImages;
use App\Filament\Widgets\MonthlyMovementsChart;
use App\Filament\Widgets\DailyOutputsByPatientTypeChart;
use Illuminate\Support\Facades\Auth;
use Filament\Navigation\MenuItem;
use TomatoPHP\FilamentArtisan\FilamentArtisanPlugin;
use Illuminate\Support\Facades\URL;
class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Green,
            ])
            ->darkMode(false)
            // ->brandLogo(asset('images/armada-logo.png'))
            // validar si la ruta actual es la de login
            ->brandName(Auth::guard('web')->check() ? 'Sistema de Farmacia' : '')
            ->renderHook('panels::auth.login.form.before', fn () => view('filament.auth.brand-header'))
            ->renderHook('panels::body.end', fn () => view('filament.uppercase-inputs'))
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                \App\Filament\Pages\Dashboard::class,
            ])
            ->plugins(array_filter([
                FilamentBackgroundsPlugin::make()
                ->imageProvider(
                    MyImages::make()
                        ->directory('images/backgrounds')
                )
                ->showAttribution(false),
                FilamentEnvEditorPlugin::make()
                    ->navigationGroup('Mantenimiento')
                    ->navigationLabel('Variables de entorno')
                    ->navigationIcon('heroicon-o-cog-8-tooth')
                    ->navigationSort(1)
                    ->slug('env-editor')
                    ->authorize(
                        fn () => Auth::guard('web')->check() && optional(Auth::guard('web')->user())->isAdmin()
                    ),
                EnvironmentIndicatorPlugin::make()
                    ->visible(fn () => Auth::guard('web')->check() && optional(Auth::guard('web')->user())->isAdmin()),
                Auth::guard('web')->check() && optional(Auth::guard('web')->user())->isAdmin() ? FilamentArtisanPlugin::make() : null
            ]))
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // Widgets\AccountWidget::class,
                MonthlyMovementsChart::class,
                DailyOutputsByPatientTypeChart::class,
            ])
            ->userMenuItems([
                MenuItem::make()
                    ->label('Mi perfil')
                    ->icon('heroicon-o-user-circle')
                    ->url(fn () => route('profile.show')),
            ])
            ->navigation(true)
            ->sidebarCollapsibleOnDesktop()
            ->sidebarFullyCollapsibleOnDesktop()
            ->sidebarWidth('full')
            ->topNavigation()
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
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
