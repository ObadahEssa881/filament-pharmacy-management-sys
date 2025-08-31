<?php

namespace App\Filament\Traits\Supplier;

trait ViewOnlyS
{
    use FullCrudS;
    
    public static function canCreate(): bool
    {
        return false;
    }
    
    public static function canEdit($record): bool
    {
        return false;
    }
    
    public static function canDelete($record): bool
    {
        return false;
    }
}