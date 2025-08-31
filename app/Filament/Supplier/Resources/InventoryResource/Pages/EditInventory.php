<?php

namespace App\Filament\Supplier\Resources\InventoryResource\Pages;

use App\Filament\Supplier\Resources\InventoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInventory extends EditRecord
{
    protected static string $resource = InventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['warehouse_id'] = auth('supplier')->user()->warehouseId;
        $data['last_updated'] = now();
        return $data;
    }

    protected function canEdit(): bool
    {
        return $this->record->warehouse_id === auth('supplier')->user()->warehouseId;
    }
}
