<?php

namespace  App\Filament\Supplier\Resources;

use App\Filament\Supplier\Resources\PharmacyResource\Pages\ListPharmacies;
use App\Filament\Supplier\Resources\PharmacyResource\Pages\ViewPharmacy;
use App\Filament\SupplierResources\PharmacyResource\Pages;
use App\Filament\SupplierResources\PharmacyResource\RelationManagers;
use App\Filament\Traits\Supplier\ViewOnlyS;
use App\Models\Pharmacy;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PharmacyResource extends Resource
{
    use ViewOnlyS;
    protected static ?string $model = Pharmacy::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationGroup = 'Pharmacy Management';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = 'Pharmacy';
    protected static ?string $pluralModelLabel = 'Pharmacies';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Pharmacy Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('contact_number')
                            ->tel()
                            ->maxLength(20),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Location')
                    ->schema([
                        Forms\Components\TextInput::make('address')
                            ->required()
                            ->maxLength(255),


                    ])
                    ->columns(2),

                Forms\Components\Section::make('Owner Information')
                    ->schema([
                        Forms\Components\Select::make('owner_id')
                            ->label('Owner')
                            ->relationship('owner', 'username') // NOT 'users'
                            ->searchable()
                            ->preload()
                            ->required(),

                    ]),
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


                Tables\Columns\TextColumn::make('contact_number')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('address')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('owner.username')
                    ->label('Owner')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('has_orders')
                    ->label('Orders')
                    ->boolean()
                    ->state(function (Pharmacy $record) {
                        $supplierId = auth('supplier')->id();
                        return $record->purchaseorders->where('supplier_id', $supplierId)->isNotEmpty();
                    })
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

            ])
            ->filters([
                Tables\Filters\SelectFilter::make('owner')
                    ->relationship('owner', 'username'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->boolean()
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only')
                    ->nullable()
                // ->indeterminateLabel('All'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
            RelationManagers\PurchaseOrdersRelationManager::class,
        ];
    }
    public static function getEloquentQuery(): Builder
    {
        $supplierId = auth('supplier')->id();

        return parent::getEloquentQuery()
            ->with('purchaseorders')
            ->whereHas('purchaseorders', function ($query) use ($supplierId) {
                $query->where('supplier_id', $supplierId);
            });
    }


    public static function getPages(): array
    {
        return [
            'index' => ListPharmacies::route('/'),
            'view' => ViewPharmacy::route('/{record}'),
        ];
    }
    // public static function canViewAny(): bool
    // {
    //     return true;
    // }
}
