<?php

namespace  App\Filament\Supplier\Resources;

use App\Filament\Supplier\Resources\InventoryResource\Pages\CreateInventory;
use App\Filament\Supplier\Resources\InventoryResource\Pages\EditInventory;
use App\Filament\Supplier\Resources\InventoryResource\Pages\ListInventories;
use App\Filament\Supplier\Resources\InventoryResource\Pages\ViewInventory;
use App\Filament\SupplierResources\InventoryResource\Pages;
use App\Filament\SupplierResources\InventoryResource\RelationManagers;
use App\Filament\Traits\Supplier\FullCrudS;
use App\Models\Inventory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InventoryResource extends Resource
{
    use FullCrudS;
    protected static ?string $model = Inventory::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Inventory Management';
    protected static ?int $navigationSort = 2;
    protected static ?string $modelLabel = 'Inventory Item';
    protected static ?string $pluralModelLabel = 'Inventory';

    public static function form(Form $form): Form
    {

        // dd(auth('supplier')->user());
        return $form
            ->schema([
                Forms\Components\Section::make('Inventory Details')
                    ->schema([
                        Forms\Components\Select::make('medicine_id')
                            ->label('Medicine')
                            ->relationship('medicine', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        // Hidden warehouse field - automatically set from user
                        Forms\Components\Hidden::make('warehouse_id')
                            ->default(function () {
                                return auth('supplier')->user()->warehouseId;
                            })
                            ->required(),

                        Forms\Components\TextInput::make('quantity')
                            ->label('Quantity')
                            ->numeric()
                            ->required()
                            ->minValue(1),

                        Forms\Components\TextInput::make('cost_price')
                            ->label('Cost Price')
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->required(),

                        Forms\Components\TextInput::make('selling_price')
                            ->label('Selling Price')
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->required(),

                        Forms\Components\DatePicker::make('expiry_date')
                            ->label('Expiry Date')
                            ->required()
                            ->default(now()->addYear()),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Placeholder::make('created_at')
                            ->label('Created at')
                            ->content(
                                fn(?Inventory $record): string =>
                                $record && $record->created_at ? $record->created_at->diffForHumans() : '-'
                            ),

                        Forms\Components\Placeholder::make('updated_at')
                            ->label('Last modified at')
                            ->content(
                                fn(?Inventory $record): string =>
                                $record && $record->updated_at ? $record->updated_at->diffForHumans() : '-'
                            ),
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
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('medicine.titer')
                    ->label('Strength')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('warehouse.name')
                    ->badge()
                    ->color('info')
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

                Tables\Columns\TextColumn::make('cost_price')
                    ->label('Cost Price')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('selling_price')
                    ->label('Selling Price')
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
                // Keep your existing filters
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
                        Forms\Components\TextInput::make('quantity')
                            ->label('Quantity')
                            ->numeric()
                            ->required()
                            ->minValue(1),

                        Forms\Components\TextInput::make('cost_price')
                            ->label('New Cost Price')
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->helperText('Leave blank to keep current price')
                            ->required(false),

                        Forms\Components\TextInput::make('selling_price')
                            ->label('New Selling Price')
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->helperText('Leave blank to keep current price')
                            ->required(false),

                        Forms\Components\DatePicker::make('expiry_date')
                            ->label('Expiry Date')
                            ->required()
                            ->default(now()->addYear()),
                    ])
                    ->action(function (array $data, Inventory $record) {
                        $updateData = [
                            'quantity' => $record->quantity + $data['quantity'],
                            'expiry_date' => $data['expiry_date'],
                            'last_updated' => now(),
                        ];

                        // Only update prices if new values are provided
                        if (!empty($data['cost_price'])) {
                            $updateData['cost_price'] = $data['cost_price'];
                        }

                        if (!empty($data['selling_price'])) {
                            $updateData['selling_price'] = $data['selling_price'];
                        }

                        $record->update($updateData);

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




    public static function getPages(): array
    {
        return [
            'index' => ListInventories::route('/'),
            'create' => CreateInventory::route('/create'),
            'view' => ViewInventory::route('/{record}'),
            'edit' => EditInventory::route('/{record}/edit'),
        ];
    }
    // public static function canViewAny(): bool
    // {
    //     return true;
    // }
}
