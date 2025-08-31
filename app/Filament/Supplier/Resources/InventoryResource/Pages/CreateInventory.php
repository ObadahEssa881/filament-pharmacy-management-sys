<?php

namespace App\Filament\Supplier\Resources\InventoryResource\Pages;

use App\Filament\Supplier\Resources\InventoryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateInventory extends CreateRecord
{
    protected static string $resource = InventoryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
       
        $data['warehouse_id'] = auth('supplier')->user()->warehouseId;
        $data['last_updated'] = now();
        return $data;
    }
}
