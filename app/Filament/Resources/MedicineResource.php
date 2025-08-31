<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MedicineResource\Pages;
use App\Filament\Resources\MedicineResource\RelationManagers;
use App\Models\Inventory;
use App\Models\Medicine;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class MedicineResource extends Resource
{
    protected static ?string $model = Medicine::class;
    protected static ?string $navigationGroup = 'Pharmacy Management';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $modelLabel = 'Medicine';
    protected static ?string $pluralModelLabel = 'All Medicines';
    protected static ?string $navigationDescription = 'View all medicines in the central database';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Medicine Information')
                    ->description('Basic information about the medicine')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(191)
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('titer')
                            ->required()
                            ->maxLength(191),

                        Forms\Components\Select::make('category_id')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false),

                        Forms\Components\Select::make('company_id')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false),

                        Forms\Components\TextInput::make('unit_price')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01),

                        Forms\Components\Select::make('supplier_id')
                            ->relationship('supplier', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false),

                        Forms\Components\TextInput::make('Type')
                            ->maxLength(191),
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
                    ->weight('medium')
                    ->description(fn(Medicine $record): string => $record->titer)
                    ->icon('heroicon-m-cube'),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('company.name')
                    ->label('Company')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('unit_price')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('Type')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // Column to show if medicine is in current pharmacy's inventory
                IconColumn::make('in_inventory')
                    ->label('In Your Inventory')
                    ->boolean()
                    ->getStateUsing(function (Medicine $record) {
                        return Inventory::where('medicine_id', $record->id)
                            ->where('pharmacy_id', Auth::user()->pharmacy_id)
                            ->exists();
                    })
                    ->trueIcon('heroicon-m-check-circle')
                    ->falseIcon('heroicon-m-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),

                // Column to show current stock level if in inventory
                Tables\Columns\TextColumn::make('current_stock')
                    ->label('Your Stock')
                    ->getStateUsing(function (Medicine $record) {
                        $totalQuantity = Inventory::where('medicine_id', $record->id)
                            ->where('pharmacy_id', Auth::user()->pharmacy_id)
                            ->sum('quantity');

                        return $totalQuantity > 0 ? $totalQuantity : 'Not in inventory';
                    })
                    ->badge()
                    ->color(
                        fn(string $state): string =>
                        match (true) {
                            is_numeric($state) && $state <= 5 => 'danger',
                            is_numeric($state) && $state <= 15 => 'warning',
                            is_numeric($state) => 'success',
                            default => 'gray',
                        }
                    ),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->relationship('category', 'name')
                    ->label('Category'),

                Tables\Filters\SelectFilter::make('company_id')
                    ->relationship('company', 'name')
                    ->label('Company'),

                Tables\Filters\SelectFilter::make('supplier_id')
                    ->relationship('supplier', 'name')
                    ->label('Supplier'),

                // Filter for medicines in inventory
                Tables\Filters\Filter::make('in_inventory')
                    ->label('Only Medicines in Your Inventory')
                    ->query(
                        fn(Builder $query): Builder =>
                        $query->whereHas('inventories', function ($q) {
                            $q->where('pharmacy_id', Auth::user()->pharmacy_id);
                        })
                    ),
            ])
            ->actions([
                // MODIFIED: Add to Inventory action with modal form
                Action::make('add_to_inventory')
                    ->label('Add to Inventory')
                    ->icon('heroicon-m-plus-circle')
                    ->color('success')
                    ->modalHeading('Add Medicine to Inventory')
                    ->modalSubmitActionLabel('Add to Inventory')
                    ->form([
                        Forms\Components\TextInput::make('quantity')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(1),

                        Forms\Components\TextInput::make('cost_price')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->default(fn($record) => $record->unit_price),

                        Forms\Components\TextInput::make('selling_price')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->default(fn($record) => $record->unit_price * 1.2),

                        Forms\Components\DatePicker::make('expiry_date')
                            ->minDate(now())
                            ->required()
                            ->default(now()->addMonths(6)),
                    ])
                    ->action(function (array $data, Medicine $record) {
                        Inventory::create([
                            'medicine_id' => $record->id,
                            'pharmacy_id' => Auth::user()->pharmacy_id,
                            'quantity' => $data['quantity'],
                            'cost_price' => $data['cost_price'],
                            'selling_price' => $data['selling_price'],
                            'expiry_date' => $data['expiry_date'],
                            'last_updated' => now(),
                        ]);

                        Notification::make()
                            ->title('Medicine added to inventory')
                            ->success()
                            ->send();
                    })
                    ->visible(fn(Medicine $record) => true), // Always visible

                Tables\Actions\ViewAction::make()
                    ->button(),
                Tables\Actions\EditAction::make()
                    ->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // NEW: Bulk action to add selected medicines to inventory with form
                    Action::make('add_selected_to_inventory')
                        ->label('Add selected to inventory')
                        ->icon('heroicon-m-plus-circle')
                        ->color('success')
                        ->modalHeading('Add Medicines to Inventory')
                        ->modalSubmitActionLabel('Add to Inventory')
                        ->form([
                            Forms\Components\TextInput::make('quantity')
                                ->required()
                                ->numeric()
                                ->minValue(1)
                                ->default(1),

                            Forms\Components\TextInput::make('cost_price')
                                ->required()
                                ->numeric()
                                ->prefix('$')
                                ->step(0.01),

                            Forms\Components\TextInput::make('selling_price')
                                ->required()
                                ->numeric()
                                ->prefix('$')
                                ->step(0.01),

                            Forms\Components\DatePicker::make('expiry_date')
                                ->minDate(now())
                                ->required()
                                ->default(now()->addMonths(6)),
                        ])
                        ->action(function (array $data, array $records) {
                            $addedCount = 0;

                            foreach ($records as $recordId) {
                                $medicine = Medicine::find($recordId);

                                if (!$medicine) {
                                    continue;
                                }

                                Inventory::create([
                                    'medicine_id' => $medicine->id,
                                    'pharmacy_id' => Auth::user()->pharmacy_id,
                                    'quantity' => $data['quantity'],
                                    'cost_price' => $data['cost_price'],
                                    'selling_price' => $data['selling_price'],
                                    'expiry_date' => $data['expiry_date'],
                                    'last_updated' => now(),
                                ]);

                                $addedCount++;
                            }

                            Notification::make()
                                ->title($addedCount . ' medicines added to inventory')
                                ->success()
                                ->send();
                        }),
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMedicines::route('/'),
            'create' => Pages\CreateMedicine::route('/create'),
            'view' => Pages\ViewMedicine::route('/{record}'),
            'edit' => Pages\EditMedicine::route('/{record}/edit'),
        ];
    }
}
