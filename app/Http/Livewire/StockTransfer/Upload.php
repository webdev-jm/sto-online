<?php

namespace App\Http\Livewire\StockTransfer;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

use App\Models\Customer;
use App\Models\UploadTemplate;
use App\Models\AccountUploadTemplate;

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
    public $year, $month;
    public $data;
    public $perPage = 20;

    public function saveData() {
        $this->validate([
            'year' => 'required',
            'month' => 'required',
        ]);

        if(!empty($this->data)) {
            foreach($this->data as $val) {
                
            }
        }
    }

    private function checkCustomer($customer_code, $customer_name) {
        // check if customer exists
        $customer = Customer::where('code', $customer_code)
            ->first();
        if(empty($customer)) {
            $customer = new Customer([
                'account_id' => $this->account_branch->account_id,
                'account_branch_id' => $this->account_branch->id,
                'code' => $customer_code,
                'name' => $customer_name,
                'address' => NULL,
            ]);
            $customer->save();
        }
    }

    public function checkFile() {
        $this->validate([
            'upload_file' => 'required',
        ]);

        $upload_template = UploadTemplate::where('title', 'STOCK TRANSFER UPLOAD')->first();
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

        $path1 = $this->upload_file->storeAs('stock-transfer-uploads/account_branch_'.$this->account_branch->id, $this->upload_file->getClientOriginalName());
        $path = storage_path('app').'/'.$path1;

        // Get the file extension
        $extension = $this->upload_file->getClientOriginalExtension();

        $data = array();
        if(in_array($extension, ['xlsx', 'csv', 'bin'])) {
            if($account_template->type == 'name') {
                $rows = SimpleExcelReader::create($path)
                    ->getRows();
            } else if($account_template->type == 'number') {
                $rows = SimpleExcelReader::create($path)
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

        $this->data = $data;
    }

    private function processRow($row, &$data, $upload_template, $account_template_fields, $type) {
        $customer_code = ''; // Assign or extract $customer_code as required
    
        foreach ($upload_template->fields as $field) {
            $column_name = $field->column_name;
            if($type == 'name') {
                $file_column_name = $account_template_fields[$field->id]['file_column_name'];
            } else if($type == 'number') {
                $file_column_name = $account_template_fields[$field->id]['file_column_number'] - 1;
            }

            ${$column_name} = $row[$file_column_name] ?? '';
        }
    
        if(!empty($customer_code) && !empty($sku_code)) {
            $data[] = [
                'customer_code' => trim($customer_code),
                'customer_name' => trim($customer_name),
                'sku_code' => $sku_code,
                'sku_code_other' => $sku_code_other,
                'product_description' => $product_description,
                'transfer_ty' => $transfer_ty,
                'transfer_ly' => $transfer_ly,
            ];
        }
    }

    private function paginateArray($data, $perPage) {
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

        return view('livewire.stock-transfer.upload')->with([
            'paginatedData' => $paginatedData,
        ]);
    }
}
