<?php

namespace App\Filament\Resources\MedicineResource\RelationManagers;

use App\Models\Inventory;
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
                Forms\Components\TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->minValue(0),

                Forms\Components\TextInput::make('cost_price')
                    ->required()
                    ->numeric()
                    ->prefix('$')
                    ->step(0.01),

                Forms\Components\TextInput::make('selling_price')
                    ->required()
                    ->numeric()
                    ->prefix('$')
                    ->step(0.01),

                Forms\Components\DatePicker::make('expiry_date')
                    ->minDate(now())
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitle('Inventory')
            ->modifyQueryUsing(fn(Builder $query) => $query->where('pharmacy_id', auth()->user()->pharmacy_id))
            ->columns([
                // REMOVED: location_type column as it's not needed for pharmacy owners
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(
                        fn(int $state): string =>
                        match (true) {
                            $state <= 5 => 'danger',
                            $state <= 15 => 'warning',
                            default => 'success',
                        }
                    ),

                Tables\Columns\TextColumn::make('cost_price')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('selling_price')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('profit_margin')
                    ->label('Margin')
                    ->getStateUsing(function ($record): string {
                        $costPrice = $record->cost_price;
                        if ($costPrice <= 0) return 'N/A';

                        $margin = (($record->selling_price - $costPrice) / $costPrice) * 100;
                        return round($margin, 2) . '%';
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('expiry_date')
                    ->date()
                    ->sortable()
                    ->badge()
                    ->color(
                        fn(string $state): string =>
                        now()->diffInDays($state, false) < 0 ? 'danger' : (now()->diffInDays($state) <= 60 ? 'warning' : 'success')
                    ),
            ])
            ->filters([
                Tables\Filters\Filter::make('low_stock')
                    ->label('Low Stock')
                    ->query(fn(Builder $query): Builder => $query->where('quantity', '<=', 15)),

                Tables\Filters\Filter::make('expiring_soon')
                    ->label('Expiring Soon')
                    ->query(fn(Builder $query): Builder => $query->where('expiry_date', '<=', now()->addMonths(2))),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['pharmacy_id'] = auth()->user()->pharmacy_id;
                        return $data;
                    }),
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
