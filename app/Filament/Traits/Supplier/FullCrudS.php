<?php

namespace App\Filament\Traits\Supplier;

use Illuminate\Support\Facades\Auth;
use Filament\Facades\Filament;

trait FullCrudS
{
    public static function canViewAny(): bool
    {
        return static::isSupplierPanel();
    }

    public static function canCreate(): bool
    {
        return static::isSupplierPanel();
    }

    public static function canEdit($record): bool
    {
        return static::isSupplierPanel() &&
            $record->warehouse_id === static::getWarehouseId(); // Use snake_case for inventory
    }

    public static function canDelete($record): bool
    {
        return static::isSupplierPanel() &&
            $record->warehouse_id === static::getWarehouseId(); // Use snake_case for inventory
    }

    protected static function isSupplierPanel(): bool
    {

        return Filament::getCurrentPanel()?->getId() === 'supplier'
            && auth('supplier')->check()
            && auth('supplier')->user()->role === 'SUPPLIER_ADMIN';
    }

    protected static function getWarehouseId(): ?int
    {

        return auth('supplier')->check() ? auth('supplier')->user()->warehouseId : null;
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {

        $query = parent::getEloquentQuery();

        if (static::isSupplierPanel()) {
            $query->where('warehouse_id', static::getWarehouseId());
        }

        return $query;
    }
}
