<?php

namespace App\Filament\Resources\PurchaseOrderResource\Pages;

use App\Filament\Resources\PurchaseOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Services\PurchaseService;
use Filament\Notifications\Notification;
use App\Models\PurchaseOrder;

class EditPurchaseOrder extends EditRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('processOrder')
                ->label('Process Order')
                ->color('info')
                ->visible(fn(PurchaseOrder $record) => $record->status === 'PENDING')
                ->action(function (PurchaseOrder $record) {
                    $this->updateStatus($record, 'PROCESSING');
                }),

            Actions\Action::make('shipOrder')
                ->label('Mark as Shipped')
                ->color('primary')
                ->visible(fn(PurchaseOrder $record) => $record->status === 'PROCESSING')
                ->action(function (PurchaseOrder $record) {
                    $this->updateStatus($record, 'SHIPPED');
                }),

            Actions\Action::make('deliverOrder')
                ->label('Mark as Delivered')
                ->color('success')
                ->visible(fn(PurchaseOrder $record) => $record->status === 'SHIPPED')
                ->action(function (PurchaseOrder $record) {
                    $this->updateStatus($record, 'DELIVERED');
                }),

            Actions\Action::make('cancelOrder')
                ->label('Cancel Order')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn(PurchaseOrder $record) => !in_array($record->status, ['CANCELLED', 'DELIVERED']))
                ->action(function (PurchaseOrder $record) {
                    $this->updateStatus($record, 'CANCELLED');
                }),
        ];
    }

    protected function updateStatus(PurchaseOrder $record, string $status): void
    {
        try {
            $purchaseService = app(PurchaseService::class);
            $purchaseService->updateStatus($record->id, $status);

            Notification::make()
                ->title('Order status updated')
                ->body("Order status changed to {$status}")
                ->success()
                ->send();

            $this->redirect($this->getResource()::getUrl('edit', ['record' => $record->id]));
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error updating order status')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
