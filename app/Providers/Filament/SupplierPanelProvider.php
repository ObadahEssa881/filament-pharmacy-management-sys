<?php

namespace App\Providers\Filament;

use App\Filament\Supplier\Pages\Dashboard;
use App\Filament\Supplier\Pages\SupplierReportPage;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Widgets;
use Filament\Pages;
use Filament\Support\Colors\Color;

class SupplierPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('supplier')
            ->path('supplier')
            ->authGuard('supplier')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(
                in: app_path('Filament/Supplier/Resources'),
                for: 'App\\Filament\\Supplier\\Resources'
            )
            ->discoverPages(
                in: app_path('Filament/Supplier/Pages'),
                for: 'App\\Filament\\Supplier\\Pages'
            )
            ->pages([
                Dashboard::class, // ✅ Ensure Dashboard is explicitly registered
                SupplierReportPage::class
            ])
            ->discoverWidgets(
                in: app_path('Filament/Supplier/Widgets'),
                for: 'App\\Filament\\Supplier\\Widgets'
            )
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                \Illuminate\Cookie\Middleware\EncryptCookies::class,
                \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
                \Illuminate\Session\Middleware\StartSession::class,
                \Illuminate\View\Middleware\ShareErrorsFromSession::class,
                \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
                \Illuminate\Routing\Middleware\SubstituteBindings::class,
                \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
            ])
            ->authMiddleware([
                \Filament\Http\Middleware\Authenticate::class,
            ]);
    }
}
