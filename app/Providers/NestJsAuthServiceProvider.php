<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use App\Auth\NestJsUserProvider;
use App\Auth\NestJsAuthGuard;

class NestJsAuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register our custom user provider driver
        Auth::provider('nestjs', function ($app, array $config) {
            return new NestJsUserProvider();
        });

        // Register our custom guard
        Auth::extend('nestjs', function ($app, $name, array $config) {
            return new NestJsAuthGuard(
                Auth::createUserProvider($config['provider'] ?? 'nestjs'),
                $app->make('request')
            );
        });
    }
}
