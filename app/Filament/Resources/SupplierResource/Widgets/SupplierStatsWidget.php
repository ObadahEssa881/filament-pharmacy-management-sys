<?php

namespace App\Filament\Resources\SupplierResource\Widgets;

use App\Models\Inventory;
use App\Models\Supplier;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class SupplierStatsWidget extends BaseWidget
{
    protected static ?int $sort = 0;

    protected int | string | array $columnSpan = 'full';

    public ?int $recordId = null;

    protected function getStats(): array
    {
        // Check if recordId is set
        if (!$this->recordId) {
            return [
                Stat::make('Error', 'Supplier not found')
                    ->description('Please refresh the page')
                    ->color('danger')
                    ->icon('heroicon-m-exclamation-circle'),
            ];
        }

        // Find the supplier
        $supplier = Supplier::find($this->recordId);

        // Check if supplier exists
        if (!$supplier) {
            return [
                Stat::make('Error', 'Supplier not found')
                    ->description('The supplier may have been deleted')
                    ->color('danger')
                    ->icon('heroicon-m-exclamation-circle'),
            ];
        }

        // Get the warehouse associated with this supplier
        $warehouse = $supplier->warehouse;

        // Check if warehouse exists
        if (!$warehouse) {
            return [
                Stat::make('Error', 'Warehouse not found')
                    ->description('This supplier is not associated with a warehouse')
                    ->color('warning')
                    ->icon('heroicon-m-exclamation-triangle'),
            ];
        }

        // Get inventory for this warehouse
        $inventory = Inventory::where('warehouse_id', $warehouse->id)->get();
        $totalValue = $inventory->sum(fn($item) => $item->quantity * $item->cost_price);
        $lowStockItems = $inventory->where('quantity', '<=', 10)->count();
        $expiringSoon = $inventory->where('expiry_date', '<=', now()->addDays(30))->count();

        return [
            Stat::make('Role', $supplier->role)
                ->description('Supplier role')
                ->icon('heroicon-m-identification')
                ->color(match ($supplier->role) {
                    'SUPPLIER_ADMIN' => 'primary',
                    'SUPPLIER_EMPLOYEE' => 'gray',
                    default => 'gray',
                }),

            Stat::make('Warehouse', $warehouse->name)
                ->description('Associated warehouse')
                ->icon('heroicon-m-building-storefront'),

            Stat::make('Total Value', Number::currency($totalValue, 'USD'))
                ->description('Current inventory value')
                ->icon('heroicon-m-currency-dollar'),

            Stat::make('Low Stock', $lowStockItems)
                ->description('Items with 10 or fewer units')
                ->icon('heroicon-m-exclamation-triangle'),

            Stat::make('Expiring Soon', $expiringSoon)
                ->description('Items expiring within 30 days')
                ->icon('heroicon-m-clock'),
        ];
    }
}
