<?php

namespace App\Filament\Supplier\Pages;

use App\Models\Pharmacy;
use App\Models\Supplier;
use App\Models\Warehouse;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use App\Services\Reports\SupplierReportService;

class SupplierReportPage extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Supplier Reports';
    protected static ?string $title = 'Supplier Reports';
    protected static ?string $slug = 'supplier-report-page';

    protected static string $view = 'filament.supplier.pages.supplier-report-page';

    public array $filters = [];
    public array $reportData = [];

    public function mount(): void
    {
        $this->filters = [
            'start'        => now()->startOfMonth()->toDateString(),
            'end'          => now()->endOfMonth()->toDateString(),
            'pharmacy_id'  => null,
            'supplier_id'  => null,
            'warehouse_id' => null,
            'statuses'     => [],
            'group_by'     => 'day',
        ];

        $this->refreshReport();
        $this->form->fill($this->filters);
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Grid::make(6)->schema([
                Forms\Components\DatePicker::make('start')
                    ->label('Start date')
                    ->required()
                    ->columnSpan(2),

                Forms\Components\DatePicker::make('end')
                    ->label('End date')
                    ->required()
                    ->columnSpan(2),

                Forms\Components\Select::make('group_by')
                    ->label('Group by')
                    ->options([
                        'day'   => 'Day',
                        'week'  => 'Week',
                        'month' => 'Month',
                    ])
                    ->default('day')
                    ->columnSpan(2),

                Forms\Components\Select::make('pharmacy_id')
                    ->label('Pharmacy')
                    ->options(fn() => Pharmacy::pluck('name', 'id')->toArray())
                    ->searchable()
                    ->preload()
                    ->columnSpan(2),

                Forms\Components\Select::make('warehouse_id')
                    ->label('Warehouse')
                    ->options(fn() => Warehouse::where('owner_id', Auth::id())->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->columnSpan(2),

                Forms\Components\Select::make('supplier_id')
                    ->label('Supplier')
                    ->options(function (callable $get) {
                        $warehouseId = $get('warehouse_id');
                        if (!$warehouseId) {
                            return [];
                        }
                        // Supplier must belong to selected warehouse
                        return Supplier::where('warehouseId', $warehouseId)->pluck('name', 'id');
                    })
                    ->searchable()
                    ->preload()
                    ->columnSpan(2),

                Forms\Components\Select::make('statuses')
                    ->label('Statuses')
                    ->options([
                        'PENDING'   => 'PENDING',
                        'SHIPPED'   => 'SHIPPED',
                        'DELIVERED' => 'DELIVERED',
                        'CANCELLED' => 'CANCELLED',
                    ])
                    ->multiple()
                    ->columnSpan(3),

                Forms\Components\Actions::make([
                    Forms\Components\Actions\Action::make('apply')
                        ->label('Apply filters')
                        ->submit('applyFilters')
                        ->color('primary')
                        ->icon('heroicon-o-arrow-path'),
                    Forms\Components\Actions\Action::make('reset')
                        ->label('Reset')
                        ->action('resetFilters')
                        ->color('secondary')
                        ->icon('heroicon-o-arrow-uturn-left'),
                ])
                    ->fullWidth()
                    ->columnSpan(6),
            ]),
        ];
    }

    public function form(Form $form): Form
    {
        return $form->schema($this->getFormSchema())->statePath('filters');
    }

    public function applyFilters(): void
    {
        $this->filters = $this->form->getState();

        if (!in_array(($this->filters['group_by'] ?? 'day'), ['day', 'week', 'month'], true)) {
            $this->filters['group_by'] = 'day';
        }

        $this->refreshReport();
        $this->dispatchBrowserEvent('report-updated'); // tell JS to redraw chart
    }

    public function resetFilters(): void
    {
        $this->mount(); // re-init defaults + refresh
    }

    protected function refreshReport(): void
    {
        $this->reportData = app(SupplierReportService::class)->run($this->filters);
    }
}
