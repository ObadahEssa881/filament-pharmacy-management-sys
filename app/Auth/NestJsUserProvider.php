<?php

// namespace App\Auth;

// use Illuminate\Contracts\Auth\Authenticatable;
// use Illuminate\Contracts\Auth\UserProvider;
// use Illuminate\Support\Facades\Http;
// use Illuminate\Support\Facades\Log;
// use Illuminate\Contracts\Hashing\Hasher;

// class NestJsUserProvider implements UserProvider
// {
//     protected $hasher;
//     protected $model;

//     public function __construct(Hasher $hasher = null, $model = null)
//     {
//         $this->hasher = $hasher;
//         $this->model = $model;
//     }

//     public function retrieveById($identifier)
//     {
//         return null;
//     }

//     public function retrieveByToken($identifier, $token)
//     {
//         return null;
//     }

//     public function updateRememberToken(Authenticatable $user, $token)
//     {
//         // Not used
//     }

//     public function retrieveByCredentials(array $credentials)
//     {
//         Log::info('Attempting authentication with NestJS backend', [
//             'email' => $credentials['email']
//         ]);

//         try {
//             // First try pharmacy user login
//             $userResponse = Http::timeout(10)->post(
//                 config('services.nestjs.url') . '/auth/user/signin',
//                 [
//                     'email' => $credentials['email'],
//                     'password' => $credentials['password'],
//                 ]
//             );

//             Log::info('User login response', [
//                 'status' => $userResponse->status(),
//                 'body' => $userResponse->body()
//             ]);

//             if ($userResponse->successful()) {
//                 $token = $userResponse->json('access_token');
//                 $payload = $this->decodeJwt($token);

//                 Log::info('User authenticated successfully', $payload);

//                 session([
//                     'nestjs_token' => $token,
//                     'token_expiry' => $payload['exp'],
//                     'user_payload' => $payload
//                 ]);

//                 return new NestJsUser($payload);
//             }
//         } catch (\Exception $e) {
//             Log::error('User login error', [
//                 'message' => $e->getMessage(),
//                 'trace' => $e->getTraceAsString()
//             ]);
//         }

//         try {
//             // If user login failed, try supplier login
//             $supplierResponse = Http::timeout(10)->post(
//                 config('services.nestjs.url') . '/auth/supplier/signin',
//                 [
//                     'email' => $credentials['email'],
//                     'password' => $credentials['password'],
//                 ]
//             );

//             Log::info('Supplier login response', [
//                 'status' => $supplierResponse->status(),
//                 'body' => $supplierResponse->body()
//             ]);

//             if ($supplierResponse->successful()) {
//                 $token = $supplierResponse->json('access_token');
//                 $payload = $this->decodeJwt($token);

//                 Log::info('Supplier authenticated successfully', $payload);

//                 session([
//                     'nestjs_token' => $token,
//                     'token_expiry' => $payload['exp'],
//                     'user_payload' => $payload
//                 ]);

//                 return new NestJsUser($payload);
//             }
//         } catch (\Exception $e) {
//             Log::error('Supplier login error', [
//                 'message' => $e->getMessage(),
//                 'trace' => $e->getTraceAsString()
//             ]);
//         }

//         Log::warning('Authentication failed for email: ' . $credentials['email']);
//         return null;
//     }

//     public function validateCredentials(Authenticatable $user, array $credentials)
//     {
//         return true;
//     }

//     public function rehashPasswordIfRequired(
//         Authenticatable $user,
//         array $credentials,
//         bool $force = false
//     ): bool {
//         return false;
//     }

//     private function decodeJwt(string $token): array
//     {
//         $parts = explode('.', $token);
//         if (count($parts) !== 3) {
//             throw new \Exception('Invalid JWT format');
//         }

//         return json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1])), true);
//     }
// }
// ///////////////////////////////////////////////////////////////////////////////////////////////////
// ///////////////////////////////////////////////////////////////////////////////////////////////////
// ///////////////////////////////////////////////////////////////////////////////////////////////////
// ///////////////////////////////////////////////////////////////////////////////////////////////////
// ///////////////////////////////////////////////////////////////////////////////////////////////////


namespace App\Providers\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\Http;
use App\Models\NestJsUser;

class NestJsUserProvider implements UserProvider
{
    protected $model;

    public function __construct($model)
    {
        $this->model = $model;
    }

    public function retrieveById($identifier): ?Authenticatable
    {
        // This won't be used since we're authenticating via API
        return null;
    }

    public function retrieveByToken($identifier, $token): ?Authenticatable
    {
        try {
            // Decode the JWT to get user ID
            $decoded = \Firebase\JWT\JWT::decode(
                $token,
                new \Firebase\JWT\Key(config('services.nestjs.secret'), 'HS256')
            );

            // Create user model from decoded token
            return new $this->model([
                'id' => $decoded->id,
                'email' => $decoded->email,
                'role' => $decoded->role,
                'pharmacy_id' => $decoded->pharmacy_id ?? null,
                'warehouse_id' => $decoded->warehouse_id ?? null
            ]);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function updateRememberToken(Authenticatable $user, $token): void
    {
        // Not needed for JWT authentication
    }

    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        // Call NestJS API to authenticate
        $response = Http::post(config('services.nestjs.url') . '/auth/login', [
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ]);

        if ($response->failed() || !isset($response['access_token'])) {
            return null;
        }

        // Store token in session
        session(['nestjs_token' => $response['access_token']]);

        // Decode token to get user data
        $decoded = \Firebase\JWT\JWT::decode(
            $response['access_token'],
            new \Firebase\JWT\Key(config('services.nestjs.secret'), 'HS256')
        );

        // Create user model
        return new $this->model([
            'id' => $decoded->id,
            'email' => $decoded->email,
            'role' => $decoded->role,
            'pharmacy_id' => $decoded->pharmacy_id ?? null,
            'warehouse_id' => $decoded->warehouse_id ?? null
        ]);
    }

    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        // Credentials were already validated by NestJS
        return true;
    }
    public function rehashPasswordIfRequired(
        Authenticatable $user,
        array $credentials,
        bool $force = false
    ): bool {
        return false;
    }
}
