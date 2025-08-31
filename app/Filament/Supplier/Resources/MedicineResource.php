<?php

namespace App\Filament\Supplier\Resources;

use App\Filament\Supplier\Resources\MedicineResource\Pages\CreateMedicine;
use App\Filament\Supplier\Resources\MedicineResource\Pages\ListMedicines;
use App\Filament\Supplier\Resources\MedicineResource\Pages\ViewMedicine;
use App\Filament\SupplierResources\MedicineResource\Pages;
use App\Filament\SupplierResources\MedicineResource\RelationManagers;
use App\Filament\Traits\Supplier\ViewOnlyS;
use App\Models\Medicine;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MedicineResource extends Resource
{
    // use ViewOnlyS;
    protected static ?string $model = Medicine::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Inventory Management';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = 'Medicine';
    protected static ?string $pluralModelLabel = 'Medicines';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Medicine Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('titer')
                            ->label('Strength')
                            ->required()
                            ->maxLength(100),

                        Forms\Components\Select::make('category_id')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('company_id')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),


                    ])
                    ->columns(3),

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

                Tables\Columns\TextColumn::make('titer')
                    ->label('Strength')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->badge()
                    ->color('primary')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('company.name')
                    ->badge()
                    ->color('info')
                    ->toggleable(),

            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name'),

                Tables\Filters\SelectFilter::make('company')
                    ->relationship('company', 'name'),


            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('addToInventory')
                    ->label('Add to Inventory')
                    ->icon('heroicon-m-plus-circle')
                    ->color('success')
                    ->modalHeading('Add Medicine to Inventory')
                    ->form([
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


                        Forms\Components\TextInput::make('quantity')
                            ->numeric()
                            ->required()
                            ->minValue(1),

                        Forms\Components\DatePicker::make('expiry_date')
                            ->required()
                            ->default(now()->addYear()),
                    ])
                    ->action(function (array $data, Medicine $record) {
                        $warehouseId = auth('supplier')->user();
                        // dd($warehouseId->warehouseId);
                        \App\Models\Inventory::create([
                            'medicine_id' => $record->id,
                            'warehouse_id' => $warehouseId->warehouseId,
                            'quantity' => $data['quantity'],
                            'cost_price' => $data['cost_price'],
                            'selling_price' => $data['selling_price'],
                            'expiry_date' => $data['expiry_date'],
                            'last_updated' => now(),
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('Medicine added to inventory')
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

    public static function getRelations(): array
    {
        return [
            RelationManagers\InventoryRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMedicines::route('/'),
            'view' => ViewMedicine::route('/{record}'),
        ];
    }
    public static function canViewAny(): bool
    {
        return true;
    }
}
