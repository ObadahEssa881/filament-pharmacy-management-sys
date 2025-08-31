<?php

namespace App\Filament\Resources\InventoryResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class InventoryStatsWidget extends BaseWidget
{
    protected static ?int $sort = 0;

    protected int | string | array $columnSpan = 'full';

    public ?int $recordId = null;

    protected function getStats(): array
    {
        // Check if recordId is set
        if (!$this->recordId) {
            return [
                Stat::make('Error', 'Inventory not found')
                    ->description('Please refresh the page')
                    ->color('danger')
                    ->icon('heroicon-m-exclamation-circle'),
            ];
        }

        // Find the inventory
        $inventory = \App\Models\Inventory::find($this->recordId);

        // Check if inventory exists
        if (!$inventory) {
            return [
                Stat::make('Error', 'Inventory not found')
                    ->description('The inventory item may have been deleted')
                    ->color('danger')
                    ->icon('heroicon-m-exclamation-circle'),
            ];
        }

        // Calculate profit margin
        $costPrice = $inventory->cost_price;
        $sellingPrice = $inventory->selling_price;
        $profitMargin = $costPrice > 0 ? (($sellingPrice - $costPrice) / $costPrice) * 100 : 0;

        // Calculate days until expiry
        $daysUntilExpiry = $inventory->expiry_date ? now()->diffInDays($inventory->expiry_date, false) : null;
        $expiryStatus = $daysUntilExpiry === null ? 'N/A' : ($daysUntilExpiry < 0 ? 'Expired' : ($daysUntilExpiry <= 60 ? "{$daysUntilExpiry} days" : 'Good'));

        return [
            Stat::make('Current Stock', $inventory->quantity)
                ->description('Current inventory level')
                ->icon('heroicon-m-cube')
                ->color($inventory->quantity <= 5 ? 'danger' : ($inventory->quantity <= 15 ? 'warning' : 'success')),

            Stat::make('Profit Margin', Number::format($profitMargin, 2) . '%')
                ->description('Gross profit margin')
                ->icon('heroicon-m-chart-bar')
                ->color($profitMargin > 30 ? 'success' : ($profitMargin > 15 ? 'warning' : 'danger')),

            Stat::make('Expiry Status', $expiryStatus)
                ->description($daysUntilExpiry < 0 ? 'This item has expired' : 'Days until expiry')
                ->icon('heroicon-m-clock')
                ->color($daysUntilExpiry === null ? 'gray' : ($daysUntilExpiry < 0 ? 'danger' : ($daysUntilExpiry <= 60 ? 'warning' : 'success'))),
        ];
    }
}
