<?php

namespace App\Filament\SupplierResources\MedicineResource\RelationManagers;

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
                Forms\Components\Select::make('warehouse_id')
                    ->label('Warehouse')
                    ->relationship('warehouse', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\TextInput::make('quantity')
                    ->numeric()
                    ->required()
                    ->minValue(1),

                Forms\Components\TextInput::make('cost_price')
                    ->numeric()
                    ->prefix('$')
                    ->step(0.01)
                    ->required(),
                Forms\Components\TextInput::make('selling_price')
                    ->numeric()
                    ->prefix('$')
                    ->step(0.01)
                    ->required(),

                Forms\Components\DatePicker::make('expiry_date')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('warehouse.name')
            ->columns([
                Tables\Columns\TextColumn::make('warehouse.name')
                    ->label('Warehouse')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('quantity')
                    ->sortable()
                    ->badge()
                    ->color(fn(string $state): string => match (true) {
                        $state <= 5 => 'danger',
                        $state <= 15 => 'warning',
                        default => 'success',
                    }),

                Tables\Columns\TextColumn::make('cost_price')
                    ->money('USD')
                    ->label('Cost Price')
                    ->sortable(),

                Tables\Columns\TextColumn::make('selling_price')
                    ->money('USD')
                    ->label('Selling Price')
                    ->sortable(),

                Tables\Columns\TextColumn::make('expiry_date')
                    ->date()
                    ->sortable()
                    ->badge()
                    ->color(fn(string $state): string => now()->greaterThan($state) ? 'danger' : 'success'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('warehouse')
                    ->relationship('warehouse', 'name'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('restock')
                    ->label('Restock')
                    ->icon('heroicon-m-arrow-path')
                    ->color('warning')
                    ->modalHeading('Restock Medicine')
                    ->form([
                        Forms\Components\TextInput::make('quantity')
                            ->numeric()
                            ->required()
                            ->minValue(1),

                        Forms\Components\DatePicker::make('expiry_date')
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
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
