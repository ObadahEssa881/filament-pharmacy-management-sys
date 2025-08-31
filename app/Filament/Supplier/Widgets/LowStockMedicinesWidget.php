<?php

namespace App\Filament\Supplier\Widgets;


use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Inventory;

class LowStockMedicinesWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        // Get medicines with quantity below 5
        $warehouseId = auth('supplier')->user()->warehouse_id;

        $lowStock = Inventory::where('warehouse_id', $warehouseId)
            ->where('quantity', '<=', 5)
            ->count();

        $warningStock = Inventory::where('warehouse_id', $warehouseId)
            ->whereBetween('quantity', [6, 15])
            ->count();


        return [
            Stat::make('Critical Stock', $lowStock)
                ->description('Medicines with 5 or fewer units')
                ->icon('heroicon-m-exclamation-circle')
                ->color('danger')
                ->url(route('filament.admin.resources.inventories.index', [
                    'tableFilters' => [
                        'quantity' => [
                            'maxValue' => 5,
                        ]
                    ]
                ])),

            Stat::make('Low Stock', $warningStock)
                ->description('Medicines with 6-15 units')
                ->icon('heroicon-m-exclamation-triangle')
                ->color('warning')
                ->url(route('filament.admin.resources.inventories.index', [
                    'tableFilters' => [
                        'quantity' => [
                            'minValue' => 6,
                            'maxValue' => 15,
                        ]
                    ]
                ])),
        ];
    }
}
