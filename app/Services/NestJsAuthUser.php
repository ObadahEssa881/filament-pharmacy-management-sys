<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class NestJsAuthUser
{
    public function getToken(): ?string
    {
        return session('nestjs_token');
    }

    public function getPayload(): ?object
    {
        $token = $this->getToken();

        if (! $token) return null;

        return JWT::decode($token, new Key(env('NESTJS_JWT_SECRET'), 'HS256'));
    }

    public function getRole(): ?string
    {
        return $this->getPayload()?->role ?? null;
    }

    public function getPharmacyId(): ?int
    {
        return $this->getPayload()?->pharmacy_id ?? null;
    }

    public function getWarehouseId(): ?int
    {
        return $this->getPayload()?->warehouse_id ?? null;
    }

    public function isPharmacyUser(): bool
    {
        return $this->getPharmacyId() !== null;
    }

    public function isSupplierUser(): bool
    {
        return $this->getWarehouseId() !== null;
    }




    public function validatePharmacyAccess(): void
    {
        if (!$this->isPharmacyUser()) {
            throw new \Exception('Must be associated with a pharmacy');
        }
    }
}
