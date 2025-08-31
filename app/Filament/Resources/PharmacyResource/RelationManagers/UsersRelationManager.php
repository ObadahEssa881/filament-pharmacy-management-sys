<?php

namespace App\Filament\Resources\PharmacyResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('username')
                    ->required()
                    ->maxLength(191)
                    ->disabled(),

                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(191)
                    ->disabled(),

                Forms\Components\TextInput::make('role')
                    ->badge()
                    ->disabled()
                    ->color(fn(string $state): string => match ($state) {
                        'PHARMACY_OWNER' => 'primary',
                        'PHARMACIST' => 'success',
                    }),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('username')
            ->columns([
                Tables\Columns\TextColumn::make('username')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('role')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'PHARMACY_OWNER' => 'primary',
                        'PHARMACIST' => 'success',
                    })
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->options([
                        'PHARMACY_OWNER' => 'Pharmacy Owner',
                        'PHARMACIST' => 'Pharmacist',

                    ]),
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                //
            ]);
    }
}
