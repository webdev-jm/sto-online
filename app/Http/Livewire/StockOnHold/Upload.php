<?php

namespace App\Http\Livewire\StockOnHold;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

use App\Models\UploadTemplate;
use App\Models\AccountUploadTemplate;
use App\Models\StockOnHand;
use App\Models\StockOnHandProduct;
use App\Models\Customer;
use App\Models\SMSProduct;
use App\Models\AccountProductReference;

use Spatie\SimpleExcel\SimpleExcelReader;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class Upload extends Component
{
    use WithFileUploads;
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $account_branch;
    public $upload_file;
    public $data;
    public $perPage = 20;
    public $year, $month;
    public $success_msg;

    public function save() {
        $this->validate([
            'year' => [
                'required'
            ],
            'month' => [
                'required'
            ],
        ]);

        if(!empty($this->data)) {

            $account_product_references = AccountProductReference::where('account_id', $this->account_branch->account->sms_account_id)->get();

            foreach($this->data as $val) {
                $customer = $this->checkCustomer($val['customer_code'], $val['customer_name']);

                // check if already exists
                $stock_on_hand = StockOnHand::where('account_branch_id', $this->account_branch->id)
                    ->where('customer_id', $customer->id)
                    ->where('year', $this->year)
                    ->where('month', $this->month)
                    ->first();
                if(empty($stock_on_hand)) {
                    $stock_on_hand = new StockOnHand([
                        'account_branch_id' => $this->account_branch->id,
                        'customer_id' => $customer->id,
                        'year' => $this->year,
                        'month' => $this->month,
                        'total_inventory' => 0
                    ]);
                    $stock_on_hand->save();
                }

                // check product
                $product = SMSProduct::where('stock_code', $val['sku_code'])
                    ->orWhere('stock_code', $val['sku_code_other'])
                    ->first();
                if(empty($product)) {
                    $account_product = $account_product_references->filter(function ($reference) use ($val) {
                            return $reference->account_reference == $val['sku_code'] ||
                                $reference->account_reference == $val['sku_code_other'] ||
                                intval($reference->account_reference) == intval($val['sku_code']) ||
                                intval($reference->account_reference) == intval($val['sku_code_other']);
                        })->first();

                    if(!empty($account_product)) {
                        $product = SMSProduct::where('id', $account_product->product_id)
                            ->first();
                    }
                }

                $stock_on_hand_product = new StockOnHandProduct([
                    'stock_on_hand_id' => $stock_on_hand->id,
                    'product_id' => $product->id ?? NULL,
                    'sku_code' => $val['sku_code'],
                    'sku_code_other' => $val['sku_code_other'],
                    'inventory' => $val['inventory']
                ]);
                $stock_on_hand_product->save();

                $stock_on_hand->update([
                    'total_inventory' => $stock_on_hand->total_inventory + $val['inventory']
                ]);
            }

            $this->reset('data');
            $this->success_msg = "Stock on hand uploaded successfully.";
        }
    }

    public function checkCustomer($customer_code, $customer_name) {
        // check if already exists
        $customer = Customer::where('code', $customer_code)
            ->where('account_branch_id', $this->account_branch->id)
            ->first();
        if(empty($customer)) {
            $customer = new Customer([
                'account_id' => $this->account_branch->account_id,
                'account_branch_id' => $this->account_branch->id,
                'code' => $customer_code,
                'name' => $customer_name,
                'address' => '',
            ]);
            $customer->save();
        }

        return $customer;
    }

    public function checkFile() {
        $this->validate([
            'upload_file' => 'required'
        ]);

        $this->reset('data');

        $upload_template = UploadTemplate::where('title', 'STOCK ON HAND UPLOAD')->first();
        $account_template = AccountUploadTemplate::where('upload_template_id', $upload_template->id)
            ->where('account_id', $this->account_branch->account_id)
            ->first();

        $account_template_fields = $account_template->fields->mapWithKeys(function($field) {
            return [
                $field->upload_template_field_id => [
                    'file_column_name' => $field->file_column_name,
                    'file_column_number' => $field->file_column_number,
                ],
            ];
        });

        $path1 = $this->upload_file->storeAs('stock-on-hand-uploads/account_branch_'.$this->account_branch->id, $this->upload_file->getClientOriginalName());
        $path = storage_path('app').'/'.$path1;

        // Get the file extension
        $extension = $this->upload_file->getClientOriginalExtension();

        $data = array();
        if(in_array($extension, ['xlsx', 'csv'])) {
            if($account_template->type == 'name') {
                $rows = $rows = SimpleExcelReader::create($path)
                    ->getRows();
            } else if($account_template->type == 'number') {
                $rows = $rows = SimpleExcelReader::create($path)
                    ->skip($account_template->start_row - 1)
                    ->noHeaderRow()
                    ->getRows();
            }

            $rows->each(function($row) use(&$data, $upload_template, $account_template_fields, $account_template) {
                $this->processRow($row, $data, $upload_template, $account_template_fields, $account_template->type);
            });
        } else if($extension == 'xml') {
            $xml = simplexml_load_file($path);
            foreach($xml->children() as $child) {
                $row = [];
                foreach ($upload_template->fields as $field) {
                    $file_column_name = $account_template_fields[$field->id]['file_column_name'];
                    $row[$file_column_name] = (string)$child->{$file_column_name};
                }
            }
        }

        usort($data, function($a, $b) {
            return $a['customer_code'] <=> $b['customer_code'];
        });

        $this->data = $data;
    }

    function processRow($row, &$data, $upload_template, $account_template_fields, $type) {
        $customer_code = ''; // Assign or extract $customer_code as required
    
        foreach ($upload_template->fields as $field) {
            $column_name = $field->column_name;
            if($type == 'name') {
                $file_column_name = $account_template_fields[$field->id]['file_column_name'];
            } else if($type == 'number') {
                $file_column_name = $account_template_fields[$field->id]['file_column_number'] - 1;
            }

            ${$column_name} = $row[$file_column_name];
        }
    
        if(!empty($customer_code) && $inventory > 0) {
            $data[] = [
                'customer_code' => trim($customer_code),
                'customer_name' => trim($customer_name),
                'sku_code' => $sku_code,
                'sku_code_other' => $sku_code_other,
                'product_description' => $product_description,
                'inventory' => $inventory,
            ];
        }
    }

    private function paginateArray($data, $perPage)
    {
        $currentPage = $this->page ?: 1;
        $items = collect($data);
        $offset = ($currentPage - 1) * $perPage;
        $itemsForCurrentPage = $items->slice($offset, $perPage);
        
        $paginator = new LengthAwarePaginator(
            $itemsForCurrentPage,
            $items->count(),
            $perPage,
            $currentPage,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'onEachSide' => 1]
        );

        return $paginator;
    }

    public function mount($account_branch) {
        $this->account_branch = $account_branch;
        $this->year = date('Y');
        $this->month = date('m');
    }

    public function render()
    {
        $paginatedData = NULL;
        if(!empty($this->data)) {
            $paginatedData = $this->paginateArray($this->data, $this->perPage);
        }

        return view('livewire.stock-on-hold.upload')->with([
            'paginatedData' => $paginatedData
        ]);
    }
}
