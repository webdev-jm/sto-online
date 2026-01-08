<?php

namespace App\Http\Livewire\Inventory;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

use Maatwebsite\Excel\Facades\Excel;

use App\Models\Location;
use App\Models\Inventory;
use App\Models\InventoryUpload as IU;
use App\Models\SMSProduct;

use App\Http\Traits\GenerateMonthlyInventory;

use App\Jobs\InventoryImportJob;

use PhpOffice\PhpSpreadsheet\Shared\Date;

class InventoryUpload extends Component
{
    use GenerateMonthlyInventory;

    use WithFileUploads;
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $inventory_data;
    public $inventory_date;
    public $keys;
    public $file;
    public $data;
    public $err_msg;

    public $account;
    public $account_branch;

    public $perPage = 20;

    public $upload_triggered = false;

    public function uploadData() {
        if(!empty($this->inventory_data)) {

            $this->validate([
                'inventory_date' => [
                    'required'
                ]
            ]);

            // avoid duplicate uploads
            if ($this->upload_triggered) {
                return;
            }

            $inventory_upload = new IU([
                'account_id' => $this->account->id,
                'account_branch_id' => $this->account_branch->id,
                'user_id' => auth()->user()->id,
                'date' => $this->inventory_date,
                'total_inventory' => 0
            ]);
            $inventory_upload->save();

            InventoryImportJob::dispatch($this->inventory_data, $this->account->id, $this->account_branch->id, auth()->user()->id, $inventory_upload->id);

            $total = 0;
            foreach($this->inventory_data as $data) {
                if($data['check'] == 0) {
                    $total++;
                }
            }

            $upload_data = [
                'total' => $total,
                'start' => 0,
                'upload_id' => $inventory_upload->id,
            ];

            $this->upload_triggered = true;

            // logs
            activity('upload')
            ->performedOn($inventory_upload)
            ->log(':causer.name has uploaded inventory data on ['.$this->account->short_name.']');

            return redirect()->route('inventory.index')->with([
                'message_success' => 'Inventory data has been uploaded.',
                'upload_data' => $upload_data
            ]);
        }
    }

    public function updatedFile() {
        $this->validate([
            'file' => 'required|mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel'
        ]);

        $path1 = $this->file->store('inventory-uploads');
        $path = storage_path('app').'/'.$path1;
        $this->data = Excel::toArray([], $path)[0];

        $this->resetPage('page');

        $this->checkData($this->data);
    }

    public function checkData($data) {
        $this->reset([
            'inventory_data',
            'err_msg'
        ]);

        $header = $data[0];

        if($this->checkHeader($header) == 0) {
            $sku_codes = array_unique(array_map('trim', Collection::make($data)->pluck(0)->slice(1)->toArray()));
            $location_codes = array_unique(array_map('trim', Collection::make($data)->pluck(2)->slice(1)->toArray()));

            foreach($sku_codes as $key => $code) {
                if(strpos(trim($code ?? ''), '-')) {
                    $sku_arr = explode('-', $code);

                    $sku_codes[$key] = end($sku_arr);
                }
            }

            $products = SMSProduct::whereIn('stock_code', $sku_codes)
                ->get()
                ->keyBy('stock_code');

            $locations = Location::where('account_id', $this->account->id)
                ->where('account_branch_id', $this->account_branch->id)
                ->whereIn('code', $location_codes)
                ->get()
                ->keyBy('code');

            foreach(array_slice($data, 1) as $data_key => $row) {
                $sku_code = trim($row[0] ?? '');
                $location_code = trim($row[2] ?? '');

                $type = 1;
                if(strpos(trim($sku_code ?? ''), '-')) {
                    $sku_arr = explode('-', $sku_code);
                    if($sku_arr[0] == 'FG') { // Free Goods
                        $sku_code = end($sku_arr);
                        // process when free goods
                        $type = 2;
                    }
                    if($sku_arr[0] == 'PRM') { // Promo
                        $sku_code = end($sku_arr);
                        // process when promo
                        $type = 3;
                    }
                }

                $expiry_date = $row[5];
                if (is_numeric($expiry_date)) {
                    $expiry_date = Date::excelToDateTimeObject($expiry_date)->format('Y-m-d');
                }

                if($products->has($sku_code) && $locations->has($location_code)) {
                    $product = $products->get($sku_code);
                    $location = $locations->get($location_code);

                    $this->inventory_data[$data_key] = [
                        'type' => $type,
                        'check' => 0,
                        'sku_code' => $row[0],
                        'description' => $row[1],
                        'location_code' => $location->code ?? NULL,
                        'location_name' => $location->name ?? NULL,
                        'location' => $location ?? NULL,
                        'product_id' => $product->id ?? NULL,
                        'uom' => $row[3],
                        'quantity' => $row[4],
                        'expiry_date' => $expiry_date
                    ];
                } else {
                    if(!$products->has($sku_code)) {
                        $this->inventory_data[$data_key] = [
                            'type' => $type,
                            'check' => 1,
                            'sku_code' => $row[0],
                            'description' => $row[1],
                            'product_id' => $product->id ?? NULL,
                            'location_code' => $location->code ?? NULL,
                            'location_name' => $location->name ?? NULL,
                            'location' => $location ?? NULL,
                            'uom' => $row[3],
                            'quantity' => $row[4],
                            'expiry_date' => $expiry_date
                        ];
                    } else if(!$locations->has($location_code)) {
                        $this->inventory_data[$data_key] = [
                            'type' => $type,
                            'check' => 2,
                            'sku_code' => $row[0],
                            'description' => $row[1],
                            'product_id' => $product->id ?? NULL,
                            'location_code' => $location->code ?? NULL,
                            'location_name' => $location->name ?? NULL,
                            'location' => $location ?? NULL,
                            'uom' => $row[3],
                            'quantity' => $row[4],
                            'expiry_date' => $expiry_date
                        ];
                    }
                }
            }

            usort($this->inventory_data, function($a, $b) {
                return $b['check'] <=> $a['check'];
            });

        } else {
            $this->err_msg = 'Invalid format. Please provide an excel with the correct format.';
        }
    }

    private function checkHeader($header) {
        $requiredHeaders = [
            'SKU CODE',
            'DESCRIPTION',
            'LOCATION',
            'UOM',
            'QUANTITY',
            'EXPIRY DATE',
        ];

        $err = 0;
        foreach ($requiredHeaders as $index => $requiredHeader) {
            if (trim(strtolower($header[$index]) ?? '') !== strtolower($requiredHeader)) {
                $err++;
            }
        }

        return $err;
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

    public function mount() {
        $this->account = Session::get('account');
        $this->account_branch = Session::get('account_branch');
    }

    public function render()
    {
        $paginatedData = NULL;
        if(!empty($this->inventory_data)) {
            $paginatedData = $this->paginateArray($this->inventory_data, $this->perPage);
        }

        return view('livewire.inventory.inventory-upload')->with([
            'paginatedData' => $paginatedData
        ]);
    }
}
