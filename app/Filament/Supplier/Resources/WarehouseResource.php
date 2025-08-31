<?php

namespace  App\Filament\Supplier\Resources;

use App\Filament\Supplier\Resources\WarehouseResource\Pages\CreateWarehouse;
use App\Filament\Supplier\Resources\WarehouseResource\Pages\EditWarehouse;
use App\Filament\Supplier\Resources\WarehouseResource\Pages\ListWarehouses;
use App\Filament\Supplier\Resources\WarehouseResource\Pages\ViewWarehouse;
use App\Filament\Supplier\Resources\WarehouseResource\RelationManagers;
use App\Filament\SupplierResources\WarehouseResource\RelationManagers\InventoryRelationManager;
use App\Filament\SupplierResources\WarehouseResource\RelationManagers\SuppliersRelationManager;
use App\Filament\Traits\Supplier\FullCrudS;
use App\Models\Warehouse;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WarehouseResource extends Resource
{
    use FullCrudS;
    protected static ?string $model = Warehouse::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationGroup = 'Warehouse Management';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = 'Warehouse';
    protected static ?string $pluralModelLabel = 'Warehouses';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Warehouse Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),


                    ])
                    ->columns(2),

                Forms\Components\Section::make('Location')
                    ->schema([
                        Forms\Components\TextInput::make('address')
                            ->required()
                            ->maxLength(255),




                    ])
                    ->columns(2),

                Forms\Components\Section::make('Contact Information')
                    ->schema([
                        Forms\Components\TextInput::make('contact_number')
                            ->maxLength(255),



                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),



                Tables\Columns\TextColumn::make('address')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('contact_number')
                    ->searchable()
                    ->toggleable(),


            ])
            ->filters([])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('inventory')
                    ->label('View Inventory')
                    ->icon('heroicon-m-cube')
                    ->url(fn(Warehouse $record): string => InventoryResource::getUrl('index', ['tableFilters' => ['warehouse' => ['value' => $record->id]]])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            InventoryRelationManager::class,
            SuppliersRelationManager::class,
        ];
    }
    public static function getEloquentQuery(): Builder
    {
        $warehouseId = auth('supplier')->user()->warehouseId;

        return parent::getEloquentQuery()->where('id', $warehouseId);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWarehouses::route('/'),
            'create' => CreateWarehouse::route('/create'),
            'view' => ViewWarehouse::route('/{record}'),
            'edit' => EditWarehouse::route('/{record}/edit'),
        ];
    }
    public static function canViewAny(): bool
    {
        return true;
    }
}
