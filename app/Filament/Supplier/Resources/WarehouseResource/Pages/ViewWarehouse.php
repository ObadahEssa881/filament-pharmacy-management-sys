<?php

namespace App\Filament\Supplier\Resources\WarehouseResource\Pages;

use App\Filament\Supplier\Resources\WarehouseResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewWarehouse extends ViewRecord
{
    protected static string $resource = WarehouseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
