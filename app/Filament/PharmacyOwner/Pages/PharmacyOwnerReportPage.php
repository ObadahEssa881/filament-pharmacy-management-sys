<?php

namespace App\Filament\PharmacyOwner\Pages;

use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
use App\Services\Reports\PharmacyOwnerReportService;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PharmacyOwnerReportExport;

class PharmacyOwnerReportPage extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationLabel = 'Financial Report';
    protected static ?string $title = 'Pharmacy Owner Financial Report';
    protected static ?string $slug = 'pharmacy-owner-report';
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-bar';

    protected static string $view = 'filament.pharmacy-owner.pages.pharmacy-owner-report-page';

    public array $filters = [];
    public array $reportData = [];

    public function mount(): void
    {
        $this->filters = [
            'start' => now()->startOfMonth()->toDateString(),
            'end'   => now()->endOfMonth()->toDateString(),
        ];
        $this->refreshReport();
        $this->form->fill($this->filters);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(6)->schema([
                Forms\Components\DatePicker::make('start')->label('Start Date')->required()->columnSpan(2),
                Forms\Components\DatePicker::make('end')->label('End Date')->required()->columnSpan(2),
                Forms\Components\Actions::make([
                    Forms\Components\Actions\Action::make('apply')->label('Apply')->submit('applyFilters'),
                    Forms\Components\Actions\Action::make('reset')->label('Reset')->action('resetFilters'),
                ])->fullWidth()->columnSpan(6),
            ])
        ])->statePath('filters');
    }

    public function applyFilters(): void
    {
        $this->filters = $this->form->getState();
        $this->refreshReport();
    }

    public function resetFilters(): void
    {
        $this->mount();
    }

    protected function refreshReport(): void
    {
        $pharmacyId = Auth::user()->pharmacy_id;
        $this->reportData = app(PharmacyOwnerReportService::class)->run($this->filters, $pharmacyId);
    }

    public function exportExcel()
    {
        return Excel::download(new PharmacyOwnerReportExport($this->reportData), 'pharmacy-owner-report.xlsx');
    }
    public function exportSales()
    {
        return Excel::download(new PharmacyOwnerReportExport(
            ['salesRecords' => $this->reportData['salesRecords']]
        ), 'sales-report.xlsx');
    }

    public function exportPurchases()
    {
        return Excel::download(new PharmacyOwnerReportExport(
            ['purchaseRecords' => $this->reportData['purchaseRecords']]
        ), 'purchases-report.xlsx');
    }
}
