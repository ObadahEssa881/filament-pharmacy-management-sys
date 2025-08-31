<?php

namespace App\Auth;

use Illuminate\Contracts\Auth\Authenticatable;

class NestJsUser implements Authenticatable
{
    protected $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function getAuthIdentifierName()
    {
        return 'id';
    }

    public function getAuthIdentifier()
    {
        return $this->payload['id'];
    }

    public function getAuthPassword()
    {
        return null;
    }

    public function getAuthPasswordName()
    {
        // REQUIRED METHOD - WAS MISSING
        return 'password';
    }

    public function getRememberToken()
    {
        return null;
    }

    public function setRememberToken($value)
    {
        // Not used
    }

    public function getRememberTokenName()
    {
        return 'remember_token';
    }

    // Custom getters for JWT payload
    public function role(): string
    {
        return $this->payload['role'];
    }

    public function pharmacyId(): ?int
    {
        return $this->payload['pharmacy_id'] ?? null;
    }

    public function warehouseId(): ?int
    {
        return $this->payload['warehouse_id'] ?? null;
    }

    public function type(): string
    {
        return $this->payload['type'];
    }

    public function __get($name)
    {
        return $this->payload[$name] ?? null;
    }
}
