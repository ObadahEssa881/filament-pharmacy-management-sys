<?php

namespace App\Filament\Resources\PurchaseorderResource\Pages;

use App\Filament\Resources\PurchaseorderResource;
use App\Filament\Resources\PurchaseorderResource\Widgets\PurchaseOrderStatsWidget;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPurchaseorder extends ViewRecord
{
    protected static string $resource = PurchaseorderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
            // Actions\Action::make('print_invoice')
            //     ->label('Print Invoice')
            //     ->url(fn($record): string => route('purchase-orders.invoice.print', $record->id))
            //     ->icon('heroicon-m-printer')
            //     ->color('primary'),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            PurchaseOrderStatsWidget::make([
                'recordId' => $this->record->id,
            ]),
        ];
    }
}
