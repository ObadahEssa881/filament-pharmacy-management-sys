<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        $this->registerPolicies();

        Gate::guessPolicyNamesUsing(function ($modelClass) {
            // Let Laravel resolve to SupplierPolicy instead of default
            return 'App\\Policies\\Supplier\\' . class_basename($modelClass) . 'Policy';
        });

        // 👇 Force Gate to use the supplier guard inside the supplier panel
        Gate::before(function ($user, $ability) {
            if (Auth::guard('supplier')->check()) {
                Auth::shouldUse('supplier');
            }
        });
        Gate::before(function ($user, $ability) {
            if (Auth::guard('supplier')->check()) {
                Auth::shouldUse('supplier');
            }
        });
    }
}
