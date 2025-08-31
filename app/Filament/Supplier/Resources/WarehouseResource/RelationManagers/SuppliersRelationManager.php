<?php

namespace App\Filament\SupplierResources\WarehouseResource\RelationManagers;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\RelationManagers\RelationManager;
use Illuminate\Database\Eloquent\Builder;

class SuppliersRelationManager extends RelationManager
{
    protected static string $relationship = 'suppliers';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('phone')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('contact_person')
                    ->toggleable(),

                Tables\Columns\IconColumn::make('has_orders')
                    ->label('Orders')
                    ->boolean()
                    ->state(function ($record) {
                        return $record->purchaseorders->isNotEmpty();
                    })
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),
            ])
            ->headerActions([])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }
    public static function getRelations(): array
    {
        return [
            PurchaseOrdersRelationManager::class,
        ];
    }
}
