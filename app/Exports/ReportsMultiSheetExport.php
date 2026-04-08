<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ReportsMultiSheetExport implements WithMultipleSheets
{

    use Exportable;

    protected $year;

    public function __construct(int $year) {
        $this->year = $year;
    }

    public function sheets(): array
    {
        return [
            new ReportSheetExport('sales_data', 'Sales Data', $this->year),
            new ReportSheetExport('inventory_data', 'Inventory Data', $this->year),
            new ReportSheetExport('inventory_aging', 'Inventory Aging', $this->year),
        ];
    }
}
