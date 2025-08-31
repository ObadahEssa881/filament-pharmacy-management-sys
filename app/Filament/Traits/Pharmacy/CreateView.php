<?php

namespace App\Filament\Traits\Pharmacy;

trait CreateView
{
    use FullCrud;

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }
}
