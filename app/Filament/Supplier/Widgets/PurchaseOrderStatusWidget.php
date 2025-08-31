<?php

namespace App\Filament\Supplier\Widgets;

use App\Filament\Resources\PurchaseOrderResource;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Purchaseorder;
use App\Models\Supplier;

class PurchaseOrderStatusWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        // 1. Current supplier admin's warehouse
        $warehouseId = auth('supplier')->user()->warehouseId;

        // 2. Get all suppliers in this warehouse
        $supplierIds = Supplier::where('warehouseId', $warehouseId)->pluck('id');

        // 3. Query purchase orders by supplier_id in this set
        $pending = Purchaseorder::whereIn('supplier_id', $supplierIds)
            ->where('status', 'pending')
            ->count();

        $processing = Purchaseorder::whereIn('supplier_id', $supplierIds)
            ->where('status', 'processing')
            ->count();

        $shipped = Purchaseorder::whereIn('supplier_id', $supplierIds)
            ->where('status', 'shipped')
            ->count();

        $delivered = Purchaseorder::whereIn('supplier_id', $supplierIds)
            ->where('status', 'delivered')
            ->count();

        return [
            Stat::make('Pending Orders', $pending)
                ->description('Orders awaiting processing')
                ->icon('heroicon-m-clock')
                ->color('warning')
                ->url(PurchaseOrderResource::getUrl('index', [
                    'tableFilters' => [
                        'status' => ['values' => ['pending']],
                    ]
                ])),

            Stat::make('Processing', $processing)
                ->description('Orders being processed')
                ->icon('heroicon-m-cog')
                ->color('info')
                ->url(PurchaseOrderResource::getUrl('index', [
                    'tableFilters' => [
                        'status' => ['values' => ['processing']],
                    ]
                ])),

            Stat::make('Shipped', $shipped)
                ->description('Orders shipped but not delivered')
                ->icon('heroicon-m-truck')
                ->color('primary')
                ->url(PurchaseOrderResource::getUrl('index', [
                    'tableFilters' => [
                        'status' => ['values' => ['shipped']],
                    ]
                ])),

            Stat::make('Delivered', $delivered)
                ->description('Orders successfully delivered')
                ->icon('heroicon-m-check-badge')
                ->color('success')
                ->url(PurchaseOrderResource::getUrl('index', [
                    'tableFilters' => [
                        'status' => ['values' => ['delivered']],
                    ]
                ])),
        ];
    }
}
