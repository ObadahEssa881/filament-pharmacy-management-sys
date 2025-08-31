<?php

namespace App\Filament\Supplier\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SupplierReportStats extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $supplierId = auth('supplier')->id();
        $warehouseId = auth('supplier')->user()->warehouse_id;

        return [
            Stat::make('Total Orders', \App\Models\PurchaseOrder::where('supplier_id', $supplierId)->count()),
            Stat::make('Total Invoices', \App\Models\Invoice::where('supplier_id', $supplierId)->count()),
            Stat::make('Stock in Warehouses', \App\Models\Inventory::where('warehouse_id', $warehouseId)->sum('quantity')),
        ];
    }
}
