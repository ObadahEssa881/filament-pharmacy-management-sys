<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\PurchaseService;
use App\Services\NestJsAuthService;
use App\Services\NestJsAuthUser;

class WarehouseInventoryController extends Controller
{
    protected PurchaseService $purchaseService;
    protected NestJsAuthUser $authService;

    public function __construct(
        PurchaseService $purchaseService,
        NestJsAuthUser $authService
    ) {
        $this->purchaseService = $purchaseService;
        $this->authService = $authService;
    }

    public function index(Request $request)
    {
        $warehouseId = $request->query('warehouse_id');

        if (!$warehouseId) {
            Log::error('Warehouse ID missing in inventory request');
            return response()->json([
                'error' => 'Warehouse ID is required'
            ], 400);
        }

        // Verify user is authenticated
        if (!$this->authService->getPayload()) {
            Log::error('Invalid token when fetching warehouse inventory', [
                'warehouse_id' => $warehouseId
            ]);

            return response()->json([
                'error' => 'Authentication token missing or invalid'
            ], 401);
        }

        try {
            // Fetch inventory using Laravel service
            $inventory = $this->purchaseService->getWarehouseInventory($warehouseId);

            Log::info('Warehouse inventory fetched successfully', [
                'warehouse_id' => $warehouseId,
                'item_count' => count($inventory)
            ]);

            return response()->json($inventory);
        } catch (\Exception $e) {
            Log::error('Failed to fetch warehouse inventory', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'warehouse_id' => $warehouseId
            ]);

            return response()->json([
                'error' => 'Failed to fetch inventory: ' . $e->getMessage()
            ], 500);
        }
    }
    public function updateStatus(Request $request, int $id)
    {
        $request->validate([
            'status' => 'required|string|in:PENDING,PROCESSING,SHIPPED,DELIVERED,CANCELLED',
        ]);

        try {
            $order = $this->purchaseService->updateStatus($id, $request->input('status'));

            return response()->json([
                'message' => 'Order status updated successfully',
                'order' => $order,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
