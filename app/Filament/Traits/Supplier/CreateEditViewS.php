<?php

namespace App\Filament\Traits\Supplier;

trait CreateEditViewS
{
    use FullCrudS;
    
    public static function canDelete($record): bool
    {
        return false;
    }
}