<?php

namespace App\Services;

use App\Models\Purchaseorder;
use App\Models\Inventory;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;

class SupplierOrderService
{
    /**
     * Handle status transitions initiated by a supplier admin,
     * adjusting inventories accordingly.
     */
    public function transition(Purchaseorder $order, string $to): Purchaseorder
    {
        return DB::transaction(function () use ($order, $to) {
            $from = $order->status;

            // Allowed transitions
            $allowed = [
                'PENDING'    => ['PROCESSING', 'CANCELLED'],
                'PROCESSING' => ['SHIPPED', 'CANCELLED'],
                'SHIPPED'    => ['DELIVERED'],
            ];

            if (!isset($allowed[$from]) || !in_array($to, $allowed[$from])) {
                throw new \Exception("Invalid status transition from {$from} to {$to}");
            }

            // The acting supplier
            /** @var Supplier $supplier */
            $supplier = auth('supplier')->user(); // supplier guard should be active for supplier panel
            if (!$supplier instanceof Supplier) {
                throw new \Exception('Not authenticated as supplier');
            }

            // When moving to PROCESSING: bind supplier and ensure warehouse_id
            if ($from === 'PENDING' && $to === 'PROCESSING') {
                $order->supplier_id = $supplier->id;
                if (empty($order->warehouse_id) && !empty($supplier->warehouseId)) {
                    $order->warehouse_id = $supplier->warehouseId;
                }
                $order->status = 'PROCESSING';
                $order->save();

                Notification::make()->title('Order set to PROCESSING')->success()->send();
                return $order->refresh();
            }

            // When moving to SHIPPED: deduct from supplier warehouse inventory
            if ($from === 'PROCESSING' && $to === 'SHIPPED') {
                if (empty($order->warehouse_id)) {
                    throw new \Exception('Order has no assigned warehouse');
                }

                foreach ($order->purchaseorderitems as $item) {
                    $inv = Inventory::where([
                        'medicine_id'   => $item->medicine_id,
                        'warehouse_id'  => $order->warehouse_id,
                        'location_type' => 'WAREHOUSE',
                    ])->lockForUpdate()->first();

                    if (!$inv || $inv->quantity < $item->quantity) {
                        throw new \Exception('Insufficient stock in warehouse for medicine ID ' . $item->medicine_id);
                    }

                    $inv->decrement('quantity', $item->quantity);
                }

                $order->status = 'SHIPPED';
                $order->save();

                Notification::make()->title('Order set to SHIPPED')->success()->send();
                return $order->refresh();
            }

            // When moving to DELIVERED: increase pharmacy inventory
            if ($from === 'SHIPPED' && $to === 'DELIVERED') {
                foreach ($order->purchaseorderitems as $item) {
                    $inv = Inventory::firstOrCreate([
                        'medicine_id'   => $item->medicine_id,
                        'pharmacy_id'   => $order->pharmacy_id,
                        'location_type' => 'PHARMACY',
                    ], [
                        'quantity'      => 0,
                        'cost_price'    => $item->unit_price,
                        'selling_price' => $item->unit_price * 1.2,
                        'expiry_date'   => now()->addYear(),
                    ]);

                    $inv->increment('quantity', $item->quantity);
                }

                $order->status = 'DELIVERED';
                $order->delivery_date = now();
                $order->save();

                Notification::make()->title('Order set to DELIVERED')->success()->send();
                return $order->refresh();
            }

            // Cancel: if PROCESSING/PENDING
            if (in_array($from, ['PENDING', 'PROCESSING']) && $to === 'CANCELLED') {
                // No stock deducted yet (deductions happen at SHIPPED), so just update status
                $order->status = 'CANCELLED';
                $order->save();

                Notification::make()->title('Order cancelled')->success()->send();
                return $order->refresh();
            }

            // Fallback
            throw new \Exception('Unsupported transition');
        });
    }
}
