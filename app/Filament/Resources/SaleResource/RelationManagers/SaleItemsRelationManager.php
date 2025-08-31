<?php

namespace App\Filament\Resources\SaleResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SaleItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'saleItems';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('medicine_id')
                    ->relationship('medicine', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->columnSpan(2),

                Forms\Components\TextInput::make('quantity')
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->columnSpan(1),

                Forms\Components\TextInput::make('unit_price')
                    ->numeric()
                    ->required()
                    ->prefix('$')
                    ->step(0.01)
                    ->columnSpan(1),

                Forms\Components\TextInput::make('cost_price')
                    ->numeric()
                    ->prefix('$')
                    ->step(0.01)
                    ->disabled()
                    ->columnSpanFull(),
            ])
            ->columns(4);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('medicine.name')
            ->columns([
                Tables\Columns\TextColumn::make('medicine.name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('medicine.category.name')
                    ->label('Category')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('unit_price')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('cost_price')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('total_price')
                    ->money('USD')
                    ->getStateUsing(fn($record): float => $record->quantity * $record->unit_price)
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('medicine.category_id')
                    ->relationship('medicine.category', 'name')
                    ->label('Category'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->striped();
    }
}
