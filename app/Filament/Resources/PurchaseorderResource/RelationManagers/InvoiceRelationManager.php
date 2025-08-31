<?php

namespace App\Filament\Resources\PurchaseOrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InvoiceRelationManager extends RelationManager
{
    protected static string $relationship = 'invoice';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('total_amount')
                    ->numeric()
                    ->prefix('$')
                    ->step(0.01)
                    ->required(),
                Forms\Components\Select::make('payment_status')
                    ->options([
                        'UNPAID' => 'Unpaid',
                        'PAID' => 'Paid',
                        'PARTIAL' => 'Partial',
                    ])
                    ->required(),
                Forms\Components\DatePicker::make('payment_date')
                    ->label('Payment Date')
                    ->visible(fn($record) => $record->payment_status !== 'UNPAID'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitle('Invoice')
            ->columns([
                Tables\Columns\TextColumn::make('total_amount')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'PAID' => 'success',
                        'UNPAID' => 'warning',
                        'PARTIAL' => 'info',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
            ]);
    }
}
