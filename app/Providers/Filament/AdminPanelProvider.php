<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
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
            ->brandName('Sistema de Gestión de Pedidos')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
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
            ])
            ->renderHook(
                \Filament\View\PanelsRenderHook::BODY_END,
                function (): string {
                    if (! request()->routeIs('filament.admin.auth.login')) {
                        return '';
                    }

                    return \Illuminate\Support\Facades\Blade::render('
                        <style>
                            body {
                                background-image: url("/images/login-bg.jpg");
                                background-size: cover;
                                background-position: center;
                                background-repeat: no-repeat;
                                min-height: 100vh;
                                position: relative;
                            }
                            body::before {
                                content: "";
                                position: absolute;
                                top: 0;
                                left: 0;
                                width: 100%;
                                height: 100%;
                                background: rgba(15, 23, 42, 0.65);
                                z-index: -1;
                            }
                            /* Fix for specific Filament layouts that might override background */
                            .fi-body {
                                background-color: transparent !important;
                            }
                            
                            /* Make Brand Name larger/clearer */
                            .fi-simple-main-header .font-bold {
                                font-size: 1.5rem !important;
                            }

                            /* Make "Iniciar Sesión" (Sign In) smaller */
                            .fi-simple-header-heading {
                                font-size: 1.25rem !important; 
                            }
                        </style>
                    ');
                }
            );
    }
}
