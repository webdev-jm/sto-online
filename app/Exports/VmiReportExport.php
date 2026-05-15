<?php

namespace App\Exports;

use Illuminate\Support\Collection;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithProperties;
use Maatwebsite\Excel\Concerns\WithBackgroundColor;
use Maatwebsite\Excel\Concerns\WithCustomChunkSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VmiReportExport implements FromCollection, ShouldAutoSize, WithStyles, WithProperties, WithBackgroundColor, WithCustomChunkSize, WithEvents
{
    public function __construct(
        private array $data,
        private array $months_arr,
        private int $year,
        private int $month,
        private int $parameter,
    ) {}

    public function backgroundColor(): ?string
    {
        return null;
    }

    public function properties(): array
    {
        return [
            'creator'        => 'STO ONLINE',
            'lastModifiedBy' => 'STO',
            'title'          => 'VMI REPORT',
            'description'    => 'Vendor-Managed Inventory Report',
            'subject'        => 'STO VMI',
            'keywords'       => 'sto vmi,export,spreadsheet',
            'category'       => 'STO VMI',
            'manager'        => 'STO ONLINE SYSTEM',
            'company'        => 'BEVI',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 15],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'color' => ['argb' => 'E7FDEC'],
                ],
            ],
            2 => [
                'font' => ['bold' => true, 'size' => 12],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'color' => ['argb' => 'ddfffd'],
                ],
            ],
        ];
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->getParent()->getCalculationEngine()->setCalculationCacheEnabled(false);
            },
        ];
    }

    public function collection(): Collection
    {
        $header = ['STOCK CODE', 'DESCRIPTION', 'INV TOTAL CS'];

        foreach (array_keys($this->months_arr) as $n) {
            $header[] = $n . 'MO STO CS';
            $header[] = $n . 'MO WEEK COV';
            $header[] = $n . 'MO COV NEED';
            $header[] = $n . 'MO TO ORDER';
        }

        $rows = [
            ['STO ONLINE - VMI REPORT - ' . $this->year . '/' . str_pad($this->month, 2, '0', STR_PAD_LEFT) . ' (Param: ' . $this->parameter . ' wks)'],
            $header,
        ];

        foreach ($this->data as $row) {
            $line = [
                $row['stock_code'],
                $row['description'],
                round($row['cs_total'], 1),
            ];

            foreach ($row['months_data'] as $m) {
                $line[] = round($m['sto'], 1);
                $line[] = round($m['w_cov'], 1);
                $line[] = round($m['w_cov_needed'], 1);
                $line[] = round($m['vmi'], 1);
            }

            $rows[] = $line;
        }

        return collect($rows);
    }
}
