<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\NestJsAuthUser;
use App\Models\Inventory;
use App\Models\Pharmacy;

class PharmacyInventoryController extends Controller
{
    protected NestJsAuthUser $authService;

    public function __construct(
        NestJsAuthUser $authService
    ) {
        $this->authService = $authService;
    }

    public function index(Request $request)
    {
        // First, verify the user is authenticated
        $payload = $this->authService->getPayload();
        if (!$payload) {
            Log::error('Invalid token when fetching pharmacy inventory');
            return response()->json([
                'error' => 'Authentication token missing or invalid'
            ], 401);
        }

        // Get pharmacy_id from payload or query parameter
        $pharmacyId = $request->query('pharmacy_id');

        // If not provided in query, get from auth payload
        if (!$pharmacyId) {
            $pharmacyId = $this->authService->getPharmacyId();
            Log::info('Using pharmacy_id from auth payload', ['pharmacy_id' => $pharmacyId]);
        }

        if (!$pharmacyId) {
            Log::error('Pharmacy ID missing in inventory request');
            return response()->json([
                'error' => 'Pharmacy ID is required'
            ], 400);
        }

        try {
            // Fetch inventory for the pharmacy
            $inventory = Inventory::with('medicine')
                ->where('pharmacy_id', $pharmacyId)
                ->where('location_type', 'PHARMACY')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'medicine_id' => $item->medicine_id,
                        'medicine' => [
                            'id' => $item->medicine->id,
                            'name' => $item->medicine->name,
                            'titer' => $item->medicine->titer ?? ''
                        ],
                        'quantity' => $item->quantity,
                        'cost_price' => $item->cost_price,
                        'selling_price' => $item->selling_price
                    ];
                })
                ->toArray();

            Log::info('Pharmacy inventory fetched successfully', [
                'pharmacy_id' => $pharmacyId,
                'item_count' => count($inventory)
            ]);

            return response()->json($inventory);
        } catch (\Exception $e) {
            Log::error('Failed to fetch pharmacy inventory', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'pharmacy_id' => $pharmacyId
            ]);

            return response()->json([
                'error' => 'Failed to fetch inventory: ' . $e->getMessage()
            ], 500);
        }
    }
}
