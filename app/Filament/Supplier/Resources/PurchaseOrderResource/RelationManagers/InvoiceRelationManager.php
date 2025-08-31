<?php

namespace App\Filament\Supplier\Resources\PurchaseOrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class InvoiceRelationManager extends RelationManager
{
    protected static string $relationship = 'invoice';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('invoice_number')->disabled(),
                Forms\Components\TextInput::make('total_amount')->disabled(),
                Forms\Components\Select::make('payment_status')->disabled()
                    ->options([
                        'PAID' => 'Paid',
                        'UNPAID' => 'Unpaid',
                        'PARTIAL' => 'Partial',
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')->label('Invoice #'),
                Tables\Columns\TextColumn::make('total_amount')->money('USD'),
                Tables\Columns\TextColumn::make('payment_status')->badge(),
            ])
            ->headerActions([]) // supplier cannot create invoice
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }
}
