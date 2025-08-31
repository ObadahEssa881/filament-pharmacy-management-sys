<?php

namespace App\Filament\Traits\Supplier;

trait CreateViewS
{
    use FullCrudS;

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }
}
