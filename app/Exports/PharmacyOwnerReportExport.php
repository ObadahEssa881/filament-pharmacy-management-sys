<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class PharmacyOwnerReportExport implements FromView
{
    public function __construct(public array $reportData) {}

    public function view(): View
    {
        return view('exports.pharmacy-owner-report', [
            'reportData' => $this->reportData,
        ]);
    }
}
