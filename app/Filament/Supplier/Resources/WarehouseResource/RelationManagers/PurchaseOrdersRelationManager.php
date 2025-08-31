<?php

namespace App\Filament\SupplierResources\WarehouseResource\RelationManagers;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\RelationManagers\RelationManager;
use Illuminate\Database\Eloquent\Builder;

class PurchaseOrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'purchaseorders';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitle(fn($record) => $record->order_date ? $record->order_date->format('Y-m-d') : 'Order #' . $record->id)
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Order #')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('order_date')
                    ->label('Order Date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('delivery_date')
                    ->label('Delivery Date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(?string $state): string => match ($state) {
                        'PENDING' => 'warning',
                        'PROCESSING' => 'info',
                        'SHIPPED' => 'primary',
                        'DELIVERED' => 'success',
                        'CANCELLED' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('USD')
                    ->sortable()
                    ->getStateUsing(function ($record) {
                        return $record->invoice ? $record->invoice->total_amount : null;
                    }),

                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Payment Status')
                    ->badge()
                    ->color(fn(?string $state): string => match ($state) {
                        'PAID' => 'success',
                        'UNPAID' => 'warning',
                        'PARTIAL' => 'info',
                        default => 'gray',
                    })
                    ->sortable()
                    ->getStateUsing(function ($record) {
                        return $record->invoice ? $record->invoice->payment_status : 'N/A';
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'PENDING' => 'Pending',
                        'PROCESSING' => 'Processing',
                        'SHIPPED' => 'Shipped',
                        'DELIVERED' => 'Delivered',
                        'CANCELLED' => 'Cancelled',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();

        if ($query === null) {
            $query = $this->getRelationship()->getQuery();
        }

        return $query->with('invoice');
    }
}
