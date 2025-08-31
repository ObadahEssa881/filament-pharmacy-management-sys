<?php

namespace App\Filament\Resources\WarehouseResource\Pages;

use App\Filament\Resources\WarehouseResource;
use App\Filament\Resources\WarehouseResource\Widgets\WarehouseStatsWidget;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Widgets;

class ViewWarehouse extends ViewRecord
{
    protected static string $resource = WarehouseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Back to Warehouses')
                ->url(WarehouseResource::getUrl())
                ->color('gray')
                ->icon('heroicon-m-arrow-left'),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            WarehouseStatsWidget::make([
                'record' => $this->record,
            ]),
        ];
    }
}
