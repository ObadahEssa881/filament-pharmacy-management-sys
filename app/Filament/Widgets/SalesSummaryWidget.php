<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Sale;
use Illuminate\Support\Number;

class SalesSummaryWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $pharmacyId = auth()->user()->pharmacy_id;

        // Today's sales
        $todaySales = Sale::where('pharmacy_id', $pharmacyId)
            ->whereDate('sale_date', today())
            ->count();

        $todayRevenue = Sale::where('pharmacy_id', $pharmacyId)
            ->whereDate('sale_date', today())
            ->sum('total_amount');

        // Monthly sales
        $monthlySales = Sale::where('pharmacy_id', $pharmacyId)
            ->whereMonth('sale_date', now()->month)
            ->count();

        $monthlyRevenue = Sale::where('pharmacy_id', $pharmacyId)
            ->whereMonth('sale_date', now()->month)
            ->sum('total_amount');

        // Average daily sales
        $daysInMonth = now()->daysInMonth;
        $daysPassed = now()->day;
        $averageDailySales = $daysPassed > 0 ? $monthlySales / $daysPassed : 0;
        $averageDailyRevenue = $daysPassed > 0 ? $monthlyRevenue / $daysPassed : 0;

        return [
            Stat::make('Today Sales', $todaySales)
                ->description(Number::currency($todayRevenue, 'USD'))
                ->icon('heroicon-m-currency-dollar')
                ->color('primary'),

            Stat::make('Monthly Sales', $monthlySales)
                ->description(Number::currency($monthlyRevenue, 'USD'))
                ->icon('heroicon-m-chart-bar')
                ->color('success'),

            Stat::make('Daily Avg', round($averageDailySales, 1))
                ->description(Number::currency($averageDailyRevenue, 'USD'))
                ->icon('heroicon-m-chart-pie')
                ->color('info'),
        ];
    }
}
