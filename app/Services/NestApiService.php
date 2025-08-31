<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class NestApiService
{
    protected string $base;
    protected ?string $token;

    public function __construct()
    {
        $this->base = config('services.nestjs.url');
        $this->token = Session::get('nestjs_token');
    }

    protected function headers(): array
    {
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];

        if ($this->token) {
            $headers['Authorization'] = 'Bearer ' . $this->token;
        }

        return $headers;
    }

    public function createPurchaseOrder(array $payload)
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->post($this->base . '/purchase-orders', $payload);

            return $response->throw()->json();
        } catch (\Throwable $e) {
            Log::error('Nest createPurchaseOrder failed: ' . $e->getMessage(), [
                'payload' => $payload,
                'response' => $e->hasResponse() ? $e->response->json() : null
            ]);
            throw $e;
        }
    }

    public function updatePurchaseStatus(int $id, array $payload)
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->put($this->base . "/purchase-orders/{$id}/status", $payload);

            return $response->throw()->json();
        } catch (\Throwable $e) {
            Log::error('Nest updatePurchaseStatus failed: ' . $e->getMessage(), [
                'id' => $id,
                'payload' => $payload,
                'response' => $e->hasResponse() ? $e->response->json() : null
            ]);
            throw $e;
        }
    }
}
