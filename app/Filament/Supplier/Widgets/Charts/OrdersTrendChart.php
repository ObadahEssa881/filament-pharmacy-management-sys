<?php

namespace App\Filament\Supplier\Widgets\Charts;

use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use App\Models\PurchaseOrder;

use Filament\Forms;

class OrdersTrendChart extends ApexChartWidget
{
    protected int | string | array $columnSpan = 'full';
    protected static ?string $chartId = 'ordersTrendChart';
    protected static ?string $heading = 'Orders Trend';

    // ✅ define filters
    protected function getFormSchema(): array
    {
        return [
            Forms\Components\DatePicker::make('start')->default(now()->startOfMonth()),
            Forms\Components\DatePicker::make('end')->default(now()->endOfMonth()),
        ];
    }

    public function getOptions(): array
    {
        $state = $this->form->getState();
        $start = isset($state['start'])
            ? \Carbon\Carbon::parse($state['start'])->startOfDay()
            : now()->subDays(30)->startOfDay();
        $end = isset($state['end'])
            ? \Carbon\Carbon::parse($state['end'])->endOfDay()
            : now()->endOfDay();

        $orders = PurchaseOrder::selectRaw('DATE(order_date) as date, COUNT(*) as total')
            ->whereBetween('order_date', [$start, $end])
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date')
            ->toArray();

        return [
            'chart' => [
                'type' => 'line',
                'height' => 300,
                'toolbar' => ['show' => true],
            ],
            'stroke' => [
                'curve' => 'smooth',
                'width' => 3,
            ],
            'markers' => [
                'size' => 5,
            ],
            'series' => [
                [
                    'name' => 'Orders',
                    'data' => array_values($orders), // [1, 3]
                ],
            ],
            'xaxis' => [
                'categories' => array_keys($orders), // ["2025-08-20", "2025-08-21"]
                'labels' => [
                    'rotate' => -45,
                ],
            ],
            'yaxis' => [
                'title' => ['text' => 'Orders Count'],
            ],
        ];
    }
}
