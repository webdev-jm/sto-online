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

use App\Models\UploadTemplate;
use App\Models\AccountUploadTemplate;

use App\Jobs\SalesImportJob;

use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\IOFactory;

use Spatie\SimpleExcel\SimpleExcelReader;

ini_set('memory_limit', '-1');
ini_set('max_execution_time', 0);
ini_set('sqlsrv.ClientBufferMaxKBSize','1000000'); // Setting to 512M
ini_set('pdo_sqlsrv.client_buffer_max_kb_size','1000000');

class SalesUpload extends Component
{
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

    private function saveCustomer($rowData) {
        $customer_code = $rowData['customer_code'];
        $customer_name = $rowData['customer_name'] ?? '';
        $customer_address = $rowData['customer_address'] ?? '';
        $channel_code = $rowData['channel_code'] ?? '';
        $channel_name = $rowData['channel_name'] ?? '';
        $province = $rowData['province'] ?? '';
        $city = $rowData['city'] ?? '';
        $barangay = $rowData['barangay'] ?? '';
        $street = $rowData['street'] ?? '';
        $postal_code = $rowData['postal_code'] ?? '';
        $country = '';

        if(!empty($customer_code) && !empty($customer_name) && !empty($customer_address)) {

            $salesman = Salesman::where('code', $rowData['salesman_code'])
                ->orWhere('name', $rowData['salesman_name'])
                ->first();
            $channel = Channel::where('code', $rowData['channel_code'])
                ->orWhere('name', $rowData['channel_name'])
                ->first();

            $province = Province::where('province_name', $province)->first();
            $municipality = Municipality::where('municipality_name', $city)->first();
            $barangay = Barangay::where('barangay_name', $barangay)->first();

            $customer = new Customer([
                'account_id' => $this->account->id,
                'account_branch_id' => $this->account_branch->id,
                'salesman_id' => $salesman->id ?? NULL,
                'channel_id' => $channel->id ?? NULL,
                'province_id' => $province->id ?? NULL,
                'municipality_id' => $municipality->id ?? NULL,
                'barangay_id' => $barangay->id ?? NULL,
                'code' => $customer_code,
                'name' => $customer_name,
                'address' => $customer_address,
                'street' => $street,
                'brgy' => $barangay,
                'city' => $city,
                'province' => $province,
                'country' => $country,
                'postal_code' => $postal_code,
                'status' => NULL
            ]);
            $customer->save();
        }

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

        $upload_template = UploadTemplate::where('title', 'SALES UPLOAD')->first();
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

        $path1 = $this->file->storeAs('sales-uploads', $this->file->getClientOriginalName());
        $path = storage_path('app').'/'.$path1;
        // Get the file extension
        $extension = $this->file->getClientOriginalExtension();

        $data = array();
        if(in_array($extension, ['xlsx', 'csv', 'bin'])) {
            if($account_template->type == 'name') {
                $rows  = SimpleExcelReader::create($path)
                    ->getRows();
            } else if($account_template->type == 'number') {
                $rows  = SimpleExcelReader::create($path)
                    ->skip($account_template->start_row - 1)
                    ->noHeaderRow()
                    ->getRows();
            }

            $rows->each(function($row) use(&$data, $upload_template, $account_template_fields, $account_template) {
                $this->processRow($row, $data, $upload_template, $account_template_fields, $account_template->type);
            });
        }

        // $data = [];
        // foreach ($worksheet->getRowIterator() as $row) {
        //     $rowResults = []; // Array to store results for this row
        //     foreach ($row->getCellIterator() as $cell) {
        //         $rowResults[] = $cell->getCalculatedValue(); // Store the result of the formula
        //     }
        //     $data[] = $rowResults; // Store the results for this row in the main results array
        // }

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

        $header = $data[0];

        if ($this->checkHeader($header) !== 0) {
            $this->err_msg = 'Invalid header format. Please provide an excel file with the correct column structure.';
            return;
        }

        $rows = array_slice($data, 1);

        // 1. Collect all unique codes in a single pass
        $customerCodes = [];
        $locationCodes = [];
        $skuCodes = [];

        foreach ($rows as $row) {
            if (count($row) < 6) continue;

            if (!empty($row['customer_code'])) $customerCodes[trim($row['customer_code'])] = true;
            if (!empty($row['warehouse_code'])) $locationCodes[trim($row['warehouse_code'])] = true;

            $sku_code = trim($row['sku_code'] ?? '');
            if (strpos($sku_code, '-') !== false) {
                $sku_arr = explode('-', $sku_code);
                if ($sku_arr[0] === 'FG' || $sku_arr[0] === 'PRM') {
                    $sku_code = end($sku_arr);
                }
            }
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

            $invoice_date = $row['invoice_date'];
            $customer_code = trim($row['customer_code']);
            $invoice_number = trim($row['invoice_number']);
            $warehouse_code = trim($row['warehouse_code']);
            $original_sku_code = trim($row['sku_code']);
            // Convert DateTimeImmutable to string date 'YYYY-MM-DD' if needed
            if ($invoice_date instanceof \DateTimeImmutable) {
                $invoice_date = $invoice_date->format('Y-m-d');
            }
            $sku_code = $original_sku_code;
            $type = 1;
            if (strpos($original_sku_code, '-') !== false) {
                $sku_arr = explode('-', $original_sku_code);
                if ($sku_arr[0] === 'FG') { $type = 2; $sku_code = end($sku_arr); }
                if ($sku_arr[0] === 'PRM') { $type = 3; $sku_code = end($sku_arr); }
            }

            $category = 0;
            if (!empty($invoice_number) && strpos($invoice_number, '-') !== false && explode('-', $invoice_number)[0] === 'PSC') {
                $category = 1;
            }

            $quantity = (float)str_replace(',', '', trim($row['quantity']));
            $price_inc_vat = (float)str_replace(',', '', trim($row['unit_price_inc_vat']));
            $amount = (float)str_replace(',', '', trim($row['amount']));
            $amount_inc_vat = (float)str_replace(',', '', trim($row['amount_inc_vat']));
            $line_discount = (float)str_replace(',', '', trim($row['line_discount']));

            if (is_numeric($invoice_date)) {
                $invoice_date = Date::excelToDateTimeObject($invoice_date)->format('Y-m-d');
            }

            $customer = $customers->get($customer_code);
            $location = $locations->get($warehouse_code);
            $product = $products->get($sku_code);

            $rowData = [
                'type' => $type, 'check' => 0, 'date' => $invoice_date, 'document' => $invoice_number,
                'category' => $category, 'customer_code' => $customer_code, 'location_code' => $warehouse_code,
                'sku_code' => $original_sku_code, 'quantity' => $quantity, 'uom' => trim($row['uom']),
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

    private function processRow($row, &$data, $upload_template, $account_template_fields, $type) {

        foreach ($upload_template->fields as $field) {
            $column_name = $field->column_name;
            if($type == 'name') {
                $file_column_name = $account_template_fields[$field->id]['file_column_name'];
            } else if($type == 'number') {
                $file_column_name = $account_template_fields[$field->id]['file_column_number'] - 1;
            }

            ${$column_name} = $row[$file_column_name] ?? NULL;
        }

        $rowData = [];
        foreach($upload_template->fields as $field) {
            $column_name = $field->column_name;
            $rowData[$column_name] = ${$column_name};
        }
        $data[] = $rowData;
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
        // change key of header array to numeric starting at 0
        $headerKeys = array_values($header);

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
            if(empty($headerKeys[$index]) || (trim(strtolower($headerKeys[$index])) !== strtolower($requiredHeader) && trim(strtolower($headerKeys[$index])) !== strtolower($requiredHeadersAlt[$index]))) {
                $err++;
                $this->header_err[] = '<b>'.($headerKeys[$index] ?? '-').'</b> should be <b>'. $requiredHeader.'</b>';
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
