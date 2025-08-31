<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Filament\Pages\Auth\UserLogin;
use App\Filament\Pages\Auth\SupplierLogin;
use App\Http\Controllers\PharmacyInventoryController;
use App\Http\Controllers\WarehouseInventoryController;
use App\Http\Controllers\WarehouseInventoryController as ControllersWarehouseInventoryController;
// use App\Http\Controllers\WarehouseInventoryController;
use App\Services\NestJsAuthUser;
use App\Services\PurchaseService;
use Filament\Facades\Filament;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;
// Route::Filament('supplier')->auth(); 

Route::middleware([
    'web',
    \Illuminate\Cookie\Middleware\EncryptCookies::class,
    \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
    \Illuminate\Session\Middleware\StartSession::class,
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
])->group(function () {

    Route::get('/user/signin', UserLogin::class)->name('filament.user.login');
    Route::get('/supplier/signin', SupplierLogin::class)->name('filament.supplier.login');
    Route::redirect('/login', '/user/signin')->name('login');
    Route::redirect('/supplier/login', '/supplier/signin')->name('supplierlogin');

    Route::get('/api/warehouse/inventory', [WarehouseInventoryController::class, 'index'])
        ->name('api.warehouse.inventory');

    Route::get('/debug/warehouse-inventory', function (Request $request) {
        $warehouseId = $request->query('warehouse_id', 1);

        try {
            $purchaseService = app(PurchaseService::class);
            $inventory = $purchaseService->getWarehouseInventory($warehouseId);

            return response()->json([
                'warehouse_id' => $warehouseId,
                'inventory_count' => count($inventory),
                'inventory' => $inventory
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    })->middleware('auth');

    Route::put('/purchase-orders/{id}/status', [WarehouseInventoryController::class, 'updateStatus'])
        ->name('purchase-orders.update-status');

    Route::get('/api/pharmacy/inventory', [PharmacyInventoryController::class, 'index'])
        ->name('api.pharmacy.inventory');
    // API endpoint to get current pharmacy ID
    Route::get('/api/pharmacy/id', function (Request $request) {
        try {
            $authService = app(NestJsAuthUser::class);
            $pharmacyId = $authService->getPharmacyId();

            if (!$pharmacyId) {
                return response()->json([
                    'error' => 'Pharmacy ID not found for user'
                ], 404);
            }

            return response()->json([
                'pharmacy_id' => $pharmacyId
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get pharmacy ID', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to get pharmacy ID: ' . $e->getMessage()
            ], 500);
        }
    })->middleware('auth');
});
