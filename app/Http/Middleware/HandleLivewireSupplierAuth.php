<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HandleLivewireSupplierAuth
{
    public function handle(Request $request, Closure $next)
    {
        // If this is a Livewire request in the supplier panel
        if ($request->hasHeader('X-Livewire') && $request->is('supplier/*')) {
            // Force Livewire to use the supplier guard
            Auth::shouldUse('supplier');
        }

        return $next($request);
    }
}
