<?php

namespace App\Filament\Supplier\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Inventory;

class ExpiringMedicinesWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $warehouseId = auth('supplier')->user()->warehouseId;

        $expiringSoon = Inventory::where('warehouse_id', $warehouseId)
            ->whereBetween('expiry_date', [now(), now()->addMonths(2)])
            ->count();

        $expired = Inventory::where('warehouse_id', $warehouseId)
            ->where('expiry_date', '<', now())
            ->count();

        return [
            Stat::make('Expiring Soon', $expiringSoon)
                ->description('Medicines expiring within 2 months')
                ->icon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Expired Medicines', $expired)
                ->description('Medicines that have expired')
                ->icon('heroicon-m-exclamation-circle')
                ->color('danger'),
        ];
    }
}
