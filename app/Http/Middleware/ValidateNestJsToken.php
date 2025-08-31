<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ValidateNestJsToken
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::guard('filament')->check()) {
            $expiry = session('token_expiry', 0);

            // Check if token is expired (with 5 minute buffer)
            if (time() > ($expiry - 300)) {
                Auth::guard('filament')->logout();
                session()->invalidate();

                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Token expired'], 401);
                }

                return redirect()->route('filament.auth.login');
            }
        }

        return $next($request);
    }
}
