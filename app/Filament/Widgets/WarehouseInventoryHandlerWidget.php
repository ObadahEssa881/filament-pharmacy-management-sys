<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class WarehouseInventoryHandlerWidget extends Widget
{
    protected static bool $isLazy = false;
    
    protected static string $view = 'filament.resources.purchase-order-resource.widgets.warehouse-inventory-handler';
}