<?php

namespace App\Filament\Supplier\Resources\PharmacyResource\Pages;

use App\Filament\Supplier\Resources\PharmacyResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPharmacy extends ViewRecord
{
    protected static string $resource = PharmacyResource::class;

    protected function getHeaderActions(): array
    {
        return [
           
        ];
    }
}
