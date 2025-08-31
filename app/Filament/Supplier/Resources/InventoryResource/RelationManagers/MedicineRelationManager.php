<?php

namespace App\Filament\SupplierResources\InventoryResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;

class MedicineRelationManager extends RelationManager
{
    protected static string $relationship = 'medicine';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationBadge(): ?string
    {
        return static::getRecord()->medicine()->count();
    }
}
