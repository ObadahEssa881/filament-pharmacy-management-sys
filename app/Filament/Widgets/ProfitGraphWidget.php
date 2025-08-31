<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Sale;
use App\Models\Saleitem;
use Carbon\Carbon;
use Illuminate\Support\Number;

class ProfitGraphWidget extends ChartWidget
{
    protected static ?string $heading = 'Sales & Profit Trend';
    protected static ?int $sort = 5;
    protected static ?int $contentHeight = 300;

    protected function getData(): array
    {
        $pharmacyId = auth()->user()->pharmacy_id;

        // Get sales data for the last 7 days
        $salesData = [];
        $profitData = [];
        $labels = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dateStr = $date->format('Y-m-d');
            $dateLabel = $date->format('M d');

            // Get sales for this date
            $sales = Sale::where('pharmacy_id', $pharmacyId)
                ->whereDate('sale_date', $dateStr)
                ->get();

            $totalRevenue = $sales->sum('total_amount');

            // Calculate profit (revenue - cost)
            $totalCost = 0;
            foreach ($sales as $sale) {
                $totalCost += $sale->saleItems->sum(function ($item) {
                    return $item->quantity * $item->cost_price;
                });
            }

            $profit = $totalRevenue - $totalCost;

            $labels[] = $dateLabel;
            $salesData[] = $totalRevenue;
            $profitData[] = $profit;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data' => $salesData,
                    'backgroundColor' => '#3b82f6',
                    'borderColor' => '#3b82f6',
                ],
                [
                    'label' => 'Profit',
                    'data' => $profitData,
                    'backgroundColor' => '#10b981',
                    'borderColor' => '#10b981',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
