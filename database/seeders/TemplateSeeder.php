<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UploadTemplate;
use App\Models\UploadTemplateField;

class TemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.a
     *
     * @return void
     */
    public function run()
    {
        $sales_upload_template = [
            [
                'column' => 'invoice_date',
                'column_alt' => ''
            ],
            [
                'column' => 'customer_code',
                'column_alt' => ''
            ],
            [
                'column' => 'salesman_code',
                'column_alt' => ''
            ],
            [
                'column' => 'invoice_number',
                'column_alt' => ''
            ],
            [
                'column' => 'warehouse_code',
                'column_alt' => ''
            ],
            [
                'column' => 'sku_code',
                'column_alt' => ''
            ],
            [
                'column' => 'quantity',
                'column_alt' => ''
            ],
            [
                'column' => 'uom',
                'column_alt' => ''
            ],
            [
                'column' => 'unit_price_inc_vat',
                'column_alt' => ''
            ],
            [
                'column' => 'amount',
                'column_alt' => ''
            ],
            [
                'column' => 'amount_inc_vat',
                'column_alt' => ''
            ],
            [
                'column' => 'line_discount',
                'column_alt' => ''
            ],
            [
                'column' => 'customer_name',
                'column_alt' => ''
            ],
            [
                'column' => 'customer_address',
                'column_alt' => ''
            ],
            [
                'column' => 'channel_code',
                'column_alt' => ''
            ],
            [
                'column' => 'channel_name',
                'column_alt' => ''
            ],
            [
                'column' => 'province',
                'column_alt' => ''
            ],
            [
                'column' => 'city',
                'column_alt' => ''
            ],
            [
                'column' => 'barangay',
                'column_alt' => ''
            ],
            [
                'column' => 'street',
                'column_alt' => ''
            ],
            [
                'column' => 'postal_code',
                'column_alt' => ''
            ],
        ];

        $po_template_arr = [
            [
                'column' => 'po_number',
                'column_alt' => ''
            ],
            [
                'column' => 'order_date',
                'column_alt' => ''
            ],
            [
                'column' => 'ship_date',
                'column_alt' => ''
            ],
            [
                'column' => 'shipping_instruction',
                'column_alt' => ''
            ],
            [
                'column' => 'ship_to_name',
                'column_alt' => ''
            ],
            [
                'column' => 'ship_to_address',
                'column_alt' => ''
            ],
            [
                'column' => 'sku_code',
                'column_alt' => ''
            ],
            [
                'column' => 'sku_code_other',
                'column_alt' => ''
            ],
            [
                'column' => 'product_name',
                'column_alt' => ''
            ],
            [
                'column' => 'unit_of_measure',
                'column_alt' => ''
            ],
            [
                'column' => 'quantity',
                'column_alt' => ''
            ],
            [
                'column' => 'discount_amount',
                'column_alt' => ''
            ],
            [
                'column' => 'gross_amount',
                'column_alt' => ''
            ],
            [
                'column' => 'net_amount',
                'column_alt' => ''
            ],
            [
                'column' => 'net_amount_per_uom',
                'column_alt' => ''
            ],
        ];

        $stock_on_hand_template = [
            [
                'column' => 'customer_code',
                'column_alt' => ''
            ],
            [
                'column' => 'customer_name',
                'column_alt' => ''
            ],
            [
                'column' => 'sku_code',
                'column_alt' => ''
            ],
            [
                'column' => 'sku_code_other',
                'column_alt' => ''
            ],
            [
                'column' => 'product_description',
                'column_alt' => ''
            ],
            [
                'column' => 'inventory',
                'column_alt' => ''
            ],
        ];

        $stock_transfer_template = [
            [
                'column' => 'customer_code',
                'column_alt' => ''
            ],
            [
                'column' => 'customer_name',
                'column_alt' => ''
            ],
            [
                'column' => 'sku_code',
                'column_alt' => ''
            ],
            [
                'column' => 'sku_code_other',
                'column_alt' => ''
            ],
            [
                'column' => 'product_description',
                'column_alt' => ''
            ],
            [
                'column' => 'transfer_ty',
                'column_alt' => ''
            ],
            [
                'column' => 'transfer_ly',
                'column_alt' => ''
            ],
        ];

        $rtv_template = [
            [
                'column' => 'rtv_number',
                'column_alt' => ''
            ],
            [
                'column' => 'document_number',
                'column_alt' => ''
            ],
            [
                'column' => 'ship_date',
                'column_alt' => ''
            ],
            [
                'column' => 'entry_date',
                'column_alt' => ''
            ],
            [
                'column' => 'reason',
                'column_alt' => ''
            ],
            [
                'column' => 'remarks',
                'column_alt' => ''
            ],
            [
                'column' => 'ship_to_name',
                'column_alt' => ''
            ],
            [
                'column' => 'ship_to_address',
                'column_alt' => ''
            ],
            [
                'column' => 'sku_code',
                'column_alt' => ''
            ],
            [
                'column' => 'other_sku_code',
                'column_alt' => ''
            ],
            [
                'column' => 'description',
                'column_alt' => ''
            ],
            [
                'column' => 'uom',
                'column_alt' => ''
            ],
            [
                'column' => 'quantity',
                'column_alt' => ''
            ],
            [
                'column' => 'cost',
                'column_alt' => ''
            ],
        ];

        $templates_arr = [
            'PO UPLOAD' => $po_template_arr,
            'STOCK ON HAND UPLOAD' => $stock_on_hand_template,
            'STOCK TRANSFER UPLOAD' => $stock_transfer_template,
            'RTV TEMPLATE' => $rtv_template,
        ];

        foreach($templates_arr as $title => $data) {
            $template = new UploadTemplate([
                'title' => $title
            ]);
            $template->save();

            $num = 0;
            foreach($data as $val) {
                $num++;
                $field = new UploadTemplateField([
                    'upload_template_id' => $template->id,
                    'number' => $num,
                    'column_name' => $val['column'],
                    'column_name_alt' => $val['column_alt'],
                ]);
                $field->save();
            }
        }
    }
}
