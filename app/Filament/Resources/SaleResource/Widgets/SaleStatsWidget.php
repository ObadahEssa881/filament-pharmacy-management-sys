<?php

namespace App\Filament\Resources\SaleResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class SaleStatsWidget extends BaseWidget
{
    protected static ?int $sort = 0;

    protected int | string | array $columnSpan = 'full';

    public ?int $recordId = null;

    protected function getStats(): array
    {
        // Check if recordId is set
        if (!$this->recordId) {
            return [
                Stat::make('Error', 'Sale not found')
                    ->description('Please refresh the page')
                    ->color('danger')
                    ->icon('heroicon-m-exclamation-circle'),
            ];
        }

        // Find the sale
        $sale = \App\Models\Sale::find($this->recordId);

        // Check if sale exists
        if (!$sale) {
            return [
                Stat::make('Error', 'Sale not found')
                    ->description('The sale may have been deleted')
                    ->color('danger')
                    ->icon('heroicon-m-exclamation-circle'),
            ];
        }

        // Calculate total items and profit
        $totalItems = $sale->saleItems->sum('quantity');
        $totalCost = $sale->saleItems->sum(fn($item) => $item->quantity * $item->cost_price);
        $totalRevenue = $sale->total_amount;
        $profit = $totalRevenue - $totalCost;

        return [
            Stat::make('Customer', $sale->customer_name ?? 'Walk-in customer')
                ->description('Customer name')
                ->icon('heroicon-m-user'),

            Stat::make('Items', $totalItems)
                ->description('Total items sold')
                ->icon('heroicon-m-shopping-bag'),

            Stat::make('Revenue', Number::currency($totalRevenue, 'USD'))
                ->description('Total revenue from sale')
                ->icon('heroicon-m-currency-dollar'),

            Stat::make('Profit', Number::currency($profit, 'USD'))
                ->description('Estimated profit')
                ->icon('heroicon-m-chart-bar')
                ->color($profit > 0 ? 'success' : 'danger'),
        ];
    }
}
