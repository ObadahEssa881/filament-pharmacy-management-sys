<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupplierResource\Pages\ListSuppliers;
use App\Filament\Resources\SupplierResource\Pages\ViewSupplier;
use App\Filament\Traits\Pharmacy\ViewOnly;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SupplierResource extends Resource
{
    use ViewOnly;

    protected static ?string $model = Supplier::class;
    protected static ?string $navigationGroup = 'Reference Data';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $modelLabel = 'Supplier';
    protected static ?string $pluralModelLabel = 'Suppliers';
    protected static bool $shouldRegisterNavigation = true;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Supplier Information')
                    ->description('Basic information about the supplier')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(191)
                            ->disabled()
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(191)
                            ->disabled(),

                        Forms\Components\TextInput::make('contact_person')
                            ->maxLength(191)
                            ->disabled(),

                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->required()
                            ->maxLength(191)
                            ->disabled(),

                        Forms\Components\TextInput::make('address')
                            ->required()
                            ->maxLength(191)
                            ->disabled()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Warehouse Information')
                    ->description('Warehouse associated with this supplier')
                    ->schema([
                        Forms\Components\TextInput::make('warehouse.name')
                            ->label('Warehouse')
                            ->disabled(),
                        Forms\Components\TextInput::make('warehouse.address')
                            ->label('Address')
                            ->disabled(),
                        Forms\Components\TextInput::make('warehouse.contact_number')
                            ->label('Contact Number')
                            ->disabled(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Additional Information')
                    ->description('Role and other details')
                    ->schema([
                        Forms\Components\Select::make('role')
                            ->options([
                                'SUPPLIER_ADMIN' => 'Admin',
                                'SUPPLIER_EMPLOYEE' => 'Employee',
                            ])
                            ->disabled()
                            ->formatStateUsing(
                                fn(string $state): string =>
                                match ($state) {
                                    'SUPPLIER_ADMIN' => 'Admin',
                                    'SUPPLIER_EMPLOYEE' => 'Employee',
                                    default => $state,
                                }
                            ),
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
                    ->description(fn(Supplier $record): string => $record->email)
                    ->icon('heroicon-m-building-storefront'),

                Tables\Columns\TextColumn::make('contact_person')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->icon('heroicon-m-phone')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('warehouse.name')
                    ->label('Warehouse')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('role')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'SUPPLIER_ADMIN' => 'primary',
                        'SUPPLIER_EMPLOYEE' => 'gray',
                    })
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->options([
                        'SUPPLIER_ADMIN' => 'Admin',
                        'SUPPLIER_EMPLOYEE' => 'Employee',
                    ]),
                Tables\Filters\SelectFilter::make('warehouseId')
                    ->relationship('warehouse', 'name')
                    ->label('Warehouse'),
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
        return [];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSuppliers::route('/'),
            'view' => ViewSupplier::route('/{record}'),
        ];
    }
}
