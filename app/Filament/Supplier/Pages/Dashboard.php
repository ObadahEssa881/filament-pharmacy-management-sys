<?php

namespace App\Filament\Supplier\Pages;

use App\Filament\Supplier\Widgets\ExpiringMedicinesWidget;
use App\Filament\SupplierWidgets\RecentOrders;
use App\Filament\Supplier\Widgets\LowStockMedicinesWidget;
use App\Filament\Supplier\Widgets\PurchaseOrderStatusWidget;
use Filament\Pages\Dashboard as BasePage;

class Dashboard extends BasePage
{
    protected static ?string $title = 'Supplier Dashboard';

    public function getWidgets(): array
    {
        return [
            ExpiringMedicinesWidget::class,
            PurchaseOrderStatusWidget::class,
            LowStockMedicinesWidget::class,
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [
            url()->current() => 'Dashboard',
        ];
    }
}
