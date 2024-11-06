<?php 

namespace App\Http\Traits;

use Illuminate\Support\Facades\Session;

trait AccountCustomerUplaod {
    
    public function po_upload($account_code, $rows, $upload_template, $account_template_fields, $account_template) {
        

        if($account_code == '1200081') { // puregold

        }

        return $po_data;
    }

    // for puregold
    public function puregold($rows, $upload_template, $account_template_fields, $account_template) {
        $po_data = array();
        $rows->each(function($row) use(&$po_data, $upload_template, $account_template_fields, $account_template) {
            if(empty($row[0])) { // Luzon
                $this->processRow($row, $po_data, $upload_template, $account_template_fields, $account_template->type);
            } else { // Vismin
                $custom_template = [
                    1 => [
                        'col_num' => 2,
                        'field' => '',
                    ],
                    2 => [
                        'col_num' => 1,
                        'field' => '',
                    ],
                    3 => [
                        'col_num' => 11,
                        'field' => '',
                    ],
                    4 => [
                        'col_num' => 4,
                        'field' => '',
                    ],
                    5 => [
                        'col_num' => 17,
                        'field' => 'dlvLocation',
                    ],
                    6 => [
                        'col_num' => 17,
                        'field' => 'dlvAddress',
                    ],
                    7 => [
                        'col_num' => 20,
                        'field' => 'sku',
                    ],
                    8 => [
                        'col_num' => 19,
                        'field' => '',
                    ],
                    9 => [
                        'col_num' => 20,
                        'field' => 'description',
                    ],
                    10 => [
                        'col_num' => 20,
                        'field' => 'buyUM',
                    ],
                    11 => [
                        'col_num' => 21,
                        'field' => '',
                    ],
                    12 => [
                        'col_num' => 26,
                        'field' => '',
                    ],
                    13 => [
                        'col_num' => 20,
                        'field' => 'buyCost',
                    ],
                    14 => [
                        'col_num' => 26,
                        'field' => '',
                    ],
                    15 => [
                        'col_num' => 26,
                        'field' => '',
                    ],
                ];

                $custom_vismin_template = [];
                foreach($account_template_fields as $field_id => $val) {
                    if(!empty($val['number'])) {
                        $custom_vismin_template[$field_id] = $custom_template[$val['number']];
                    }
                }

                $this->processRow($row, $po_data, $upload_template, $custom_vismin_template, 'number', true);
            }
        });

        return $po_data;
    }

}