<?php

namespace App\Filament\Resources\WarehouseResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InventoryRelationManager extends RelationManager
{
    protected static string $relationship = 'inventories';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('medicine.name')
                    ->label('Medicine')
                    ->disabled(),
                Forms\Components\TextInput::make('quantity')
                    ->numeric()
                    ->disabled(),
                Forms\Components\TextInput::make('cost_price')
                    ->numeric()
                    ->disabled(),
                Forms\Components\TextInput::make('selling_price')
                    ->numeric()
                    ->disabled(),
                Forms\Components\DatePicker::make('expiry_date')
                    ->disabled(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('medicine.name')
            ->columns([
                Tables\Columns\TextColumn::make('medicine.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn(int $state): string => match (true) {
                        $state <= 10 => 'danger',
                        $state <= 20 => 'warning',
                        default => 'success',
                    }),
                Tables\Columns\TextColumn::make('cost_price')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('selling_price')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('expiry_date')
                    ->date()
                    ->sortable()
                    ->badge()
                    ->color(fn(string $state): string => match (true) {
                        now()->diffInDays($state, false) < 30 => 'danger',
                        now()->diffInDays($state, false) < 60 => 'warning',
                        default => 'success',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('medicine.category_id')
                    ->relationship('medicine.category', 'name')
                    ->label('Category'),
            ])
            ->headerActions([
                //
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                //
            ]);
    }
}
