<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseOrderResource\Pages;
use App\Filament\Resources\PurchaseOrderResource\RelationManagers;
use App\Models\Purchaseorder;
use App\Services\PurchaseService;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\View;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;

class PurchaseOrderResource extends Resource
{
    protected static ?string $model = Purchaseorder::class;
    protected static ?string $navigationGroup = 'Inventory Management';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $modelLabel = 'Purchase Order';
    protected static ?string $pluralModelLabel = 'Purchase Orders';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Order Details')
                    ->description('Select warehouse and supplier for this order')
                    ->schema([
                        Select::make('warehouse_id')
                            ->label('Warehouse')
                            ->options(function () {
                                return \App\Models\Warehouse::all()->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live(onBlur: true), // This triggers the medicine-selection component

                        // Medicine selection component
                        View::make('filament.forms.components.medicine-selection')
                            ->viewData([
                                'warehouseId' => $form->getRawState()['warehouse_id'] ?? null,
                            ])
                            ->hidden(fn($get) => !$get('warehouse_id')),

                        Select::make('supplier_id')
                            ->label('Supplier')
                            ->options(fn() => \App\Models\Supplier::all()->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $supplier = \App\Models\Supplier::find($state);
                                $set('warehouse_id', $supplier?->warehouse_id);
                                $set('warehouse_name', $supplier?->warehouse?->name);
                            }),

                    ])
                    ->columns(2),

                // Hidden field to store selected items
                Forms\Components\Hidden::make('selected_items')
                    ->default(json_encode([])),

                // Cart summary section
                Section::make('Order Summary')
                    ->schema([
                        Repeater::make('items')
                            ->schema([
                                Forms\Components\Hidden::make('medicine_id'),
                                TextInput::make('medicine_name')
                                    ->label('Medicine')
                                    ->disabled()
                                    ->extraInputAttributes(['class' => 'text-gray-900 dark:text-white'])
                                    ->columnSpan(2),
                                TextInput::make('quantity')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->default(1)
                                    ->extraInputAttributes(['class' => 'text-gray-900 dark:text-white'])
                                    ->suffixAction(
                                        Forms\Components\Actions\Action::make('adjust')
                                            ->icon('heroicon-m-adjustments-vertical')
                                            ->color('primary')
                                            ->action(fn(callable $set, $get) => $set('quantity', $get('quantity') + 1))
                                    )
                                    ->afterStateUpdated(function ($state, callable $set, $get) {
                                        $unitPrice = $get('unit_price') ?? 0;
                                        $set('total_price', $state * $unitPrice);
                                    }),
                                TextInput::make('unit_price')
                                    ->numeric()
                                    ->prefix('$')
                                    ->step(0.01)
                                    ->disabled()
                                    ->extraInputAttributes(['class' => 'text-gray-900 dark:text-white'])
                                    ->hint('Price controlled by warehouse inventory'),
                                TextInput::make('total_price')
                                    ->numeric()
                                    ->prefix('$')
                                    ->step(0.01)
                                    ->disabled()
                                    ->extraInputAttributes(['class' => 'text-gray-900 dark:text-white'])
                                    ->label('Total Price'),
                            ])
                            ->columns(5)
                            ->hiddenLabel()
                            ->defaultItems(0)
                            ->addActionLabel('+ Add Medicine')
                            ->deletable()
                            ->collapsible(),
                    ])
                    ->hidden(fn($get) => empty($get('items'))),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(PurchaseOrder::query()->with(['supplier.warehouse']))
            ->columns([
                TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('supplier.warehouse.name')
                    ->label('Warehouse')
                    ->sortable(),
                TextColumn::make('order_date')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'PENDING' => 'warning',
                        'PROCESSING' => 'info',
                        'SHIPPED' => 'primary',
                        'DELIVERED' => 'success',
                        'CANCELLED' => 'danger',
                    })
                    ->sortable(),

                TextColumn::make('invoice.total_amount')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('invoice.payment_status')
                    ->label('Payment Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'PAID' => 'success',
                        'UNPAID' => 'warning',
                        'PARTIAL' => 'info',
                    })
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'PENDING' => 'Pending',
                        'PROCESSING' => 'Processing',
                        'SHIPPED' => 'Shipped',
                        'DELIVERED' => 'Delivered',
                        'CANCELLED' => 'Cancelled',
                    ]),

                Tables\Filters\SelectFilter::make('supplier_id')
                    ->relationship('supplier', 'name')
                    ->label('Supplier'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->button(),
                Tables\Actions\EditAction::make()
                    ->button(),

                // // Action for processing order
                // Action::make('process_order')
                //     ->label('Process Order')
                //     ->color('info')
                //     ->visible(fn($record) => $record->status === 'PENDING')
                //     ->action(function ($record) {
                //         $response = Http::withToken(Session::get('nestjs_token'))
                //             ->put(config('services.nestjs.url') . '/purchase-orders/' . $record->id . '/status', [
                //                 'status' => 'PROCESSING'
                //             ]);

                //         if ($response->successful()) {
                //             \Filament\Notifications\Notification::make()
                //                 ->title('Order processing started')
                //                 ->success()
                //                 ->send();
                //         } else {
                //             \Filament\Notifications\Notification::make()
                //                 ->title('Error processing order')
                //                 ->body($response->json('message') ?? 'Please try again')
                //                 ->danger()
                //                 ->send();
                //         }
                //     }),

                // Action for marking as shipped
                Action::make('mark_shipped')
                    ->label('Mark as Shipped')
                    ->color('primary')
                    ->visible(fn($record) => $record->status === 'PROCESSING')
                    ->action(function ($record) {
                        $response = Http::withToken(Session::get('nestjs_token'))
                            ->put(config('services.nestjs.url') . '/purchase-orders/' . $record->id . '/status', [
                                'status' => 'SHIPPED'
                            ]);

                        if ($response->successful()) {
                            \Filament\Notifications\Notification::make()
                                ->title('Order marked as shipped')
                                ->success()
                                ->send();
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->title('Error marking order as shipped')
                                ->body($response->json('message') ?? 'Please try again')
                                ->danger()
                                ->send();
                        }
                    }),

                // Action for marking as delivered
                Action::make('mark_delivered')
                    ->label('Mark as Delivered')
                    ->color('success')
                    ->visible(fn($record) => $record->status === 'SHIPPED')
                    ->action(function ($record) {
                        $response = Http::withToken(Session::get('nestjs_token'))
                            ->put(config('services.nestjs.url') . '/purchase-orders/' . $record->id . '/status', [
                                'status' => 'DELIVERED'
                            ]);

                        if ($response->successful()) {
                            \Filament\Notifications\Notification::make()
                                ->title('Order marked as delivered')
                                ->success()
                                ->send();
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->title('Error marking order as delivered')
                                ->body($response->json('message') ?? 'Please try again')
                                ->danger()
                                ->send();
                        }
                    }),

                // Action for cancellation - FIXED
                Action::make('cancel_order')
                    ->label('Cancel Order')
                    ->color('danger')
                    ->visible(fn($record) => $record->status === 'PENDING')
                    ->requiresConfirmation()
                    ->action(function (Action $action) {
                        try {
                            $record = $action->getRecord();
                            $purchaseService = app(PurchaseService::class);
                            $purchaseService->updateStatus($record->id, 'CANCELLED');

                            \Filament\Notifications\Notification::make()
                                ->title('Order cancelled')
                                ->success()
                                ->send();

                            // CORRECT WAY TO REFRESH
                            $action->refresh();
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('Error cancelling order')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
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
            RelationManagers\ItemsRelationManager::class,
            RelationManagers\InvoiceRelationManager::class,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('pharmacy_id', auth()->user()->pharmacy_id);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchaseOrders::route('/'),
            'create' => Pages\CreatePurchaseOrder::route('/create'),
            'view' => Pages\ViewPurchaseOrder::route('/{record}'),
            'edit' => Pages\EditPurchaseOrder::route('/{record}/edit'),
        ];
    }
}
