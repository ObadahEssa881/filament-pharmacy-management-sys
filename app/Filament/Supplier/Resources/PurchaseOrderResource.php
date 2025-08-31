<?php

namespace App\Filament\Supplier\Resources;

use App\Filament\Supplier\Resources\PurchaseOrderResource\Pages\EditPurchaseOrder;
use App\Filament\Supplier\Resources\PurchaseOrderResource\Pages\ListPurchaseOrders;
use App\Filament\Supplier\Resources\PurchaseOrderResource\Pages\ViewPurchaseOrder;
use App\Filament\Supplier\Resources\PurchaseOrderResource\RelationManagers\ItemsRelationManager;
use App\Filament\Supplier\Resources\PurchaseOrderResource\RelationManagers\InvoiceRelationManager;
use App\Models\Purchaseorder;
use App\Services\SupplierOrderService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PurchaseOrderResource extends Resource
{
    protected static ?string $model = Purchaseorder::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Order Management';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = 'Purchase Order';
    protected static ?string $pluralModelLabel = 'Purchase Orders';

    public static function canCreate(): bool
    {
        // Supplier admins cannot create purchase orders
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Order Information')
                    ->schema([
                        Forms\Components\Select::make('supplier_id')
                            ->label('Supplier')
                            ->relationship('supplier', 'name'),

                        Forms\Components\Select::make('pharmacy_id')
                            ->label('Pharmacy')
                            ->relationship('pharmacy', 'name')
                            ->disabled(),


                        Forms\Components\Select::make('supplier_id')
                            ->label('Warehouse')
                            ->relationship('supplier.warehouse', 'name')
                            ->disabled(),


                        Forms\Components\DatePicker::make('order_date')
                            ->label('Order Date')
                            ->disabled(),

                        Forms\Components\Select::make('status')
                            ->options([
                                'PENDING' => 'Pending',
                                'PROCESSING' => 'Processing',
                                'SHIPPED' => 'Shipped',
                                'DELIVERED' => 'Delivered',
                                'CANCELLED' => 'Cancelled',
                            ])
                            ->helperText('Supplier can only change the status.')
                            ->required(),
                    ])
                    ->columns(5),

                Forms\Components\Section::make('Notes')
                    ->schema([
                        Forms\Components\Textarea::make('notes')->disabled()->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('#')->sortable(),
                Tables\Columns\TextColumn::make('pharmacy.name')->label('Pharmacy')->searchable(),
                Tables\Columns\TextColumn::make('supplier.name')->label('Supplier')->searchable(),
                Tables\Columns\TextColumn::make('supplier.warehouse.name')->label('Warehouse ID')->toggleable(),
                Tables\Columns\TextColumn::make('order_date')->date(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'PENDING' => 'warning',
                        'PROCESSING' => 'info',
                        'SHIPPED' => 'primary',
                        'DELIVERED' => 'success',
                        'CANCELLED' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('invoice.total_amount')->money('USD')->toggleable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\Action::make('mark_processing')
                    ->label('Mark Processing')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->visible(fn($record) => $record->status === 'PENDING')
                    ->action(function (Purchaseorder $record) {
                        app(SupplierOrderService::class)->transition($record, 'PROCESSING');
                    }),

                Tables\Actions\Action::make('mark_shipped')
                    ->label('Mark Shipped')
                    ->icon('heroicon-o-truck')
                    ->visible(fn($record) => $record->status === 'PROCESSING')
                    ->action(function (Purchaseorder $record) {
                        app(SupplierOrderService::class)->transition($record, 'SHIPPED');
                    }),

                Tables\Actions\Action::make('mark_delivered')
                    ->label('Mark Delivered')
                    ->color('success')
                    ->icon('heroicon-o-check-badge')
                    ->visible(fn($record) => $record->status === 'SHIPPED')
                    ->action(function (Purchaseorder $record) {
                        app(SupplierOrderService::class)->transition($record, 'DELIVERED');
                    }),

                Tables\Actions\Action::make('cancel')
                    ->label('Cancel')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn($record) => in_array($record->status, ['PENDING', 'PROCESSING']))
                    ->action(function (Purchaseorder $record) {
                        app(SupplierOrderService::class)->transition($record, 'CANCELLED');
                    }),

                // Edit page is allowed but only status is actually honored server-side.
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
            InvoiceRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPurchaseOrders::route('/'),
            'view' => ViewPurchaseOrder::route('/{record}'),
            'edit' => EditPurchaseOrder::route('/{record}/edit'),
        ];
    }
}
