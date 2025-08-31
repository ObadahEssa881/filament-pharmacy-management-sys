<?php

namespace App\Filament\Resources\SupplierResource\Pages;

use App\Filament\Resources\SupplierResource;
use App\Filament\Resources\SupplierResource\Widgets\SupplierStatsWidget;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Widgets;

class ViewSupplier extends ViewRecord
{
    protected static string $resource = SupplierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Back to Suppliers')
                ->url(SupplierResource::getUrl())
                ->color('gray')
                ->icon('heroicon-m-arrow-left'),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            SupplierStatsWidget::make([
                'record' => $this->record,
            ]),
        ];
    }
}
