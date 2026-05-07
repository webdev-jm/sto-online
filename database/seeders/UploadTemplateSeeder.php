<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UploadTemplate;
use App\Models\UploadTemplateField;

class UploadTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            'sales' => [
                'title' => 'Sales Upload',
                'fields' => [
                    ['number' => 0, 'column_name' => 'invoice_date',    'column_name_alt' => 'Invoice Date'],
                    ['number' => 1, 'column_name' => 'customer_code',   'column_name_alt' => 'Customer Code'],
                    ['number' => 2, 'column_name' => 'salesman_code',   'column_name_alt' => 'Salesman Code'],
                    ['number' => 3, 'column_name' => 'invoice_number',  'column_name_alt' => 'Document No./Invoice Number/CM Number'],
                    ['number' => 4, 'column_name' => 'warehouse_code',  'column_name_alt' => 'Warehouse Code'],
                    ['number' => 5, 'column_name' => 'sku_code',        'column_name_alt' => 'BEVI Sku Code'],
                    ['number' => 6, 'column_name' => 'quantity',        'column_name_alt' => 'Quantity'],
                    ['number' => 7, 'column_name' => 'uom',             'column_name_alt' => 'Unit of Measure Code'],
                    ['number' => 8, 'column_name' => 'price_inc_vat',   'column_name_alt' => 'Unit Price Incl. VAT'],
                    ['number' => 9, 'column_name' => 'amount',          'column_name_alt' => 'Amount'],
                    ['number' => 10, 'column_name' => 'amount_inc_vat', 'column_name_alt' => 'Amount Including VAT'],
                    ['number' => 11, 'column_name' => 'line_discount',  'column_name_alt' => 'Line Discount %'],
                ],
            ],
            'inventory' => [
                'title' => 'Inventory Upload',
                'fields' => [
                    ['number' => 0, 'column_name' => 'sku_code',       'column_name_alt' => 'SKU CODE'],
                    ['number' => 1, 'column_name' => 'description',    'column_name_alt' => 'DESCRIPTION'],
                    ['number' => 2, 'column_name' => 'location_code',  'column_name_alt' => 'LOCATION'],
                    ['number' => 3, 'column_name' => 'uom',            'column_name_alt' => 'UOM'],
                    ['number' => 4, 'column_name' => 'quantity',       'column_name_alt' => 'QUANTITY'],
                    ['number' => 5, 'column_name' => 'expiry_date',    'column_name_alt' => 'EXPIRY DATE'],
                ],
            ],
            'customer' => [
                'title' => 'Customer Upload',
                'fields' => [
                    ['number' => 0,  'column_name' => 'code',          'column_name_alt' => 'Code'],
                    ['number' => 1,  'column_name' => 'name',          'column_name_alt' => 'Name'],
                    ['number' => 2,  'column_name' => 'address',       'column_name_alt' => 'Address'],
                    ['number' => 3,  'column_name' => 'salesman_code', 'column_name_alt' => 'Salesman Code'],
                    ['number' => 4,  'column_name' => 'channel_code',  'column_name_alt' => 'Channel Code'],
                    ['number' => 5,  'column_name' => 'channel_name',  'column_name_alt' => 'Channel Name'],
                    ['number' => 6,  'column_name' => 'province',      'column_name_alt' => 'Province'],
                    ['number' => 7,  'column_name' => 'city',          'column_name_alt' => 'City/Town'],
                    ['number' => 8,  'column_name' => 'barangay',      'column_name_alt' => 'Barangay'],
                    ['number' => 9,  'column_name' => 'street',        'column_name_alt' => 'Street'],
                    ['number' => 10, 'column_name' => 'postal_code',   'column_name_alt' => 'Postal Code'],
                ],
            ],
            'salesman' => [
                'title' => 'Salesman Upload',
                'fields' => [
                    ['number' => 0, 'column_name' => 'code',           'column_name_alt' => 'Code'],
                    ['number' => 1, 'column_name' => 'name',           'column_name_alt' => 'Name'],
                    ['number' => 2, 'column_name' => 'type',           'column_name_alt' => 'Type of Salesman'],
                    ['number' => 3, 'column_name' => 'district_code',  'column_name_alt' => 'District Code'],
                ],
            ],
            'location' => [
                'title' => 'Location Upload',
                'fields' => [
                    ['number' => 0, 'column_name' => 'code', 'column_name_alt' => 'Code'],
                    ['number' => 1, 'column_name' => 'name', 'column_name_alt' => 'Name'],
                ],
            ],
        ];

        foreach ($templates as $type => $config) {
            $existing = UploadTemplate::where('title', $config['title'])->first();

            if ($existing) {
                continue;
            }

            $template = UploadTemplate::create(['title' => $config['title']]);

            foreach ($config['fields'] as $field) {
                UploadTemplateField::create([
                    'upload_template_id' => $template->id,
                    'number'             => $field['number'],
                    'column_name'        => $field['column_name'],
                    'column_name_alt'    => $field['column_name_alt'],
                ]);
            }
        }
    }
}
