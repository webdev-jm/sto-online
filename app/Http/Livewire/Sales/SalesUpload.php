<?php

namespace App\Http\Livewire\Sales;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

use Maatwebsite\Excel\Facades\Excel;

use App\Models\Sale;
use App\Models\Location;
use App\Models\Customer;
use App\Models\SMSProduct;
use App\Models\SalesUpload as Upload;

use App\Jobs\SalesImportJob;

use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\IOFactory;

use App\Http\Traits\ProductMappingTrait;

ini_set('memory_limit', '-1');
ini_set('max_execution_time', 0);
ini_set('sqlsrv.ClientBufferMaxKBSize','1000000'); // Setting to 512M
ini_set('pdo_sqlsrv.client_buffer_max_kb_size','1000000');

class SalesUpload extends Component
{
    use ProductMappingTrait;
    use WithFileUploads;
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $sales_data;
    public $data;
    public $file;
    public $account;
    public $account_branch;
    public $err_msg;

    public $perPage = 20;

    public $upload_data;

    public $upload_triggered = false;

    public $header_err;

    protected $listeners = [
        'checkData' => 'updateData'
    ];

    public function updateData() {
        $this->checkData($this->data);
    }

    public function maintainCustomer($customer_code) {
        $this->emit('maintainCustomer', $customer_code);
        $this->dispatchBrowserEvent('maintainCustomer');
    }

    public function maintainLocation($location_code) {
        $this->emit('maintainLocation', $location_code);
        $this->dispatchBrowserEvent('maintainLocation');
    }

    public function saveUpload() {
        // avoid duplicate uploads
        if ($this->upload_triggered) {
            return;
        }

        if(!empty($this->sales_data)) {

            $upload = new Upload([
                'account_id' => $this->account->id,
                'account_branch_id' => $this->account_branch->id,
                'user_id' => auth()->user()->id,
                'sku_count' => 0,
                'total_quantity' => 0,
                'total_price_vat' => 0,
                'total_amount' => 0,
                'total_amount_vat' => 0,
            ]);
            $upload->save();

            SalesImportJob::dispatch($this->sales_data, $this->account->id, $this->account_branch->id, auth()->user()->id, $upload->id);

            $total = 0;
            foreach($this->sales_data as $data) {
                if($data['check'] == 0) {
                    $total++;
                }
            }

            $upload_data = [
                'total' => $total,
                'start' => 0,
                'upload_id' => $upload->id
            ];

            // logs
            activity('upload')
            ->performedOn($upload)
            ->log(':causer.name has uploaded sales data on ['.$this->account->short_name.']');

            $this->upload_triggered = true;

            return redirect()->route('sales.index')->with([
                'message_success' => 'Sales data has been added to queue for processing.',
                'upload_data' => $upload_data
            ]);

        } else {
            $this->err_msg = 'No data has been saved!';
        }
    }

    public function updatedFile() {
        $this->validate([
            'file' => 'required|mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel'
        ]);

        $path1 = $this->file->storeAs('sales-uploads', $this->file->getClientOriginalName());
        $path = storage_path('app').'/'.$path1;
        // $this->data = Excel::toArray([], $path)[0];
        $spreadsheet = IOFactory::load($path);
        $worksheet = $spreadsheet->getActiveSheet();

        $data = [];
        foreach ($worksheet->getRowIterator() as $row) {
            $rowResults = []; // Array to store results for this row
            foreach ($row->getCellIterator() as $cell) {
                $rowResults[] = $cell->getCalculatedValue(); // Store the result of the formula
            }
            $data[] = $rowResults; // Store the results for this row in the main results array
        }

        $this->data = $data;

        $this->checkData($this->data);

        $this->resetPage('page');
    }

    public function checkData($data) {
        $this->reset([
            'sales_data',
            'err_msg'
        ]);

        if (empty($data) || count($data) < 3) {
            $this->err_msg = 'The file is empty or has no data rows.';
            return;
        }

        $header = $data[1];

        if ($this->checkHeader($header) !== 0) {
            $this->err_msg = 'Invalid header format. Please provide an excel file with the correct column structure.';
            return;
        }

        $rows = array_slice($data, 2);

        // 1. Collect all unique codes in a single pass
        $customerCodes = [];
        $locationCodes = [];
        $skuCodes = [];

        foreach ($rows as $row) {
            if (count($row) < 6) continue;

            if (!empty($row[1])) $customerCodes[trim($row[1])] = true;
            if (!empty($row[4])) $locationCodes[trim($row[4])] = true;

            $sku_code = trim($row[5] ?? '');
            if (strpos($sku_code, '-') !== false) {
                $sku_arr = explode('-', $sku_code);
                if ($sku_arr[0] === 'FG' || $sku_arr[0] === 'PRM') {
                    $sku_code = end($sku_arr);
                }
            }

            // SKU MAPPING
            $mappingResult = $this->productMapping($this->account->account_code, $sku_code);
            $sku_code = $mappingResult[0];

            if (!empty($sku_code)) $skuCodes[$sku_code] = true;
        }

        // 2. Pre-fetch all related models efficiently
        $customers = Customer::where('account_id', $this->account->id)
            ->where('account_branch_id', $this->account_branch->id)
            ->whereIn('code', array_keys($customerCodes))
            ->get()->keyBy('code');

        $locations = Location::where('account_id', $this->account->id)
            ->where('account_branch_id', $this->account_branch->id)
            ->whereIn('code', array_keys($locationCodes))
            ->get()->keyBy('code');

        $products = SMSProduct::whereIn('stock_code', array_keys($skuCodes))
            ->get()->keyBy('stock_code');

        // 3. Process rows and prepare for bulk existence check
        $processedData = [];
        $existenceCheckPayload = [];

        foreach ($rows as $index => $row) {
            if (count($row) < 12) continue;

            $invoice_date = $row[0];
            $customer_code = trim($row[1]);
            $invoice_number = trim($row[3]);
            $warehouse_code = trim($row[4]);
            $original_sku_code = trim($row[5]);

            $sku_code = $original_sku_code;
            $type = 1;
            if (strpos($original_sku_code, '-') !== false) {
                $sku_arr = explode('-', $original_sku_code);
                if ($sku_arr[0] === 'FG') { $type = 2; $sku_code = end($sku_arr); }
                if ($sku_arr[0] === 'PRM') { $type = 3; $sku_code = end($sku_arr); }
            }

            // SKU MAPPING
            $mappingResult = $this->productMapping($this->account->account_code, $sku_code);
            $sku_code = $mappingResult[0];
            $type = $mappingResult[1] ?? $type;

            $quantity = (float)str_replace(',', '', trim($row[6]));
            $price_inc_vat = (float)str_replace(',', '', trim($row[8]));
            $amount = (float)str_replace(',', '', trim($row[9]));
            $amount_inc_vat = (float)str_replace(',', '', trim($row[10]));
            $line_discount = (float)str_replace(',', '', trim($row[11]));

            $category = 0;
            if ((!empty($invoice_number) && strpos($invoice_number, '-') !== false && explode('-', $invoice_number)[0] === 'PSC') || ($amount < 0)) {
                $category = 1;
            }

            if (is_numeric($invoice_date)) {
                $invoice_date = Date::excelToDateTimeObject($invoice_date)->format('Y-m-d');
            }

            $customer = $customers->get($customer_code);
            $location = $locations->get($warehouse_code);
            $product = $products->get($sku_code);

            $rowData = [
                'type' => $type, 'check' => 0, 'date' => $invoice_date, 'document' => $invoice_number,
                'category' => $category, 'customer_code' => $customer_code, 'location_code' => $warehouse_code,
                'sku_code' => $original_sku_code, 'quantity' => $quantity, 'uom' => trim($row[7]),
                'price_inc_vat' => $price_inc_vat, 'amount' => $amount, 'amount_inc_vat' => $amount_inc_vat,
                'line_discount' => $line_discount, 'status' => 2,
            ];

            if ($customer && $location && $product) {
                $rowData = array_merge($rowData, [
                    'customer_id' => $customer->id, 'channel_id' => $customer->channel_id,
                    'location_id' => $location->id, 'product_id' => $product->id,
                    'salesman_id' => $customer->salesman_id, 'description' => $product->description,
                    'size' => $product->size, 'status' => $customer->status,
                ]);
                $existenceCheckPayload[] = ['doc' => $invoice_number, 'cust_id' => $customer->id, 'prod_id' => $product->id, 'index' => $index];
            } else {
                $rowData['check'] = !$customer ? 1 : (!$location ? 2 : 3);
            }
            $processedData[$index] = $rowData;
        }

        // 4. Bulk check for existing sales records
        if (!empty($existenceCheckPayload)) {
            $existingSales = Sale::where('account_id', $this->account->id)
                ->where('account_branch_id', $this->account_branch->id)
                ->where(function ($query) use ($existenceCheckPayload) {
                    foreach ($existenceCheckPayload as $payload) {
                        $query->orWhere(function ($q) use ($payload) {
                            $q->where('document_number', $payload['doc'])
                              ->where('customer_id', $payload['cust_id'])
                              ->where('product_id', $payload['prod_id']);
                        });
                    }
                })
                ->select('document_number', 'customer_id', 'product_id')->get()
                ->mapWithKeys(fn($sale) => [$sale->document_number . '|' . $sale->customer_id . '|' . $sale->product_id => true]);

            foreach ($existenceCheckPayload as $payload) {
                $key = $payload['doc'] . '|' . $payload['cust_id'] . '|' . $payload['prod_id'];
                if (isset($existingSales[$key])) {
                    $processedData[$payload['index']]['check'] = 4;
                }
            }
        }

        // 5. Final sort and assignment
        $this->sales_data = array_values($processedData);
        usort($this->sales_data, function($a, $b) {
            if ($a['check'] !== $b['check']) {
                return $b['check'] <=> $a['check'];
            }
            return $a['date'] <=> $b['date'];
        });
    }

    private function isExcelDate(Cell $cell) {
        return Date::isDateTime($cell);
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

    private function checkHeader($header) {
        $requiredHeaders = [
            'Invoice Date',
            'Customer Code',
            'Salesman Code',
            'Document No./Invoice Number/CM Number',
            'Warehouse Code',
            'BEVI Sku Code',
            'Quantity',
            'Unit of Measure Code',
            'Unit Price Incl. VAT',
            'Amount',
            'Amount Including VAT',
            'Line Discount %'
        ];

        $requiredHeadersAlt = [
            'Invoice Date',
            'Customer Code',
            'Salesman Code',
            'Invoice Number',
            'Warehouse Code',
            'BEVI Sku Code',
            'Quantity',
            'Unit of Measure Code',
            'Unit Price Incl VAT',
            'Amount',
            'Amount Including VAT',
            'Line Discount'
        ];

        $err = 0;
        $this->header_err = array();
        foreach ($requiredHeaders as $index => $requiredHeader) {
            if(empty($header[$index]) || (trim(strtolower($header[$index])) !== strtolower($requiredHeader) && trim(strtolower($header[$index])) !== strtolower($requiredHeadersAlt[$index]))) {
                $err++;
                $this->header_err[] = '<b>'.($header[$index] ?? '-').'</b> should be <b>'. $requiredHeader.'</b>';
            }
        }

        return $err;
    }

    public function mount() {
        $this->account = Session::get('account');
        $this->account_branch = Session::get('account_branch');
    }

    public function render()
    {
        $paginatedData = NULL;
        if(!empty($this->sales_data)) {
            $paginatedData = $this->paginateArray($this->sales_data, $this->perPage);
        }

        return view('livewire.sales.sales-upload')->with([
            'paginatedData' => $paginatedData
        ]);
    }
}
