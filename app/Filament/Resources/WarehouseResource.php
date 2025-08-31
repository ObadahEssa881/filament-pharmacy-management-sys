<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WarehouseResource\Pages\ListWarehouses;
use App\Filament\Resources\WarehouseResource\Pages\ViewWarehouse;
use App\Filament\Resources\WarehouseResource\RelationManagers\InventoryRelationManager;
use App\Filament\Resources\WarehouseResource\RelationManagers\SuppliersRelationManager;
use App\Filament\Traits\Pharmacy\ViewOnly;
use App\Models\Warehouse;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WarehouseResource extends Resource
{
    use ViewOnly;

    protected static ?string $model = Warehouse::class;
    protected static ?string $navigationGroup = 'Reference Data';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static bool $shouldRegisterNavigation = true;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Warehouse Details')
                    ->description('Basic information about the warehouse')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->disabled()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('address')
                            ->required()
                            ->maxLength(255)
                            ->disabled()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('contact_number')
                            ->tel()
                            ->maxLength(20)
                            ->disabled(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Suppliers')
                    ->description('Suppliers associated with this warehouse')
                    ->schema([
                        Forms\Components\Repeater::make('suppliers')
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->disabled(),
                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->disabled(),
                                Forms\Components\TextInput::make('role')
                                    ->disabled(),
                            ])
                            ->columns(3)
                            ->disableItemDeletion()
                            ->disableItemCreation()
                            ->collapsible()
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn(Warehouse $record): string => $record->address)
                    ->icon('heroicon-m-building-storefront'),

                Tables\Columns\TextColumn::make('suppliers_count')
                    ->label('Suppliers')
                    ->counts('suppliers')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                Tables\Columns\TextColumn::make('contact_number')
                    ->label('Contact')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->icon('heroicon-m-phone'),
            ])
            ->filters([
                // REMOVE THIS LINE:
                // Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->button(),
            ])
            ->bulkActions([
                // No bulk actions for view-only resources
            ])
            ->striped();
    }

    public static function getRelations(): array
    {
        return [
            SuppliersRelationManager::class,
            InventoryRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWarehouses::route('/'),
            'view' => ViewWarehouse::route('/{record}'),
        ];
    }
}
