<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Inventory;

class SaleService
{
    protected NestJsAuthUser $authService;

    public function __construct()
    {
        $this->authService = app(NestJsAuthUser::class);
    }

    /**
     * Create a sale with items in one transaction
     */
    public function create(array $dto): Sale
    {
        $this->authService->validatePharmacyAccess();
        $pharmacyId = $this->authService->getPharmacyId();

        if (empty($dto['items'])) {
            throw ValidationException::withMessages([
                'items' => 'Sale must include at least one item'
            ]);
        }

        $totalAmount = 0;
        $validatedItems = [];

        foreach ($dto['items'] as $index => $item) {
            $medicineId = $item['medicine_id'] ?? null;
            $quantity   = $item['quantity'] ?? null;
            $unitPrice  = $item['unit_price'] ?? null;
            $costPrice  = $item['cost_price'] ?? null;

            if (!$medicineId || $medicineId <= 0) {
                throw ValidationException::withMessages([
                    "items.{$index}.medicine_id" => "Item at index {$index} has invalid medicine_id"
                ]);
            }

            if (!is_numeric($quantity) || $quantity <= 0) {
                throw ValidationException::withMessages([
                    "items.{$index}.quantity" => "Item at index {$index} has invalid quantity"
                ]);
            }

            if (!is_numeric($unitPrice) || $unitPrice <= 0) {
                throw ValidationException::withMessages([
                    "items.{$index}.unit_price" => "Item at index {$index} has invalid unit_price"
                ]);
            }

            // Check inventory
            $inventory = Inventory::where([
                'medicine_id' => $medicineId,
                'pharmacy_id' => $pharmacyId,
                'location_type' => 'PHARMACY'
            ])->first();

            if (!$inventory || $inventory->quantity < $quantity) {
                throw ValidationException::withMessages([
                    "items.{$index}.quantity" => "Not enough stock for medicine ID {$medicineId}"
                ]);
            }

            $validatedItems[] = [
                'medicine_id' => (int) $medicineId,
                'quantity'    => (int) $quantity,
                'unit_price'  => (float) $unitPrice,
                'cost_price'  => (float) $costPrice
            ];

            $totalAmount += $quantity * $unitPrice;
        }

        return DB::transaction(function () use ($dto, $pharmacyId, $validatedItems, $totalAmount) {
            $sale = Sale::create([
                'customer_name' => $dto['customer_name'],
                'sale_date'     => $dto['sale_date'] ?? now(),
                'total_amount'  => $totalAmount,
                'payment_mode'  => $dto['payment_mode'],
                'pharmacy_id'   => $pharmacyId
            ]);

            foreach ($validatedItems as $item) {
                SaleItem::create([
                    'sale_id'      => $sale->id,
                    'medicine_id'  => $item['medicine_id'],
                    'quantity'     => $item['quantity'],
                    'unit_price'   => $item['unit_price'],
                    'cost_price'   => $item['cost_price']
                ]);

                // Update inventory
                $inventory = Inventory::where([
                    'medicine_id'   => $item['medicine_id'],
                    'pharmacy_id'   => $pharmacyId,
                    'location_type' => 'PHARMACY'
                ])->first();

                if ($inventory) {
                    $inventory->decrement('quantity', $item['quantity']);

                    // If inventory is zero, you might want to delete it
                    if ($inventory->quantity <= 0) {
                        $inventory->delete();
                    }
                }
            }

            return $sale;
        });
    }
}
