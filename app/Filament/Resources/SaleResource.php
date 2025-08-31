<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SaleResource\Pages;
use App\Filament\Resources\SaleResource\RelationManagers;
use App\Models\Inventory;
use App\Models\Sale;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\View;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;

class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;
    protected static ?string $navigationGroup = 'Sales Management';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $modelLabel = 'Sale';
    protected static ?string $pluralModelLabel = 'Sales';

    // In SaleResource.php, update the form schema
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Sale Details')
                    ->description('Basic information about the sale')
                    ->schema([
                        Forms\Components\TextInput::make('customer_name')
                            ->maxLength(191)
                            ->placeholder('Walk-in customer')
                            ->columnSpanFull(),

                        Forms\Components\DateTimePicker::make('sale_date')
                            ->required()
                            ->default(now())
                            ->columnSpanFull(),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                // VISIBLE field - NOT required (just for display)
                                Forms\Components\TextInput::make('total_amount_display')
                                    ->label('Total Amount')
                                    ->numeric()
                                    ->prefix('$')
                                    ->step(0.01)
                                    ->disabled(),

                                // HIDDEN field - ACTUALLY required for submission
                                Forms\Components\Hidden::make('total_amount')
                                    ->required()
                                    ->default(0),

                                Forms\Components\Select::make('payment_mode')
                                    ->options([
                                        'CASH' => 'CASH',
                                        'credit_card' => 'Credit Card',
                                        'debit_card' => 'Debit Card',
                                        'mobile_payment' => 'Mobile Payment',
                                    ])
                                    ->required()
                                    ->default('CASH')
                                    ->native(false),
                            ]),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Sale Items')
                    ->description('Medicines included in this sale')
                    ->schema([
                        // Medicine selection component
                        View::make('filament.forms.components.medicine-sale-selection')
                            ->viewData([
                                'pharmacyId' => auth()->user()->pharmacy_id,
                            ]),

                        // HIDDEN FIELD TO STORE ITEMS
                        Forms\Components\Hidden::make('items_json')
                            ->default('[]')
                            ->required()
                            ->dehydrated(true) 
                            ->afterStateHydrated(fn($set, $state) => $set('items_json', $state ?? '[]')),
                    ])
                    ->columns(1),
            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer_name')
                    ->searchable()
                    ->toggleable()
                    ->default('Walk-in customer'),

                Tables\Columns\TextColumn::make('sale_date')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('total_amount')
                    ->money('USD')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money('USD')
                            ->label('Total Sales'),
                    ]),

                Tables\Columns\TextColumn::make('payment_mode')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'CASH' => 'success',
                        'credit_card' => 'primary',
                        'debit_card' => 'info',
                        'mobile_payment' => 'warning',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'CASH' => 'CASH',
                        'credit_card' => 'Credit Card',
                        'debit_card' => 'Debit Card',
                        'mobile_payment' => 'Mobile Payment',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('saleItems_count')
                    ->label('Items')
                    ->counts('saleItems')
                    ->badge()
                    ->color('primary')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('payment_mode')
                    ->options([
                        'CASH' => 'CASH',
                        'credit_card' => 'Credit Card',
                        'debit_card' => 'Debit Card',
                        'mobile_payment' => 'Mobile Payment',
                    ]),
                Tables\Filters\Filter::make('sale_date')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('to'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('sale_date', '>=', $date),
                            )
                            ->when(
                                $data['to'],
                                fn(Builder $query, $date): Builder => $query->whereDate('sale_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->button(),
                Tables\Actions\EditAction::make()
                    ->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->striped();
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\SaleItemsRelationManager::class,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('pharmacy_id', auth()->user()->pharmacy_id);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSales::route('/'),
            'create' => Pages\CreateSale::route('/create'),
            'view' => Pages\ViewSale::route('/{record}'),
            'edit' => Pages\EditSale::route('/{record}/edit'),
        ];
    }
}
