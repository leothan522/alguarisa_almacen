<?php

namespace App\Providers\Filament;

use App\Http\Middleware\AccessPanel;
use App\Http\Middleware\UserActive;
use Filament\Actions\Action;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class DashboardPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('dashboard')
            ->path('dashboard')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets($this->widget())
            ->middleware(middleware: [
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                'verified',
                AccessPanel::class,
                UserActive::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->favicon(asset('favicons/favicon-128x128.png'))
            ->userMenuItems([
                Action::make('settings')
                    ->label(__('Settings'))
                    ->url(fn (): string => route('profile.edit'))
                    ->icon(Heroicon::OutlinedCog)
                    ->extraAttributes(['onclick' => "Alpine.store('loader').show()"]),
                'logout' => fn (Action $action) => $action
                    ->label(__('Log out'))
                    ->icon(Heroicon::ArrowRightStartOnRectangle)
                    ->extraAttributes(['onclick' => "Alpine.store('loader').show()"]),
            ])
            ->resourceCreatePageRedirect('index')
            ->sidebarCollapsibleOnDesktop()
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): string => view('filament.footer')
            )
            ->renderHook(
                PanelsRenderHook::HEAD_START,
                fn (): string => view('filament.loader-css')
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): string => view('filament.loader-html')
            );
    }

    protected function widget(): array
    {
        $widgets = [
            AccountWidget::class,
        ];

        if (config('app.filament_info_widget')) {
            $widgets[] = FilamentInfoWidget::class;
        }

        return $widgets;
    }
}
