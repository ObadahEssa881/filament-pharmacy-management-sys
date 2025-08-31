<?php

namespace App\Providers;

use App\Services\NestJsAuthUser;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use Filament\Http\Responses\Auth\Contracts\LoginResponse as LoginResponseContract;
use App\Http\Responses\LoginResponse as CustomLoginResponse;
use App\Models\Purchaseorder;
use App\Observers\PurchaseOrderObserver;
use Filament\Navigation\NavigationGroup;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(NestJsAuthUser::class, fn() => new NestJsAuthUser());
        $this->app->singleton(
            LoginResponseContract::class,
            CustomLoginResponse::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register custom navigation group
        NavigationGroup::macro('icon', function (string $icon) {
            $this->icon = $icon;
            return $this;
        });

        NavigationGroup::macro('getIcon', function () {
            return $this->icon ?? 'heroicon-o-folder';
        });
        // Set default string length
        Schema::defaultStringLength(191);

        // Register observers
        Purchaseorder::observe(PurchaseOrderObserver::class);
    }
}
