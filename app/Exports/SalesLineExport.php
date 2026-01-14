<?php

namespace App\Exports;

use Illuminate\Support\Collection;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithProperties;
use Maatwebsite\Excel\Concerns\WithBackgroundColor;
use Maatwebsite\Excel\Concerns\WithCustomChunkSize;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesLineExport implements FromCollection, ShouldAutoSize, WithStyles, WithProperties, WithBackgroundColor, WithCustomChunkSize
{
    public $sales_upload;

    public function __construct($sales_upload) {
        $this->sales_upload = $sales_upload;
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
            'title'          => 'SALES UPLOAD DETAILS',
            'description'    => 'List of Sales Uploads',
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

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        // header
        $header = [
            'DATE',
            'DOCUMENT NUMBER',
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
            'LOCATION',
            'SKU CODE',
            'DESCRIPTION',
            'UOM',
            'QUANTITY',
            'AMOUNT',
            'AMOUNT INC. VAT',
        ];

        $sales = $this->sales_upload->sales()
            ->with(['salesman', 'location', 'product', 'customer'])
            ->get();

        $data = array();
        foreach($sales as $sale) {
            $data[] = [
                $sale->date ?? '',
                $sale->document_number ?? '',
                $sale->customer->code ?? '',
                $sale->customer->name ?? '',
                $sale->customer->address ?? '',
                $sale->customer->street ?? '',
                $sale->customer->brgy ?? '',
                $sale->customer->city ?? '',
                $sale->customer->province ?? '',
                $sale->salesman->code ? $sale->customer->salesman->code : '',
                $sale->salesman->name ? $sale->customer->salesman->name : '',
                $sale->channel->code ? $sale->customer->channel->code : '',
                $sale->channel->name ? $sale->customer->channel->name : '',
                $sale->location->code ?? '',
                $sale->product->stock_code ?? '',
                $sale->product->description ?? '',
                $sale->uom ?? '',
                $sale->quantity ?? '',
                $sale->amount ?? '',
                $sale->amount_inc_vat ?? '',
            ];
        }

        return new Collection([
            ['STO ONLINE'],
            $header,
            $data,
        ]);
    }
}
