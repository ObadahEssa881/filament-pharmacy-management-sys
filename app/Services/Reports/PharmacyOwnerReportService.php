<?php

namespace App\Services\Reports;

use App\Models\Purchaseorder;
use App\Models\Purchaseorderitem;
use App\Models\Sale;
use App\Models\Saleitem;
use App\Models\Inventory;
use Illuminate\Support\Facades\DB;

class PharmacyOwnerReportService
{
    public function run(array $filters, int $pharmacyId): array
    {
        $start = $filters['start'] ?? now()->startOfMonth();
        $end   = $filters['end'] ?? now()->endOfMonth();

        // === PURCHASES ===
        $purchases = Purchaseorder::where('pharmacy_id', $pharmacyId)
            ->whereBetween('order_date', [$start, $end])
            ->with('purchaseorderitems')
            ->get();

        $purchaseAmount = $purchases->flatMap->purchaseorderitems->sum(fn($item) => $item->quantity * $item->unit_price);

        // === SALES ===
        $sales = Sale::where('pharmacy_id', $pharmacyId)
            ->whereBetween('sale_date', [$start, $end])
            ->with('saleitems')
            ->get();

        $revenue = $sales->sum('total_amount');
        $ordersCount = $sales->count();
        $itemsSold = $sales->flatMap->saleitems->sum('quantity');
        $avgOrderValue = $ordersCount > 0 ? $revenue / $ordersCount : 0;

        $costOfGoods = $sales->flatMap->saleitems->sum(fn($item) => $item->quantity * $item->cost_price);
        $grossProfit = $revenue - $costOfGoods;
        $expenses = $purchaseAmount; // extend with salaries, rent, etc.
        $netProfit = $grossProfit - $expenses;
        $profitMargin = $revenue > 0 ? ($netProfit / $revenue) * 100 : 0;

        $uniqueCustomers = $sales->pluck('customer_id')->unique()->count();

        // === TOP MEDICINES ===
        $topMedicines = Saleitem::select(
            'medicine_id',
            DB::raw('SUM(quantity) as qty'),
            DB::raw('SUM(quantity * unit_price) as total')
        )
            ->whereIn('sale_id', $sales->pluck('id'))
            ->groupBy('medicine_id')
            ->orderByDesc('qty')
            ->with('medicine')
            ->limit(5)
            ->get();


        $topSuppliers = Purchaseorderitem::select(
            'supplier.warehouseId',
            'warehouse.name as warehouse_name',
            DB::raw('SUM(purchaseorderitem.quantity * purchaseorderitem.unit_price) as total')
        )
            ->join('purchaseorder', 'purchaseorder.id', '=', 'purchaseorderitem.order_id')
            ->join('supplier', 'supplier.id', '=', 'purchaseorder.supplier_id')
            ->join('warehouse', 'warehouse.id', '=', 'supplier.warehouseId')
            ->where('purchaseorder.pharmacy_id', $pharmacyId)
            ->whereBetween('purchaseorder.order_date', [$start, $end])
            ->groupBy('supplier.warehouseId', 'warehouse.name')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(function ($row) {
                return [
                    'name'  => $row->warehouse_name,
                    'total' => $row->total,
                ];
            });


        // === INVENTORY VALUE ===
        $inventoryValue = Inventory::where('pharmacy_id', $pharmacyId)
            ->sum(DB::raw('quantity * cost_price'));

        // === SALES OVER TIME ===
        $salesOverTime = Sale::select(
            DB::raw("DATE(sale_date) as period"),
            DB::raw("SUM(total_amount) as total")
        )
            ->where('pharmacy_id', $pharmacyId)
            ->whereBetween('sale_date', [$start, $end])
            ->groupBy('period')
            ->orderBy('period')
            ->get();
        // === Detailed Sales Records ===
        $salesRecords = Sale::where('pharmacy_id', $pharmacyId)
            ->whereBetween('sale_date', [$start, $end])
            ->withCount('saleitems')
            ->get(['id', 'customer_name', 'sale_date', 'total_amount', 'payment_mode']);

        // === Detailed Purchase Records ===
        $purchaseRecords = Purchaseorder::where('pharmacy_id', $pharmacyId)
            ->dateBetween($start, $end)
            ->withCount('purchaseorderitems')
            ->with('supplier')
            ->get(['id', 'supplier_id', 'order_date', 'status']);

        return [
            'kpis' => [
                'revenue' => $revenue,
                'purchases' => $purchaseAmount,
                'grossProfit' => $grossProfit,
                'expenses' => $expenses,
                'netProfit' => $netProfit,
                'profitMargin' => $profitMargin,
                'ordersCount' => $ordersCount,
                'itemsSold' => $itemsSold,
                'avgOrderValue' => $avgOrderValue,
                'uniqueCustomers' => $uniqueCustomers,
                'suppliersCount' => $topSuppliers->count(),
                'inventoryValue' => $inventoryValue,
            ],
            'topMedicines' => $topMedicines,
            'supplierSpend' => $topSuppliers,
            'salesOverTime' => $salesOverTime,
            'salesRecords'   => $salesRecords,
            'purchaseRecords' => $purchaseRecords,
            'filters' => compact('start', 'end'),
        ];
    }
}
