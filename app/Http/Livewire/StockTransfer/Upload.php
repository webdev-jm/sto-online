<?php

namespace App\Http\Livewire\StockTransfer;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

use App\Models\Customer;
use App\Models\UploadTemplate;
use App\Models\AccountUploadTemplate;
use App\Models\AccountProductReference;
use App\Models\SMSProduct;
use App\Models\StockTransfer;
use App\Models\StockTransferProduct;

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
    public $success_msg;
    public $page;

    public function saveData() {
        $this->validate([
            'year' => 'required',
            'month' => 'required',
        ]);

        if(!empty($this->data)) {
            // get account product mapping
            $account_product_references = AccountProductReference::where('account_id', $this->account_branch->account->sms_account_id)
                ->get();

            foreach($this->data as $val) {
                $customer = $this->checkCustomer($val['customer_code'], $val['customer_name']);

                // check if already exists
                $stock_transfer = StockTransfer::where('account_branch_id', $this->account_branch->id)
                    ->where('customer_id', $customer->id)
                    ->where('year', $this->year)
                    ->where('month', $this->month)
                    ->first();
                if(empty($stock_transfer)) {
                    $stock_transfer = new StockTransfer([
                        'account_branch_id' => $this->account_branch->id,
                        'customer_id' => $customer->id,
                        'year' => $this->year,
                        'month' => $this->month,
                    ]);
                    $stock_transfer->save();
                }

                // check product
                $product = SMSProduct::where('stock_code', $val['sku_code'])
                    ->orWhere('stock_code', $val['sku_code_other'])
                    ->first();
                if(empty($product)) {
                    $account_reference = $account_product_references->filter(function($reference) use($val) {
                            return $reference->account_reference == $val['sku_code']
                            || $reference->account_reference == $val['sku_code_other']
                            || $reference->account_reference == intval($val['sku_code'])
                            || $reference->account_reference == intval($val['sku_code_other']);
                        })
                        ->first();

                    if(!empty($account_reference)) {
                        $product = SMSProduct::where('id', $account_reference->product_id)
                            ->first();
                    }
                }

                $stock_transfer_product = new StockTransferProduct([
                    'stock_transfer_id' => $stock_transfer->id,
                    'product_id' => !empty($product) ? $product->id : NULL,
                    'sku_code' => $val['sku_code'],
                    'sku_code_other' => $val['sku_code_other'],
                    'transfer_ty' => $val['transfer_ty'],
                    'transfer_ly' => $val['transfer_ly'],
                ]);
                $stock_transfer_product->save();

                // increment total values
                $stock_transfer->update([
                    'total_units_transferred_ty' => $stock_transfer->total_units_transferred_ty + $val['transfer_ty'],
                    'total_units_transferred_ly' => $stock_transfer->total_units_transferred_ly + $val['transfer_ly'],
                ]);
            }

            $this->reset('data');
            $this->success_msg = 'Stock transfer data has been uploaded.';
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
                'address' => '',
            ]);
            $customer->save();
        }

        return $customer;
    }

    public function checkFile() {
        $this->validate([
            'upload_file' => 'required',
        ]);

        $stock_transfers = StockTransfer::select(
                'c.code',
                'product_id',
                'sku_code',
                'sku_code_other',
            )
            ->leftJoin('stock_transfer_products as stp', 'stp.stock_transfer_id', '=', 'stock_transfers.id')
            ->leftJoin('customers as c', 'c.id', '=', 'stock_transfers.customer_id')
            ->where('stock_transfers.account_branch_id', $this->account_branch->id)
            ->where('year', $this->year)
            ->where('month', $this->month)
            ->get();

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

            $rows->each(function($row) use(&$data, $upload_template, $account_template_fields, $account_template, $stock_transfers) {
                $this->processRow($row, $data, $upload_template, $account_template_fields, $account_template->type, $stock_transfers);
            });
        } else if($extension == 'xml') { // to be updated
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

    private function processRow($row, &$data, $upload_template, $account_template_fields, $type, $stock_transfers) {
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

            // check for duplicate
            $isDuplicate = $stock_transfers->contains(function ($stock_transfer) use ($customer_code, $sku_code, $sku_code_other) {
                return $stock_transfer->code == $customer_code && (
                        $stock_transfer->sku_code == $sku_code ||
                        $stock_transfer->sku_code_other == $sku_code_other
                    );
            });

            if(empty($isDuplicate)) {
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

    public function gotoPage($page, $el) {
        $this->page = $page;
    }

    public function previousPage($el) {
        $this->page--;
    }

    public function nextPage($el) {
        $this->page++;
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
