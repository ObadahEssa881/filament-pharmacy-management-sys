<?php

namespace App\Filament\Resources\WarehouseResource\Widgets;

use App\Models\Inventory;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class WarehouseStatsWidget extends BaseWidget
{
    protected static ?int $sort = 0;

    protected int | string | array $columnSpan = 'full';

    public ?int $recordId = null;

    protected function getStats(): array
    {
        $inventory = Inventory::where('warehouse_id', $this->recordId)->get();
        $totalValue = $inventory->sum(fn($item) => $item->quantity * $item->cost_price);
        $lowStockItems = $inventory->where('quantity', '<=', 10)->count();
        $expiringSoon = $inventory->where('expiry_date', '<=', now()->addDays(30))->count();

        return [
            Stat::make('Total Inventory Value', Number::currency($totalValue, 'USD'))
                ->description('Current stock value')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('success'),

            Stat::make('Low Stock Items', $lowStockItems)
                ->description('Items with 10 or fewer units')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning'),

            Stat::make('Expiring Soon', $expiringSoon)
                ->description('Items expiring within 30 days')
                ->descriptionIcon('heroicon-m-clock')
                ->color('danger'),
        ];
    }
}
