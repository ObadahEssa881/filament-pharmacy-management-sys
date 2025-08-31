<?php

namespace App\Filament\SupplierResources\WarehouseResource\RelationManagers;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\RelationManagers\RelationManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;

class InventoryRelationManager extends RelationManager
{
    protected static string $relationship = 'inventories';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('medicine.name')
            ->columns([
                Tables\Columns\ImageColumn::make('medicine.image')
                    ->circular()
                    ->toggleable()
                    ->size(40),

                Tables\Columns\TextColumn::make('medicine.name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('medicine.titer')
                    ->label('Strength')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('quantity')
                    ->sortable()
                    ->badge()
                    ->color(fn(string $state): string => match (true) {
                        $state <= 5 => 'danger',
                        $state <= 15 => 'warning',
                        default => 'success',
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('selling_price')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('cost_price')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('expiry_date')
                    ->date()
                    ->sortable()
                    ->badge()
                    ->color(fn(string $state): string => now()->greaterThan($state) ? 'danger' : 'success')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('medicine')
                    ->relationship('medicine', 'name'),

                Tables\Filters\Filter::make('expiry_date')
                    ->form([
                        DatePicker::make('expiry_date_from'),
                        DatePicker::make('expiry_date_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['expiry_date_from'],
                                fn(Builder $query, $date) => $query->whereDate('expiry_date', '>=', $date),
                            )
                            ->when(
                                $data['expiry_date_until'],
                                fn(Builder $query, $date) => $query->whereDate('expiry_date', '<=', $date),
                            );
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('restock')
                    ->label('Restock')
                    ->icon('heroicon-m-arrow-path')
                    ->color('warning')
                    ->modalHeading('Restock Medicine')
                    ->form([
                        TextInput::make('quantity')
                            ->numeric()
                            ->required()
                            ->minValue(1),

                        DatePicker::make('expiry_date')
                            ->required()
                            ->default(now()->addYear()),
                    ])
                    ->action(function (array $data, \App\Models\Inventory $record) {
                        $record->update([
                            'quantity' => $record->quantity + $data['quantity'],
                            'expiry_date' => $data['expiry_date'],
                            'last_updated' => now(),
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('Inventory restocked successfully')
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
