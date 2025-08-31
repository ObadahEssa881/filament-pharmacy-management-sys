<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ExpiringMedicinesWidget;
use App\Filament\Widgets\LowStockMedicinesWidget;
use App\Filament\Widgets\PurchaseOrderStatusWidget;
use App\Filament\Widgets\SalesSummaryWidget;
use App\Filament\Widgets\ProfitGraphWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return [
            ExpiringMedicinesWidget::class,
            LowStockMedicinesWidget::class,
            PurchaseOrderStatusWidget::class,
            SalesSummaryWidget::class,
            ProfitGraphWidget::class,
        ];
    }
}
