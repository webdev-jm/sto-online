<?php

namespace App\Http\Livewire\PurchaseOrder;

use Livewire\Component;
use Livewire\WithFileUploads;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\AccountProductReference;
use App\Models\SMSProduct;
use App\Models\AccountUploadTemplate;
use App\Models\UploadTemplate;

use Spatie\SimpleExcel\SimpleExcelReader;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class Upload extends Component
{
    use WithFileUploads;

    public $account_branch;
    public $files;
    public $po_data;
    public $po_errors = array();
    public $success_msg = array();

    public function uploadData() {
        if(!empty($this->po_data)) {
            foreach($this->po_data as $po_number => $data) {
                $err = $this->validateData($po_number, $data);
                if(empty($err)) {
                    $purchase_order = new PurchaseOrder([
                        'sms_account_id' => $this->account_branch->account->sms_account_id,
                        'account_branch_id' => $this->account_branch->id,
                        'po_number' => $po_number,
                        'order_date' => $data['headers']['order_date'],
                        'ship_date' => $data['headers']['ship_date'],
                        'shipping_instruction' => $data['headers']['shipping_instruction'],
                        'ship_to_name' => $data['headers']['ship_to_name'],
                        'ship_to_address' => $data['headers']['ship_to_address'],
                    ]);
                    $purchase_order->save();
    
                    $total_quantity = 0;
                    $total_gross_amount = 0;
                    $total_net_amount = 0;
                    // products
                    foreach($data['products'] as $product_data) {
                        // lookup product by sku code
                        $product = SMSPRoduct::where('stock_code', $product_data['sku_code'])
                            ->orWhere('stock_code', $product_data['sku_code_other'])
                            ->first();
                        if(empty($product)) {
                            // check account product reference
                            $product_reference = AccountProductReference::where('account_id', $this->account_branch->account->sms_account_id)
                                ->where(function($query) use($product_data) {
                                    $query->where('account_reference', $product_data['sku_code'])
                                        ->orWhere('account_reference', $product_data['sku_code_other'])
                                        ->orWhere(DB::raw('CAST(account_reference AS UNSIGNED)'), $product_data['sku_code'])
                                        ->orWhere(DB::raw('CAST(account_reference AS UNSIGNED)'), $product_data['sku_code_other']);
                                })
                                ->first();
                            if(!empty($product_reference)) {
                                $product = SMSProduct::find($product_reference->product_id);
                            }
                        }
    
                        $purchase_order_detail = new PurchaseOrderDetail([
                            'purchase_order_id' => $purchase_order->id,
                            'product_id' => $product->id ?? NULL,
                            'sku_code' => $product_data['sku_code'],
                            'sku_code_other' => $product_data['sku_code_other'],
                            'product_name' => $product_data['product_name'],
                            'quantity' => $product_data['quantity'],
                            'unit_of_measure' => $product_data['unit_of_measure'],
                            'discount_amount' => empty($product_data['discount_amount']) ? 0 : $product_data['discount_amount'],
                            'gross_amount' => empty($product_data['gross_amount']) ? 0 : $product_data['gross_amount'],
                            'net_amount' => empty($product_data['net_amount']) ? 0 : $product_data['net_amount'],
                            'net_amount_per_uom' => empty($product_data['net_amount_per_uom']) ? 0 : $product_data['net_amount_per_uom'],
                        ]);
                        $purchase_order_detail->save();
    
                        $total_quantity += $product_data['quantity'];
                        $total_gross_amount += $product_data['gross_amount'];
                        $total_net_amount += $product_data['net_amount'];
                    }
    
                    $purchase_order->update([
                        'total_quantity' => $total_quantity,
                        'total_sales' => $total_gross_amount,
                        'grand_total' => $total_net_amount,
                    ]);

                    unset($this->po_data[$po_number]);
                    Session::put('po_upload_data', $this->po_data);

                    $this->success_msg[] = 'PO '.$purchase_order->po_number.' has been uploaded.';
                } else {
                    $this->po_errors[$po_number] = $err;
                }
            }


        }
    }

    public function checkUploads()
    {
        $this->validate([
            'files' => [
                'required',
            ]
        ]);

        $this->reset('success_msg');

        // $po_data = array();
        // foreach ($this->files as $file) {
        //     $path1 = $file->storeAs('purchase-order-uploads/account_branch_'.$this->account_branch->id, $file->getClientOriginalName());
        //     $path = storage_path('app').'/'.$path1;
        //     $xml = simplexml_load_file($path);
        //     $po_data[] = $xml;
        // }

        // dd($po_data);

        $upload_template = UploadTemplate::where('title', 'PO UPLOAD')->first();
        $account_template = AccountUploadTemplate::where('upload_template_id', $upload_template->id)
            ->where('account_id', $this->account_branch->account_id)
            ->first();
        
        $account_template_fields = array();
        if(!empty($account_template)) {
            $account_template_fields = $account_template->fields->mapWithKeys(function($field) {
                return [
                    $field->upload_template_field_id => [
                        'number' => $field->number,
                        'file_column_name' => $field->file_column_name,
                        'file_column_number' => $field->file_column_number,
                    ],
                ];
            });
        }
            

        $po_data = array();
        foreach ($this->files as $file) {
            $path1 = $file->storeAs('purchase-order-uploads/account_branch_'.$this->account_branch->id, $file->getClientOriginalName());
            $path = storage_path('app').'/'.$path1;

            // Get the file extension
            $extension = $file->getClientOriginalExtension();
            if(in_array($extension, ['xlsx', 'csv', 'bin', 'xls'])) {
                // convert xls to xlsx
                $spreadsheet = IOFactory::load($path);
                $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
                $xlsxPath = storage_path('app').'/purchase-order-uploads/account_branch_'.$this->account_branch->id.'/converted-file-'.time().'.xlsx';
                $writer->save($xlsxPath);

                if($account_template->type == 'name') {
                    $rows = SimpleExcelReader::create($xlsxPath)
                        ->getRows();
                } else if($account_template->type == 'number') {
                    $rows = SimpleExcelReader::create($xlsxPath)
                        ->skip($account_template->start_row - 1)
                        ->noHeaderRow()
                        ->getRows();
                }
                
                if(!empty($rows)) {

                    // Setup for Purgold
                    if($this->account_branch->account->account_code == '1200081') {
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
                        
                    // } else if($this->account_branch->account->account_code == '1200015') { // Setup for Phil Seven

                    } else {
                        $rows->each(function($row) use(&$po_data, $upload_template, $account_template_fields, $account_template) {
                            $this->processRow($row, $po_data, $upload_template, $account_template_fields, $account_template->type);
                        });
                    }

                }

            } else if($extension == 'xml') {
                

                // FOR GOLDEN DEW
                if($this->account_branch->account->account_code == '1200116') {
                    $xml = simplexml_load_file($path);
                    // dd($xml->children());
                    foreach($xml->children() as $child_data) {
                        
                        $header = $child_data->header;
                        $po_data[(string)$header->DocumentNumber] = [
                            'headers' => [
                                'vendor' => '',
                                'order_date' => date('Y-m-d', strtotime((string)$header->DateEntry)),
                                'ship_date' => date('Y-m-d', strtotime((string)$header->DateReceipt)),
                                'shipping_instruction' => (string)$header->DeliveryMessage,
                                'ship_to_name' => (string)$header->SiteCode,
                                'ship_to_address' => (string)$header->SiteName .' '. (string)$header->SiteAddress,
                                'city' => '',
                                'status' => '',
                                'total_quantity' => 0,
                                'total_amount' => (double)$child_data->footer->TotalAmount,
                                'total_net_amount' => 0,
                                'po_value' => 0,
                            ]
                        ];

                        $body = $child_data->body;
                        
                        $products = array();
                        // check if multiple products
                        if(is_array($body->article)) {
                            foreach($body->article as $product) {
                                $products[] = [
                                    'product_id' => NULL,
                                    'sku_code' => (string)$product->SKUMatCode,
                                    'sku_code_other' => (string)$product->UPC,
                                    'product_name' =>(string)$product->ArticleDescription->description,
                                    'quantity' => (int)$product->BuyQty,
                                    'unit_of_measure' => (string)$product->BuyUM,
                                    'discount' => '',
                                    'discount_amount' => '',
                                    'gross_amount' => $this->convertStringValue($product->BuyCost),
                                    'net_amount' => $this->convertStringValue($product->BuyCost),
                                    'net_amount_per_uom' => $this->convertStringValue($product->BuyCost),
                                    'po_value' => ''
                                ];
                            }
                        } else {
                            $product = $body->article;
                            $products[] = [
                                'product_id' => NULL,
                                'sku_code' => (string)$product->SKUMatCode,
                                'sku_code_other' => (string)$product->UPC,
                                'product_name' => (string)$product->ArticleDescription->description,
                                'quantity' => (int)$product->BuyQty,
                                'unit_of_measure' => (string)$product->BuyUM,
                                'discount' => 0,
                                'discount_amount' => 0,
                                'gross_amount' => $this->convertStringValue($product->BuyCost),
                                'net_amount' => $this->convertStringValue($product->BuyCost),
                                'net_amount_per_uom' => $this->convertStringValue($product->BuyCost),
                                'po_value' => 0
                            ];
                        }

                        $po_data[(string)$header->DocumentNumber]['products'] = $products;
                    }
                } else {
                    $xml = simplexml_load_file($path);
                    foreach($xml->children() as $child) {
                        $row = [];
                        foreach ($upload_template->fields as $field) {
                            $file_column_name = $account_template_fields[$field->id]['file_column_name'];
                            $row[$file_column_name] = (string)$child->{$file_column_name};
                        }
                        $this->processRow($row, $po_data, $upload_template, $account_template_fields, 'name');
                    }
                }

            }
            
        }

        $this->po_data = $po_data;
        
        Session::put('po_upload_data', $this->po_data);
    }

    private function convertStringValue($value) {
        $str_val = (string)$value;
        return (double) str_replace(',', '', $str_val);
        
    }

    function processRow($row, &$po_data, $upload_template, $account_template_fields, $type, $custom = false) {
        $po_number = ''; // Assign or extract $po_number as required
    
        foreach ($upload_template->fields as $field) {
            $column_name = $field->column_name;
            if($custom) {
                $val = $row[$account_template_fields[$field->id]['col_num'] - 1] ?? '';
                if(!empty($account_template_fields[$field->id]['field'])) {
                    if(preg_match('/:'.$account_template_fields[$field->id]['field'].':([^:]+)/', $val, $matches)) {
                        $val = trim($matches[1]);
                    }
                }
                ${$column_name} = $val ?? NULL;
            } else {
                if($type == 'name') {
                    $file_column_name = $account_template_fields[$field->id]['file_column_name'];
                } else if($type == 'number') {
                    $file_column_name = $account_template_fields[$field->id]['file_column_number'] - 1;
                }
                ${$column_name} = $row[$file_column_name] ?? NULL;
            }

        }

        if (is_object($order_date)) {
            $order_date = $order_date->format('Y-m-d'); // or 'Y-m-d H:i:s' if time is also needed
        }
    
        if (is_object($ship_date)) {
            // Convert DateTimeImmutable to string format
            $ship_date = $ship_date->format('Y-m-d'); // 'Y-m-d H:i:s' if time is needed
        }
    
        $po_data[$po_number]['headers'] = [
            'vendor' => '',
            'order_date' => date('Y-m-d', strtotime($order_date)),
            'ship_date' => date('Y-m-d', strtotime($ship_date)),
            'shipping_instruction' => $shipping_instruction,
            'ship_to_name' => $ship_to_name,
            'ship_to_address' => $ship_to_address,
            'city' => '',
            'status' => '',
            'total_quantity' => $quantity,
            'total_amount' => $gross_amount,
            'total_net_amount' => $net_amount,
            'po_value' => $net_amount,
        ];
        $po_data[$po_number]['products'][] = [
            'product_id' => NULL,
            'sku_code' => $sku_code,
            'sku_code_other' => $sku_code_other,
            'product_name' => $product_name,
            'quantity' => $quantity,
            'unit_of_measure' => $unit_of_measure,
            'discount' => 0,
            'discount_amount' => $discount_amount ?? 0,
            'gross_amount' => $gross_amount ?? 0,
            'net_amount' => $net_amount ?? 0,
            'net_amount_per_uom' => $net_amount_per_uom ?? 0,
            'total_gross_amount' => $gross_amount ?? 0
        ];
    }

    public function validateData($po_number, $data) {
        $err = array();
        // PO NUMBER
        if(!empty($po_number)) {
            $check = PurchaseOrder::where('po_number', $po_number)->first();
            if(!empty($check)) {
                $err['po_number'] = 'PO Number already exists.';
            }
        } else {
            $err['po_number'] = 'PO Number is required.';
        }

        return $err;
    }

    public function mount($account_branch) {
        $this->account_branch = $account_branch;

        if(empty($this->po_data) && !empty(Session::get('po_upload_data'))) {
            $this->po_data = Session::get('po_upload_data');
        }
    }

    public function render()
    {
        return view('livewire.purchase-order.upload');
    }
}
