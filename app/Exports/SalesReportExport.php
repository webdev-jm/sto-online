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

class SalesReportExport implements FromCollection, ShouldAutoSize, WithStyles, WithProperties, WithBackgroundColor, WithCustomChunkSize, WithEvents
{
    public $sales;

    public function __construct($sales) {
        $this->sales = $sales;
    }

    public function backgroundColor()
    {
        return null;
    }

    public function properties(): array
    {
        return [
            'creator'        => 'STO ONLINE',
            'lastModifiedBy' => 'STO',
            'title'          => 'SALES DATA DETAILS',
            'description'    => 'List of Sales Data',
            'subject'        => 'STO SALES',
            'keywords'       => 'sto sales,export,spreadsheet',
            'category'       => 'STO SALES',
            'manager'        => 'STO ONLINE SYSTEM',
            'company'        => 'BEVI',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Title
            1 => [
                'font' => ['bold' => true, 'size' => 15],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'color' => ['argb' => 'E7FDEC']
                ]
            ],
            // header
            2 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'color' => ['argb' => 'ddfffd']
                ]
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
            AfterSheet::class => function(AfterSheet $event) {
                // This disables the calculation engine for the entire sheet
                $event->sheet->getDelegate()->getParent()->getCalculationEngine()->setCalculationCacheEnabled(false);
            },
        ];
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        // header
        $header = [
            'DATE',
            'INVOICE NUMBER',
            'CUSTOMER CODE',
            'CUSTOMER NAME',
            'ADDRESS',
            'STREET',
            'BRGY',
            'CITY',
            'PROVINCE',
            'SALESMAN CODE',
            'SALESMAN NAME',
            'CHANNEL CODE',
            'CHANNEL NAME',
            'LOCATION CODE',
            'LOCATION NAME',
            'SKU CODE',
            'DESCRIPTION',
            'UOM',
            'QUANTITY',
            'PRICE INC VAT',
            'AMOUNT',
            'AMOUNT INC. VAT',
            'TYPE',
        ];

        $type_arr = [
            1 => 'NORMAL',
            2 => 'FG',
            3 => 'PROMO'
        ];

        $data = array();
        foreach($this->sales as $sale) {
            $data[] = [
                $sale->date,
                $sale->document_number,
                $sale->customer->code,
                $sale->customer->name,
                $sale->customer->address,
                $sale->customer->street,
                $sale->customer->brgy,
                $sale->customer->city,
                $sale->customer->province,
                isset($sale->salesman->code) ? $sale->salesman->code : $sale->customer->salesman->code ?? '',
                isset($sale->salesman->name) ? $sale->salesman->name : $sale->customer->salesman->name ?? '',
                isset($sale->channel->code) ? $sale->channel->code : $sale->customer->channel->code ?? '',
                isset($sale->channel->name) ? $sale->channel->name : $sale->customer->channel->name ?? '',
                $sale->location->code,
                $sale->location->name,
                $sale->product->stock_code,
                $sale->product->description.' '.$sale->product->size,
                $sale->uom,
                $sale->quantity,
                $sale->price_inc_vat,
                $sale->amount,
                $sale->amount_inc_vat,
                $type_arr[$sale->type] ?? '-',
            ];
        }

        return new Collection([
            ['STO ONLINE - SALES DATA'],
            $header,
            $data,
        ]);
    }
}
