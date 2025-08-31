<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Illuminate\Contracts\Auth\StatefulGuard;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;

class UserLogin extends BaseLogin
{
    protected static string $routePath = 'user/signin';
    protected static ?string $title = 'Pharmacy Login';

    public function authenticate(): ?LoginResponse
    {
        $data = $this->form->getState();

        // Call NestJS API
        $response = Http::post('http://localhost:3333/auth/user/signin', [
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        $token = $response->json('access_token');
        if (! $response->successful() || ! $token) {
            throw ValidationException::withMessages([
                'email' => 'Invalid credentials from API.',
            ]);
        }

        $user = User::where('email', $data['email'])->first();
        if (! $user) {
            throw ValidationException::withMessages([
                'email' => 'User not found in local DB.',
            ]);
        }

        Auth::guard('pharmacy')->login($user);
        request()->session()->regenerate();
        session()->put('nestjs_token', $token);
        session()->save();

        return app(LoginResponse::class);
    }
    protected function getRedirectUrl(): string
    {
        return url('/dashboard');
    }

    public function getFooter(): ?string
    {
        return '<a href="' . route('filament.auth.pages.supplierlogin') . '" class="text-primary-600 hover:underline">Are you a supplier?</a>';
    }
}
