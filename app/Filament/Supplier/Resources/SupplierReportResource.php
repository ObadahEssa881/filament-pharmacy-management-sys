<?php

// namespace App\Filament\Supplier\Resources;

// use App\Filament\Supplier\Pages\SupplierReportPage;
// use App\Services\SupplierReportService;
// use Filament\Forms;
// use Filament\Forms\Form;
// use Filament\Resources\Resource;
// use Filament\Tables;
// use Filament\Tables\Table;

// class SupplierReportResource extends Resource
// {
//     protected static ?string $navigationIcon = 'heroicon-o-document-text';
//     protected static ?string $model = null; // Not linked to a DB model

//     public static function form(Form $form): Form
//     {
//         return $form
//             ->schema([
//                 Forms\Components\DatePicker::make('start_date')->required(),
//                 Forms\Components\DatePicker::make('end_date')->required(),
//             ]);
//     }

//     public static function table(Table $table): Table
//     {
//         return $table->columns([]); // Not needed here, we will handle via actions
//     }

//     public static function getPages(): array
//     {
//         return [
//             'index' => SupplierReportPage::route('/'),
//         ];
//     }
// }
