<?php

namespace App\Filament\Pages\Auth;

use App\Models\Supplier;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Filament\Facades\Filament;

class SupplierLogin extends BaseLogin
{
    protected static string $routePath = 'signin';
    protected static ?string $title = 'Supplier Portal Login';

    public function authenticate(): \Filament\Http\Responses\Auth\Contracts\LoginResponse
    {
        $data = $this->form->getState();

        $response = Http::post('http://localhost:3333/auth/supplier/signin', [
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        $token = $response->json('access_token');

        if (! $response->successful() || ! $token) {
            throw ValidationException::withMessages([
                'email' => 'Invalid credentials from API.',
            ]);
        }

        $supplier = Supplier::where('email', $data['email'])->first();

        if (! $supplier) {
            throw ValidationException::withMessages([
                'email' => 'User not found in local DB.',
            ]);
        }

        // CRITICAL: Log in to the Filament supplier panel context
        Filament::getPanel('supplier')->auth()->login($supplier);

        // Save session token
        session()->regenerate();
        session()->put('nestjs_token', $token);
        session()->save();

        return app(\Filament\Http\Responses\Auth\Contracts\LoginResponse::class);
    }

    protected function getRedirectUrl(): string
    {
        return '/supplier/dashboard';
    }

    public function getFooter(): ?string
    {
        return '<a href="' . route('filament.auth.pages.login') . '" class="text-primary-600 hover:underline">Are you a user?</a>';
    }
}