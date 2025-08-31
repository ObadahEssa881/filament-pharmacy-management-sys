<?php

namespace App\Filament\Supplier\Resources\PurchaseOrderResource\Pages;

use App\Filament\Supplier\Resources\PurchaseOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePurchaseOrder extends CreateRecord
{
    protected static string $resource = PurchaseOrderResource::class;
}
