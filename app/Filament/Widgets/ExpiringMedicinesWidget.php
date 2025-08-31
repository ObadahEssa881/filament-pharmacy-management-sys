<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use App\Models\Inventory;

class ExpiringMedicinesWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        // Get medicines expiring in the next 2 months
        $expiringSoon = Inventory::where('pharmacy_id', auth()->user()->pharmacy_id)
            ->whereBetween('expiry_date', [
                now(),
                now()->addMonths(2)
            ])
            ->count();

        // Get medicines that have already expired
        $expired = Inventory::where('pharmacy_id', auth()->user()->pharmacy_id)
            ->where('expiry_date', '<', now())
            ->count();

        return [
            Stat::make('Expiring Soon', $expiringSoon)
                ->description('Medicines expiring within 2 months')
                ->icon('heroicon-m-clock')
                ->color('warning')
                ->url(route('filament.admin.resources.inventories.index', [
                    'tableFilters' => [
                        'expiry_date' => [
                            'startDate' => now()->toDateString(),
                            'endDate' => now()->addMonths(2)->toDateString(),
                        ]
                    ]
                ])),

            Stat::make('Expired Medicines', $expired)
                ->description('Medicines that have expired')
                ->icon('heroicon-m-exclamation-circle')
                ->color('danger')
                ->url(route('filament.admin.resources.inventories.index', [
                    'tableFilters' => [
                        'expiry_date' => [
                            'endDate' => now()->toDateString(),
                        ]
                    ]
                ])),
        ];
    }
}
