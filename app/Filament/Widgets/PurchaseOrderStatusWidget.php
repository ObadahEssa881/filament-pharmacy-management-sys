<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\PurchaseOrderResource;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Purchaseorder;

class PurchaseOrderStatusWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $pharmacyId = auth()->user()->pharmacy_id;

        $pending = Purchaseorder::where('pharmacy_id', $pharmacyId)
            ->where('status', 'pending')
            ->count();

        $processing = Purchaseorder::where('pharmacy_id', $pharmacyId)
            ->where('status', 'processing')
            ->count();

        $shipped = Purchaseorder::where('pharmacy_id', $pharmacyId)
            ->where('status', 'shipped')
            ->count();

        $delivered = Purchaseorder::where('pharmacy_id', $pharmacyId)
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
