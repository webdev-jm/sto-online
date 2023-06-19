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

class SalesUpload extends Component
{
    use WithFileUploads;
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $sales_data;
    public $file;
    public $account;
    public $err_msg;

    public $perPage = 20;

    public function saveUpload() {
        if(!empty($this->sales_data)) {

            $upload = new Upload([
                'account_id' => $this->account->id,
                'user_id' => auth()->user()->id,
                'sku_count' => 0,
                'total_quantity' => 0,
                'total_price_vat' => 0,
                'total_amount' => 0,
                'total_amount_vat' => 0,
            ]);
            $upload->save();

            $sku_count = 0;
            $total_quantity = 0;
            $total_price_vat = 0;
            $total_amount = 0;
            $total_amount_vat = 0;
            foreach($this->sales_data as $data) {
                // check data
                if($data['check'] == 0) { // no error
                    $sku_count++;
                    $total_quantity += $data['quantity'];
                    $total_price_vat += $data['price_inc_vat'];
                    $total_amount += $data['amount'];
                    $total_amount_vat += $data['amount_inc_vat'];

                    $sale = new Sale([
                        'sales_upload_id' => $upload->id,
                        'account_id' => $this->account->id,
                        'customer_id' => $data['customer_id'],
                        'product_id' => $data['product_id'],
                        'channel_id' => NULL,
                        'salesman_id' => $data['salesman_id'],
                        'location_id' => $data['location_id'],
                        'user_id' => auth()->user()->id,
                        'date' => date('Y-m-d', strtotime($data['date'])),
                        'document_number' => $data['document'],
                        'uom' => $data['uom'],
                        'quantity' => $data['quantity'],
                        'price_inc_vat' => $data['price_inc_vat'],
                        'amount' => $data['amount'],
                        'amount_inc_vat' => $data['amount_inc_vat'],
                    ]);
                    $sale->save();
                }
            }

            $upload->update([
                'sku_count' => $sku_count,
                'total_quantity' => $total_quantity,
                'total_price_vat' => $total_price_vat,
                'total_amount' => $total_amount,
                'total_amount_vat' => $total_amount_vat,
            ]);
        }

        // logs
        activity('upload')
        ->log(':causer.name has uploaded sales data on ['.$this->account->short_name.']');

        return redirect()->route('sales.index')->with([
            'message_success' => 'Location data has been uploaded.'
        ]);
    }

    public function updatedFile() {
        $this->validate([
            'file' => 'required|mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel'
        ]);
    
        $path = $this->file->getRealPath();
        $data = Excel::toArray([], $path)[0];
        $header = $data[1];
    
        $this->reset([
            'sales_data',
            'err_msg'
        ]);
    
        if($this->checkHeader($header) == 0) {
            // get customers
            $customers = Customer::where('account_id', $this->account->id)
                ->whereIn('code', array_unique(array_map('trim', Collection::make($data)->pluck(2)->slice(2)->toArray())))
                ->get()
                ->keyBy('code');
            // get locations
            $locations = Location::where('account_id', $this->account->id)
                ->whereIn('code', array_unique(Collection::make($data)->pluck(4)->slice(2)->toArray()))
                ->get()
                ->keyBy('code');
            // get products
            $products = SMSProduct::whereIn('stock_code', array_unique(array_map('trim', Collection::make($data)->pluck(5)->slice(2)->toArray())))
                ->get()
                ->keyBy('stock_code');
    
            foreach(array_slice($data, 2) as $row) {
                $customerCode = trim($row[2]);
                $locationCode = $row[4];
                $skuCode = trim($row[5]);

                if(strpos(trim($skuCode), '-')) {
                    $sku_arr = explode('-', $skuCode);
                    if($sku_arr[0] == 'FG') { // Free Goods
                        $skuCode = end($sku_arr);
                        // process when free goods
                    }
                    if($sku_arr[0] == 'PRM') { // Promo
                        $skuCode = end($sku_arr);
                        // process when promo
                    }
                }

                // remove comma and convert to float from values
                $quantity = (float)trim(str_replace(',', '', $row[9]));
                $price_inc_vat = (float)trim(str_replace(',', '', $row[11]));
                $amount = (float)trim(str_replace(',', '', $row[12]));
                $amount_inc_vat = (float)trim(str_replace(',', '', $row[13]));
                $line_discount = (float)trim(str_replace(',', '', $row[14]));
    
                if($customers->has($customerCode) && $locations->has($locationCode) && $products->has($skuCode)) {
                    $customer = $customers->get($customerCode);
                    $location = $locations->get($locationCode);
                    $product = $products->get($skuCode);
    
                    $this->sales_data[] = [
                        'check' => 0,
                        'date' => $row[0],
                        'document' => $row[1],
                        'customer_code' => $customerCode,
                        'location_code' => $locationCode,
                        'sku_code' => $row[5],
                        'customer_id' => $customer->id,
                        'location_id' => $location->id,
                        'product_id' => $product->id,
                        'salesman_id' => $customer->salesman_id,
                        'description' => $row[6],
                        'size' => $row[7],
                        'quantity' => $quantity,
                        'uom' => $row[10],
                        'price_inc_vat' => $price_inc_vat,
                        'amount' => $amount,
                        'amount_inc_vat' => $amount_inc_vat,
                        'line_discount' => $line_discount,
                    ];
                } else {

                    $this->sales_data[] = [
                        'check' => ($customers->has($customerCode)) ? ($locations->has($locationCode) ? 3 : 2) : 1,
                        'date' => $row[0],
                        'document' => $row[1],
                        'customer_code' => $customerCode,
                        'location_code' => $locationCode,
                        'sku_code' => $row[5],
                        'description' => $row[6],
                        'size' => $row[7],
                        'quantity' => $quantity,
                        'uom' => $row[10],
                        'price_inc_vat' => $price_inc_vat,
                        'amount' => $amount,
                        'amount_inc_vat' => $amount_inc_vat,
                        'line_discount' => $line_discount,
                    ];
                }
            }
        } else {
            $this->err_msg = 'Invalid format. Please provide an excel with the correct format.';
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

    private function checkHeader($header) {
        $requiredHeaders = [
            'Posting Date',
            'Document No.',
            'Sell-to Customer No.',
            'Type',
            'Location Code',
            'No.',
            'Description',
            'Description 2',
            'Item Category Code',
            'Quantity',
            'Unit of Measure Code',
            'Unit Price Incl. VAT',
            'Amount',
            'Amount Including VAT',
            'Line Discount %'
        ];
    
        $err = 0;
        foreach ($requiredHeaders as $index => $requiredHeader) {
            if (trim(strtolower($header[$index])) !== strtolower($requiredHeader)) {
                $err++;
            }
        }
    
        return $err;
    }

    public function mount() {
        $this->account = Session::get('account');
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
