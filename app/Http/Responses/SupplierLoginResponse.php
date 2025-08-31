<?php
// app/Filament/Responses/SupplierLoginResponse.php

// app/Http/Responses/SupplierLoginResponse.php
namespace App\Http\Responses;

use Filament\Http\Responses\Auth\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Http\RedirectResponse;

class SupplierLoginResponse implements LoginResponseContract
{
    public function toResponse($request): RedirectResponse
    {
        return redirect()->intended('/supplier/dashboard');
    }
}

