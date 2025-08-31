<?php

namespace App\Filament\Traits\Pharmacy;

use Illuminate\Support\Facades\Auth;

trait ViewOnly
{
    use FullCrud;

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
    protected static function getPharmacyId(): int
    {
        $user = Auth::user();
        if (!$user) {
            return 0; // Will return empty results
        }

        return $user->pharmacy_id ?? 0;
    }
}
