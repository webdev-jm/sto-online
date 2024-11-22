<?php

namespace App\Http\Livewire\ReturnToVendor;

use Livewire\Component;
use Livewire\WithFileUploads;

use App\Models\UploadTemplate;
use App\Models\AccountUploadTemplate;
use App\Models\ReturnToVendor;
use App\Models\ReturnToVendorProduct;

use Spatie\SimpleExcel\SimpleExcelReader;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class Upload extends Component
{
    use WithFileUploads;

    public $account_branch;
    public $upload_files;
    public $rtv_data;
    public $rtv_errors = array();
    public $success_msg = array();

    public function uploadRTV() {
        $this->validate([
            'rtv_data' => [
                'required',
            ]
        ]);

        foreach($this->rtv_data as $rtv_number => $data) {

            $err = $this->validateData($rtv_number);
            if(empty($err)) {
                $header = $data['headers'];

                $rtv = new ReturnToVendor([
                    'account_id' => $this->account_branch->account_id,
                    'account_branch_id' => $this->account_branch->id,
                    'rtv_number' => $rtv_number,
                    'document_number' => $header['document_number'],
                    'ship_date' => $header['ship_date'],
                    'entry_date' => $header['entry_date'],
                    'reason' => $header['reason'],
                    'ship_to_name' => $header['ship_to_name'],
                    'ship_to_address' => $header['ship_to_address'],
                ]);
                $rtv->save();

                foreach($data['products'] as $product) {
                    $rtv_product = new ReturnToVendorProduct([
                        'return_to_vendor_id' => $rtv->id,
                        'sku_code' => $product['sku_code'],
                        'other_sku_code' => $product['sku_code_other'],
                        'description' => $product['description'],
                        'uom' => $product['uom'],
                        'quantity' => $product['quantity'],
                        'cost' => $product['cost']
                    ]);
                    $rtv_product->save();
                }

                unset($this->rtv_data[$rtv_number]);
                $this->success_msg[] = 'RTV '.$rtv_number.' has been uploaded.';
            } else {
                $this->rtv_errors[$rtv_number] = $err;
            }
            
        }
    }

    public function checkFiles() {
        $this->validate([
            'upload_files' => [
                'required'
            ]
        ]);

        $upload_template = UploadTemplate::where('title', 'RTV UPLOAD')->first();
        $account_template = AccountUploadTemplate::where('upload_template_id', $upload_template->id)
            ->where('account_id', $this->account_branch->account_id)
            ->first();

        $account_template_fields = array();
        if(!empty($account_template)) {
            $account_template_fields = $account_template->fields->mapWithKeys(function($field) {
                return [
                    $field->upload_template_field_id => [
                        'number' => $field->number,
                        'file_column_number' => $field->file_column_name,
                        'file_column_number' => $field->file_column_number,
                    ]
                ];
            });
        }

        $rtv_data = array();
        foreach($this->upload_files as $file) {
            $path1 = $file->storeAs('rtv/account_branch_'.$this->account_branch->id, $file->getClientOriginalName());
            $path = storage_path('app').'/'.$path1;

            // get the file extension
            $extension = $file->getClientOriginalExtension();

            if(in_array($extension, ['xlsx', 'csv', 'bin', 'xls', 'tmp', 'txt'])) {
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

                $rows->each(function($row) use(&$rtv_data, $upload_template, $account_template_fields, $account_template) {
                    // Setup for Purgold
                    if($this->account_branch->account->account_code == '1200081') {
                        $custom_template = [
                            1 => [
                                'col_num' => 5,
                                'field' => '',
                            ],
                            2 => [
                                'col_num' => 26,
                                'field' => 'refNum',
                            ],
                            3 => [
                                'col_num' => 2,
                                'field' => '',
                            ],
                            4 => [
                                'col_num' => 2,
                                'field' => '',
                            ],
                            5 => [
                                'col_num' => 26,
                                'field' => 'reasForRet',
                            ],
                            6 => [
                                'col_num' => 34,
                                'field' => 'reasonCode',
                            ],
                            7 => [
                                'col_num' => 21,
                                'field' => 'name',
                            ],
                            8 => [
                                'col_num' => 21,
                                'field' => 'address',
                            ],
                            9 => [
                                'col_num' => 28,
                                'field' => '',
                            ],
                            10 => [
                                'col_num' => 29,
                                'field' => '',
                            ],
                            11 => [
                                'col_num' => 31,
                                'field' => '',
                            ],
                            12 => [
                                'col_num' => 34,
                                'field' => 'UOM',
                            ],
                            13 => [
                                'col_num' => 32,
                                'field' => '',
                            ],
                            14 => [
                                'col_num' => 34,
                                'field' => 'extendedCost',
                            ],
                        ];
    
                        $custom_puregold_template = [];
                        foreach($account_template_fields as $field_id => $val) {
                            if(!empty($val['number'])) {
                                $custom_puregold_template[$field_id] = $custom_template[$val['number']];
                            }
                        }

                        $this->processRow($row, $rtv_data, $upload_template, $custom_puregold_template, 'number', true);
    
                    } else {
                        $this->processRow($row, $rtv_data, $upload_template, $account_template_fields, $account_template->type);
                    }
                });

            } else if($extension == 'xml') {

                if($this->account_branch->account->account_code == '1200116') {
                    $xml = simplexml_load_file($path);

                    $num = 0;
                    foreach($xml->children() as $child_data) {
                        $num++;

                        $header = $child_data->documentHeader;

                        $rtv_number = (string)$header->documentNumber;

                        $rtv_data[$rtv_number]['headers'] = [
                            'document_number' => $rtv_number,
                            'entry_date' => date('Y-m-d', strtotime((string)$header->entryDate)),
                            'ship_date' => date('Y-m-d', strtotime((string)$header->RODate)),
                            'reason' => (string)$child_data->documentContents->importantRemarks,
                            'ship_to_name' => (string)$header->siteCodeAndName,
                            'ship_to_address' => (string)$header->siteAddress,
                        ];

                        $body = $child_data->documentContents;

                        $products = array();
                        foreach($body->article as $article) {
                            $qty = str_replace(',', '', $article->qty);
                            $cost = str_replace(',', '', $article->totalAmount);
                            $products[] = [
                                'product_id' => NULL,
                                'sku_code' => (string)$article->articleCode,
                                'sku_code_other' => (string)$article->barcode,
                                'description' => (string)$article->articleDescription,
                                'uom' => (string)$article->uom,
                                'quantity' => (int)$qty,
                                'cost' => (double)$cost,
                                'remarks' => '',
                            ];
                        }

                        $rtv_data[$rtv_number]['products'] = $products;
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

        $this->rtv_data = $rtv_data;
    }

    private function processRow($row, &$rtv_data, $upload_template, $account_template_fields, $type, $custom = false) {
        $rtv_number = ''; // Assign or extract $po_number as required
    
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

        if(is_object($entry_date)) {
            $entry_date = $entry_date->format('Y-m-d'); // or 'Y-m-d H:i:s' if time is also needed
        }
    
        if(is_object($ship_date)) {
            // Convert DateTimeImmutable to string format
            $ship_date = $ship_date->format('Y-m-d'); // 'Y-m-d H:i:s' if time is needed
        }
    
        $rtv_data[$rtv_number]['headers'] = [
            'document_number' => $document_number,
            'entry_date' => date('Y-m-d', strtotime($entry_date)),
            'ship_date' => date('Y-m-d', strtotime($ship_date)),
            'reason' => $reason,
            'ship_to_name' => $ship_to_name,
            'ship_to_address' => $ship_to_address,
        ];
        $rtv_data[$rtv_number]['products'][] = [
            'product_id' => NULL,
            'sku_code' => $sku_code,
            'sku_code_other' => $other_sku_code,
            'description' => $description,
            'uom' => $uom,
            'quantity' => $quantity,
            'cost' => $cost,
            'remarks' => $remarks,
        ];
    }

    private function validateData($rtv_number) {
        $err = array();
        if(!empty($rtv_number)) {
            $check = ReturnToVendor::where('rtv_number', $rtv_number)
                ->first();
            if(!empty($check)) {
                $err['rtv_number'] = 'RTV number already exists.';
            }
        } else {
            $err['rtv_number'] = 'RTV number is required';
        }

        return $err;
    }

    public function mount($account_branch) {
        $this->account_branch = $account_branch;
    }

    public function render()
    {
        return view('livewire.return-to-vendor.upload');
    }
}
