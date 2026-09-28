<?php

namespace App\Providers\Filament;

use App\Models\Setting;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->profile()
            // The admin is the light version of the public (dark) site theme.
            ->darkMode(false)
            ->font('Inter')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->colors([
                'primary' => Color::hex('#6D5DF5'),
                'gray' => [
                    50 => '#F7F7FD',
                    100 => '#EFEFFA',
                    200 => '#E2E2F3',
                    300 => '#CFD0E6',
                    400 => '#A7AAC6',
                    500 => '#8E91B0',
                    600 => '#6F7295',
                    700 => '#4A4D75',
                    800 => '#262A4D',
                    900 => '#14153A',
                    950 => '#0A0B1A',
                ],
            ])
            ->brandName(fn () => $this->setting()?->brand_name ?: config('app.name'))
            ->brandLogo(fn () => view('filament.brand'))
            ->brandLogoHeight('2rem')
            ->favicon(fn () => $this->setting()?->getFirstMediaUrl('favicon') ?: null)
            ->maxContentWidth(Width::Full)
            ->spa()
            ->breadcrumbs()
            ->unsavedChangesAlerts()
            ->sidebarCollapsibleOnDesktop()
            // Two-factor: optional locally, forced on for every admin in production (ADMIN_2FA_REQUIRED=true).
            ->multiFactorAuthentication(
                [AppAuthentication::make()->recoverable()],
                isRequired: (bool) config('portfolio.admin.require_2fa'),
            )
            ->navigationGroups([
                NavigationGroup::make('Content'),
                NavigationGroup::make('Portfolio'),
                NavigationGroup::make('About page'),
                NavigationGroup::make('Inbox'),
                NavigationGroup::make('Site'),
            ])
            ->renderHook(PanelsRenderHook::USER_MENU_BEFORE, fn () => Blade::render('@livewire(\App\Livewire\GlobalSettings::class)'))
            ->renderHook(PanelsRenderHook::SCRIPTS_AFTER, fn () => new HtmlString(view('filament.hooks.sidebar-accordion')->render()))
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
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

    private function setting(): ?Setting
    {
        static $setting = false;

        if ($setting === false) {
            $setting = Schema::hasTable('settings') ? Setting::query()->first() : null;
        }

        return $setting;
    }
}
