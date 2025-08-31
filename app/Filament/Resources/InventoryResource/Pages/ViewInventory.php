<?php

namespace App\Filament\Resources\InventoryResource\Pages;

use App\Filament\Resources\InventoryResource;
use App\Filament\Resources\InventoryResource\Widgets\InventoryStatsWidget;
use App\Models\Inventory;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewInventory extends ViewRecord
{
    protected static string $resource = InventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
            Actions\Action::make('back')
                ->label('Back to Inventory')
                ->url(InventoryResource::getUrl())
                ->color('gray')
                ->icon('heroicon-m-arrow-left'),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            InventoryStatsWidget::make([
                'record' => $this->record,
            ]),
        ];
    }


    public function getRecord(): Inventory
    {

        return parent::getRecord()
            ->load([
                'medicine',
                'medicine.category',
                'medicine.company'
            ]);
    }
}
