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
    protected $connection;

    public function __construct(string $tableName, string $sheetTitle, int $year, string $connection = 'sqlite_reports')
    {
        $this->tableName  = $tableName;
        $this->sheetTitle = $sheetTitle;
        $this->year       = $year;
        $this->connection = $connection;
    }

    public function query()
    {
        return DB::connection($this->connection)
            ->table($this->tableName)
            ->where('year', $this->year)
            ->orderBy('id');
    }

    public function headings(): array
    {
        $firstRecord = DB::connection($this->connection)
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
