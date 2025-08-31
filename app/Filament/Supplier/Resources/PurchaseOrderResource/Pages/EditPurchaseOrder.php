<?php

namespace App\Filament\Supplier\Resources\PurchaseOrderResource\Pages;

use App\Filament\Supplier\Resources\PurchaseOrderResource;
use Filament\Resources\Pages\EditRecord;

class EditPurchaseOrder extends EditRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    protected function getHeaderActions(): array
    {
        // No delete; view action not needed on edit page.
        return [];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Only allow updating status and (conditionally) supplier_id
        return array_intersect_key($data, array_flip(['status', 'supplier_id']));
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Purchase order updated';
    }
}

// <?php

// namespace App\Filament\Supplier\Resources\PurchaseOrderResource\Pages;

// use App\Filament\Supplier\Resources\PurchaseOrderResource;
// use App\Models\Purchaseorder;
// use App\Services\SupplierOrderService;
// use Filament\Actions;
// use Filament\Resources\Pages\EditRecord;

// class EditPurchaseOrder extends EditRecord
// {
//     protected static string $resource = PurchaseOrderResource::class;

//     protected function getHeaderActions(): array
//     {
//         return [
//             Actions\ViewAction::make(),
//         ];
//     }

//     protected function handleRecordUpdate(array $data): Purchaseorder
//     {
//         /** @var Purchaseorder $record */
//         $record = $this->record->refresh();

//         if (isset($data['status']) && $data['status'] !== $record->status) {
//             app(SupplierOrderService::class)->transition($record, $data['status']);
//         }

//         // Prevent other fields from being changed
//         return $record->refresh();
//     }
// }
