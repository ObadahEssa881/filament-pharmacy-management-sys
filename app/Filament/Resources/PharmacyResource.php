<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PharmacyResource\Pages\ListPharmacies;
use App\Filament\Resources\PharmacyResource\Pages\ViewPharmacy;
use App\Filament\Resources\PharmacyResource\RelationManagers\UsersRelationManager;
use App\Filament\Traits\Pharmacy\ViewOnly;
use App\Models\Pharmacy;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class PharmacyResource extends Resource
{
    use ViewOnly;

    protected static ?string $model = Pharmacy::class;
    protected static ?string $navigationGroup = 'Pharmacy Management';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $modelLabel = 'Pharmacy';
    protected static ?string $pluralModelLabel = 'Pharmacy Profile';
    protected static bool $shouldRegisterNavigation = true;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Pharmacy Information')
                    ->description('Basic information about your pharmacy')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(191)
                            ->disabled()
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('address')
                            ->required()
                            ->maxLength(191)
                            ->disabled()
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('contact_number')
                            ->tel()
                            ->required()
                            ->maxLength(191)
                            ->disabled(),

                        Forms\Components\TextInput::make('owner.name')
                            ->label('Pharmacy Owner')
                            ->disabled(),
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
                    ->weight('medium')
                    ->icon('heroicon-m-building-storefront')
                    ->description(fn(Pharmacy $record): string => $record->address),

                Tables\Columns\TextColumn::make('contact_number')
                    ->searchable()
                    ->icon('heroicon-m-phone')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('owner.name')
                    ->label('Owner')
                    ->searchable()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->button(),
            ])
            ->striped();
    }

    public static function getRelations(): array
    {
        return [
            UsersRelationManager::class,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('id', static::getPharmacyId());
    }

    protected static function getPharmacyId(): int
    {
        // Safely get the pharmacy ID with null checks
        $user = Auth::user();
        if (!$user) {
            return 0; // Will return empty results
        }

        return $user->pharmacy_id ?? 0;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPharmacies::route('/'),
            'view' => ViewPharmacy::route('/{record}'),
        ];
    }
}
