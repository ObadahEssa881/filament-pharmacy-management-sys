<?php

namespace App\Filament\Traits\Pharmacy;

use Illuminate\Support\Facades\Auth;

trait FullCrud
{
    public static function canViewAny(): bool
    {
        return static::isPharmacyPanel();
    }

    public static function canCreate(): bool
    {
        return static::isPharmacyPanel();
    }

    public static function canEdit($record): bool
    {
        return static::isPharmacyPanel() &&
            $record->pharmacy_id === static::getPharmacyId();
    }

    public static function canDelete($record): bool
    {
        return static::isPharmacyPanel() &&
            $record->pharmacy_id === static::getPharmacyId();
    }

    protected static function isPharmacyPanel(): bool
    {
        return Auth::check() &&
            Auth::user()->role === 'PHARMACY_OWNER' &&
            request()->is('dashboard/*');
    }

    protected static function getPharmacyId(): ?int
    {
        return Auth::check() ? Auth::user()->pharmacy_id : null;
    }

    public static function getEloquentQueryForPharmacy(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->where('pharmacy_id', static::getPharmacyId());
    }
}
