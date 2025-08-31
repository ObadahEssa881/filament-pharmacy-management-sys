<?php

namespace App\Filament\Traits\Pharmacy;

trait CreateEditView
{
    use FullCrud;

    public static function canDelete($record): bool
    {
        return false;
    }
}
