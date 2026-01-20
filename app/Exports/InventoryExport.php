<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithProperties;
use Maatwebsite\Excel\Concerns\WithBackgroundColor;
use Maatwebsite\Excel\Concerns\WithCustomChunkSize;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InventoryExport implements FromCollection, ShouldAutoSize, WithStyles, WithProperties, WithBackgroundColor, WithCustomChunkSize
{
    public $inventory_upload;

    public function __construct($inventory_upload) {
        $this->inventory_upload = $inventory_upload;
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
            'LOCATION',
            'PRODUCT CODE',
            'DESCRIPTION',
            'UOM',
            'INVENTORY',
            'EXPIRED DATE',
            'TYPE',
        ];

        $type_arr = [
            1 => 'REGULAR',
            2 => 'FG',
            3 => 'PROMO'
        ];

        $data = [];
        foreach($this->inventory_upload->inventories as $inventory) {
            $data[] = [
                $this->inventory_upload->date,
                $inventory->location ? $inventory->location->code : '',
                $inventory->product ? $inventory->product->stock_code : '',
                $inventory->product ? $inventory->product->description : '',
                $inventory->uom,
                $inventory->inventory,
                $inventory->expiry_date ? $inventory->expiry_date : '',
                $type_arr[$inventory->type] ?? '',
            ];
        }

        return new Collection([
            ['STO ONLINE INVENTORY LIST'],
            $header,
            $data,
        ]);
    }
}
