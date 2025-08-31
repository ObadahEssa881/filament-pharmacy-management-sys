<?php

namespace App\Filament\Resources\PurchaseorderResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class PurchaseOrderStatsWidget extends BaseWidget
{
    protected static ?int $sort = 0;

    protected int | string | array $columnSpan = 'full';

    public ?int $recordId = null;

    protected function getStats(): array
    {
        // Check if recordId is set
        if (!$this->recordId) {
            return [
                Stat::make('Error', 'Purchase Order not found')
                    ->description('Please refresh the page')
                    ->color('danger')
                    ->icon('heroicon-m-exclamation-circle'),
            ];
        }

        // Find the purchase order
        $purchaseOrder = \App\Models\Purchaseorder::find($this->recordId);

        // Check if purchase order exists
        if (!$purchaseOrder) {
            return [
                Stat::make('Error', 'Purchase Order not found')
                    ->description('The purchase order may have been deleted')
                    ->color('danger')
                    ->icon('heroicon-m-exclamation-circle'),
            ];
        }

        // Check if supplier exists
        $supplierName = $purchaseOrder->supplier ? $purchaseOrder->supplier->name : 'Unknown Supplier';

        // Calculate total items and cost
        $totalItems = $purchaseOrder->purchaseorderitems->sum('quantity');
        $totalCost = $purchaseOrder->purchaseorderitems->sum(fn($item) => $item->quantity * $item->unit_price);
        $deliveryDays = $purchaseOrder->delivery_date ? $purchaseOrder->order_date->diffInDays($purchaseOrder->delivery_date) : null;

        return [
            Stat::make('Supplier', $supplierName)
                ->description('Order supplier')
                ->icon('heroicon-m-building-storefront'),

            Stat::make('Items', $totalItems)
                ->description('Total items ordered')
                ->icon('heroicon-m-shopping-bag'),

            Stat::make('Total Cost', Number::currency($totalCost, 'USD'))
                ->description('Total order cost')
                ->icon('heroicon-m-currency-dollar'),

            Stat::make('Delivery Time', $deliveryDays ? "{$deliveryDays} days" : 'N/A')
                ->description('Estimated delivery time')
                ->icon('heroicon-m-truck'),
        ];
    }
}
