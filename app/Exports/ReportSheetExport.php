<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomChunkSize;

class ReportSheetExport implements FromQuery, WithTitle, WithHeadings, WithCustomChunkSize
{

    protected $tableName;
    protected $sheetTitle;
    protected $year;

    public function __construct(string $tableName, string $sheetTitle, int $year)
    {
        $this->tableName = $tableName;
        $this->sheetTitle = $sheetTitle;
        $this->year = $year;
    }

    public function query()
    {
        return DB::connection('sqlite_reports')
            ->table($this->tableName)
            ->where('year', $this->year)
            ->orderBy('id');
    }

    public function headings(): array
    {
        // Get column names from the first record
        $firstRecord = DB::connection('sqlite_reports')
            ->table($this->tableName)
            ->where('year', $this->year)
            ->first();

        return $firstRecord ? array_keys((array)$firstRecord) : [];
    }

    public function title(): string
    {
        return $this->sheetTitle;
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
