<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Filament\Resources\UserResource\Pages\ViewUser;
use App\Filament\Resources\UserResource\RelationManagers\NotificationRelationManager;
use App\Filament\Traits\Pharmacy\FullCrud;
use App\Models\Pharmacy;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class UserResource extends Resource
{
    use FullCrud;

    protected static ?string $model = User::class;
    protected static ?string $navigationGroup = 'Pharmacy Management';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $modelLabel = 'User';
    protected static ?string $pluralModelLabel = 'Users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('User Information')
                    ->description('Basic information about the user')
                    ->schema([
                        Forms\Components\TextInput::make('username')
                            ->required()
                            ->maxLength(191)
                            ->autofocus()
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(191)
                            ->unique(ignoreRecord: true)
                            ->columnSpanFull(),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('role')
                                    ->options([
                                        'PHARMACY_OWNER' => 'Pharmacy Owner',
                                        'PHARMACIST' => 'Pharmacist',
                                        'CASHIER' => 'Cashier',
                                    ])
                                    ->required()
                                    ->native(false),

                                Forms\Components\Select::make('pharmacy_id')
                                    ->relationship('pharmacy', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->native(false)
                                    ->visible(
                                        fn(string $operation): bool =>
                                        $operation === 'create' ||
                                            (Auth::check() && Auth::user()->role === 'PHARMACY_OWNER')
                                    ),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('password')
                                    ->password()
                                    ->required(fn(string $context): bool => $context === 'create')
                                    ->dehydrated(fn(array $state): bool => filled($state['password']))
                                    ->dehydrateStateUsing(fn(string $state): string => bcrypt($state))
                                    ->maxLength(191)
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('password_confirmation')
                                    ->password()
                                    ->required(fn(string $context): bool => $context === 'create')
                                    ->same('password')
                                    ->maxLength(191)
                                    ->columnSpan(1),
                            ])
                            ->visible(fn(string $context): bool => $context === 'create'),
                    ])
                    ->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('username')
                    ->searchable()
                    ->sortable()
                    ->description(fn(User $record): string => $record->email)
                    ->icon('heroicon-m-user-circle'),

                Tables\Columns\TextColumn::make('role')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'PHARMACY_OWNER' => 'primary',
                        'PHARMACIST' => 'success',
                        'CASHIER' => 'warning',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('pharmacy.name')
                    ->label('Pharmacy')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->options([
                        'PHARMACY_OWNER' => 'Pharmacy Owner',
                        'PHARMACIST' => 'Pharmacist',
                        'CASHIER' => 'Cashier',
                    ]),

                Tables\Filters\SelectFilter::make('pharmacy_id')
                    ->relationship('pharmacy', 'name')
                    ->label('Pharmacy'),
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
            NotificationRelationManager::class,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('pharmacy_id', static::getPharmacyId());
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'view' => ViewUser::route('/{record}'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
