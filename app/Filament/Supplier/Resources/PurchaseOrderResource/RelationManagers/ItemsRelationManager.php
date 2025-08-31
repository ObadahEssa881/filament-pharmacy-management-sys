<?php

namespace App\Filament\Supplier\Resources\PurchaseOrderResource\RelationManagers;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\RelationManagers\RelationManager;

class ItemsRelationManager extends RelationManager
{
    // Relationship name as defined on the Purchaseorder model
    protected static string $relationship = 'purchaseorderitems';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('medicine.name')->label('Medicine'),
                Tables\Columns\TextColumn::make('quantity')->numeric(),
                Tables\Columns\TextColumn::make('unit_price')->money('USD'),
                Tables\Columns\TextColumn::make('total')->label('Total')->getStateUsing(
                    fn($record) => number_format($record->quantity * $record->unit_price, 2)
                )->money('USD'),
            ])
            ->headerActions([]) // no create
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }
}
