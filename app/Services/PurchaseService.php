<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Invoice;
use App\Models\Inventory;

class PurchaseService
{
    protected NestJsAuthUser $authService;

    public function __construct()
    {
        $this->authService = app(NestJsAuthUser::class);
    }

    /**
     * Create a purchase order with items and invoice in one transaction
     */
    public function create(array $dto): PurchaseOrder
    {
        $this->authService->validatePharmacyAccess();
        $pharmacyId = $this->authService->getPharmacyId();

        if (empty($dto['items'])) {
            throw ValidationException::withMessages([
                'items' => 'Purchase must include at least one item'
            ]);
        }

        $totalAmount = 0;
        $validatedItems = [];

        foreach ($dto['items'] as $index => $item) {
            $medicineId = $item['medicine_id'] ?? null;
            $quantity   = $item['quantity'] ?? null;
            $unitPrice  = $item['unit_price'] ?? null;

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

            $validatedItems[] = [
                'medicine_id' => (int) $medicineId,
                'quantity'    => (int) $quantity,
                'unit_price'  => (float) $unitPrice
            ];

            $totalAmount += $quantity * $unitPrice;
        }

        return DB::transaction(function () use ($dto, $pharmacyId, $validatedItems, $totalAmount) {
            $order = PurchaseOrder::create([
                'supplier_id' => $dto['supplier_id'],
                'status'      => 'PENDING',
                'pharmacy_id' => $pharmacyId
            ]);

            foreach ($validatedItems as $item) {
                PurchaseOrderItem::create([
                    'order_id'    => $order->id,
                    'medicine_id' => $item['medicine_id'],
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $item['unit_price']
                ]);
            }

            Invoice::create([
                'order_id'       => $order->id,
                'supplier_id'    => $dto['supplier_id'],
                'total_amount'   => $totalAmount,
                'payment_status' => 'UNPAID'
            ]);

            return $order;
        });
    }

    /**
     * Update purchase order status with proper inventory adjustments
     */
    public function updateStatus(int $id, string $newStatus): PurchaseOrder
    {
        $this->authService->validatePharmacyAccess();
        $pharmacyId  = $this->authService->getPharmacyId();
        $warehouseId = $this->authService->getWarehouseId();

        return DB::transaction(function () use ($id, $newStatus, $pharmacyId, $warehouseId) {
            $order = PurchaseOrder::with(['purchaseOrderItems.medicine', 'invoice'])->find($id);

            if (!$order) {
                throw new \Exception('Purchase order not found');
            }
            if ($order->pharmacy_id != $pharmacyId) {
                throw new \Exception('Not authorized for this pharmacy');
            }
            if ($order->status === $newStatus) {
                return $order; // no change
            }

            $items = $order->purchaseOrderItems;

            // 🚦 Status transitions
            if ($order->status === 'PENDING' && $newStatus === 'PROCESSING') {
                foreach ($items as $item) {
                    $warehouseInventory = Inventory::where([
                        'medicine_id'   => $item->medicine_id,
                        'warehouse_id'  => $warehouseId,
                        'location_type' => 'WAREHOUSE'
                    ])->first();

                    if (!$warehouseInventory || $warehouseInventory->quantity < $item->quantity) {
                        throw new \Exception("Not enough stock in warehouse for medicine ID {$item->medicine_id}");
                    }

                    $warehouseInventory->decrement('quantity', $item->quantity);
                }
            } elseif ($order->status === 'PROCESSING' && $newStatus === 'SHIPPED') {
                // nothing to adjust
            } elseif ($order->status === 'SHIPPED' && $newStatus === 'DELIVERED') {
                foreach ($items as $item) {
                    $pharmacyInventory = Inventory::where([
                        'medicine_id'   => $item->medicine_id,
                        'pharmacy_id'   => $pharmacyId,
                        'location_type' => 'PHARMACY'
                    ])->first();

                    if ($pharmacyInventory) {
                        $pharmacyInventory->increment('quantity', $item->quantity);
                        $pharmacyInventory->cost_price   = $item->unit_price;
                        $pharmacyInventory->last_updated = now();
                        $pharmacyInventory->save();
                    } else {
                        Inventory::create([
                            'medicine_id'   => $item->medicine_id,
                            'pharmacy_id'   => $pharmacyId,
                            'location_type' => 'PHARMACY',
                            'quantity'      => $item->quantity,
                            'cost_price'    => $item->unit_price,
                            'selling_price' => $item->unit_price * 1.2,
                            'expiry_date'   => now()->addYear()
                        ]);
                    }
                }

                $order->invoice?->update(['payment_status' => 'PAID']);
            } elseif ($newStatus === 'CANCELLED') {
                // restore warehouse if needed
                if (in_array($order->status, ['PROCESSING', 'SHIPPED'])) {
                    foreach ($items as $item) {
                        $warehouseInventory = Inventory::where([
                            'medicine_id'   => $item->medicine_id,
                            'warehouse_id'  => $warehouseId,
                            'location_type' => 'WAREHOUSE'
                        ])->first();

                        if ($warehouseInventory) {
                            $warehouseInventory->increment('quantity', $item->quantity);
                        } else {
                            Inventory::create([
                                'medicine_id'   => $item->medicine_id,
                                'warehouse_id'  => $warehouseId,
                                'location_type' => 'WAREHOUSE',
                                'quantity'      => $item->quantity,
                                'cost_price'    => $item->unit_price,
                                'selling_price' => $item->unit_price * 1.2,
                                'expiry_date'   => now()->addYear()
                            ]);
                        }
                    }
                }

                // ✅ DO NOT DELETE items & invoice - keep for auditing
                // Just update status to CANCELLED
            } else {
                throw new \Exception("Invalid status transition from {$order->status} to {$newStatus}");
            }

            $order->update(['status' => $newStatus]);
            return $order->fresh();
        });
    }

    /**
     * Get warehouse inventory for pharmacy owners
     */
    public function getWarehouseInventory(int $warehouseId): array
    {
        $this->authService->validatePharmacyAccess();

        return Inventory::with('medicine')
            ->where('warehouse_id', $warehouseId)
            ->where('location_type', 'WAREHOUSE')
            ->get()
            ->map(function ($item) {
                return [
                    'id'         => $item->id,
                    'medicine_id' => $item->medicine_id,
                    'medicine'   => [
                        'id'   => $item->medicine->id,
                        'name' => $item->medicine->name,
                        'titer' => $item->medicine->titer
                    ],
                    'quantity'   => $item->quantity,
                    'unit_price' => $item->cost_price
                ];
            })
            ->toArray();
    }

    /**
     * Paginate purchase orders for the current pharmacy
     */
    public function paginate(int $page = 1, int $limit = 10): array
    {
        $this->authService->validatePharmacyAccess();
        $pharmacyId = $this->authService->getPharmacyId();

        $offset = ($page - 1) * $limit;

        $orders = PurchaseOrder::with(['purchaseOrderItems.medicine', 'invoice'])
            ->where('pharmacy_id', $pharmacyId)
            ->orderBy('order_date', 'desc')
            ->skip($offset)
            ->take($limit)
            ->get();

        $total = PurchaseOrder::where('pharmacy_id', $pharmacyId)->count();

        return [
            'orders' => $orders,
            'meta'   => [
                'total' => $total,
                'page'  => $page,
                'limit' => $limit,
                'pages' => ceil($total / $limit)
            ]
        ];
    }
}
