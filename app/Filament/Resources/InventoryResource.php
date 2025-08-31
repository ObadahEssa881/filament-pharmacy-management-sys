<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventoryResource\Pages\CreateInventory;
use App\Filament\Resources\InventoryResource\Pages\EditInventory;
use App\Filament\Resources\InventoryResource\Pages\ListInventories;
use App\Filament\Resources\InventoryResource\Pages\ViewInventory;
use App\Filament\Resources\InventoryResource\RelationManagers\PurchaseOrderItemsRelationManager;
use App\Filament\Resources\InventoryResource\RelationManagers\SaleItemsRelationManager;
use App\Filament\Traits\Pharmacy\FullCrud;
use App\Models\Inventory;
use App\Models\Medicine;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InventoryResource extends Resource
{
    use FullCrud;

    protected static ?string $model = Inventory::class;
    protected static ?string $navigationGroup = 'Inventory Management';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $modelLabel = 'Inventory Item';
    protected static ?string $pluralModelLabel = 'Inventory';
    protected static bool $shouldRegisterNavigation = true;

    // ADD THIS - Critical for create permission
    public static function canCreate(): bool
    {
        return auth()->check() && auth()->user()->pharmacy_id !== null;
    }

    // ADD THIS - Critical for edit permission
    public static function canEdit(Model $record): bool
    {
        return auth()->user()->pharmacy_id === $record->pharmacy_id;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Medicine Information')
                    ->description('Basic information about the medicine')
                    ->schema([
                        Forms\Components\Select::make('medicine_id')
                            ->label('Medicine')
                            ->relationship('medicine', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live(onBlur: true),

                        // CORRECTED: Use viewData() instead of extraViewData()
                        Forms\Components\View::make('filament.forms.components.medicine-info')
                            ->viewData([
                                'record' => $form->getRecord(),
                            ]),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Inventory Details')
                    ->description('Current inventory status and pricing')
                    ->schema([
                        Forms\Components\TextInput::make('quantity')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->suffixAction(
                                Forms\Components\Actions\Action::make('adjust')
                                    ->icon('heroicon-m-adjustments-vertical')
                                    ->color('primary')
                                    ->action(fn(callable $set, $get) => $set('quantity', $get('quantity') + 1))
                            )
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('cost_price')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                $sellingPrice = $get('selling_price');
                                if ($state && $sellingPrice) {
                                    $set('profit_margin', (($sellingPrice - $state) / $state) * 100);
                                }
                            }),

                        Forms\Components\TextInput::make('selling_price')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                $costPrice = $get('cost_price');
                                if ($state && $costPrice) {
                                    $set('profit_margin', (($state - $costPrice) / $costPrice) * 100);
                                }
                            }),

                        // CORRECTED: Use viewData() instead of extraViewData()
                        Forms\Components\View::make('filament.forms.components.profit-margin')
                            ->viewData([
                                'record' => $form->getRecord(),
                            ]),

                        Forms\Components\DatePicker::make('expiry_date')
                            ->minDate(now())
                            ->required(),

                        Forms\Components\DatePicker::make('last_updated')
                            ->minDate(now())
                            ->default(now())
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('medicine.name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn(Inventory $record): string => $record->medicine->titer)
                    ->icon('heroicon-m-cube'),

                Tables\Columns\TextColumn::make('medicine.category.name')
                    ->label('Category')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

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
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('selling_price')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('profit_margin')
                    ->label('Margin')
                    ->getStateUsing(function (Inventory $record): string {
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
                    ->color(function (string $state): string {
                        if (now()->diffInDays($state, false) < 0) {
                            return 'danger';
                        } elseif (now()->diffInDays($state) <= 60) {
                            return 'warning';
                        } else {
                            return 'success';
                        }
                    }),

                Tables\Columns\TextColumn::make('last_updated')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('medicine.category_id')
                    ->relationship('medicine.category', 'name')
                    ->label('Category'),

                Tables\Filters\Filter::make('low_stock')
                    ->label('Low Stock')
                    ->query(fn(Builder $query): Builder => $query->where('quantity', '<=', 15)),

                Tables\Filters\Filter::make('expiring_soon')
                    ->label('Expiring Soon')
                    ->query(fn(Builder $query): Builder => $query->where('expiry_date', '<=', now()->addMonths(2))),

                Tables\Filters\Filter::make('expired')
                    ->label('Expired')
                    ->query(fn(Builder $query): Builder => $query->where('expiry_date', '<', now())),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->button(),
                Tables\Actions\EditAction::make()
                    ->button(),
                Tables\Actions\DeleteAction::make(),
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
            PurchaseOrderItemsRelationManager::class,
            SaleItemsRelationManager::class,
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
            'index' => ListInventories::route('/'),
            'create' => CreateInventory::route('/create'),
            'view' => ViewInventory::route('/{record}'),
            'edit' => EditInventory::route('/{record}/edit'),
        ];
    }
}
