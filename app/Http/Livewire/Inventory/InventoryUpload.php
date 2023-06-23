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

class InventoryUpload extends Component
{
    use WithFileUploads;
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $inventory_data;
    public $keys;
    public $file;
    public $data;
    public $err_msg;

    public $perPage = 20;

    public function uploadData() {
        if(!empty($this->inventory_data)) {
            $inventory_upload = new IU([
                'account_id' => $this->account->id,
                'user_id' => auth()->user()->id,
                'date' => date('Y-m-d'),
                'total_inventory' => 0
            ]);
            $inventory_upload->save();

            $total_inventory = 0;
            foreach($this->inventory_data as $data) {

                // check
                if($data['check'] == 0) {
                    foreach($this->keys as $key => $location) {
                        $total_inventory += $data[$location['id']];

                        $inventory = new Inventory([
                            'account_id' => $this->account->id,
                            'inventory_upload_id' => $inventory_upload->id,
                            'location_id' => $location['id'],
                            'product_id' => $data['product_id'],
                            'type' => $data['type'],
                            'uom' => $data['uom'],
                            'inventory' => $data[$location['id']],
                        ]);
                        $inventory->save();
                    }
                }
            }

            $inventory_upload->update([
                'total_inventory' => $total_inventory
            ]);

            // logs
            activity('upload')
            ->log(':causer.name has uploaded sales data on ['.$this->account->short_name.']');

            return redirect()->route('inventory.index')->with([
                'message_success' => 'Inventory data has been uploaded.'
            ]);
        }
    }

    public function updatedFile() {
        $this->validate([
            'file' => 'required|mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel'
        ]);

        $path = $this->file->getRealPath();
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
            foreach($sku_codes as $key => $code) {
                if(strpos(trim($code), '-')) {
                    $sku_arr = explode('-', $code);

                    $sku_codes[$key] = end($sku_arr);
                }
            }

            $products = SMSProduct::whereIn('stock_code', $sku_codes)
                ->get()
                ->keyBy('stock_code');

            // check header
            foreach($header as $key => $val) {
                $location = Location::where('account_id', $this->account->id)
                    ->where(function($query) use($val) {
                        $query->where('code', $val)
                            ->orWhere('name', $val);
                    })
                    ->first();

                if(!empty($location)) {
                    $this->keys[$key] = $location;
                }
            }

            foreach(array_slice($data, 1) as $data_key => $row) {
                $sku_code = trim($row[0]);

                $type = 1;
                if(strpos(trim($sku_code), '-')) {
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

                
                if($products->has($sku_code)) {
                    $product = $products->get($sku_code);

                    $this->inventory_data[$data_key] = [
                        'type' => $type,
                        'check' => 0,
                        'sku_code' => $row[0],
                        'description' => $row[1],
                        'product_id' => $product->id ?? NULL,
                        'uom' => 'PCS'
                    ];
                } else {
                    $this->inventory_data[$data_key] = [
                        'type' => $type,
                        'check' => 1,
                        'sku_code' => $row[0],
                        'description' => $row[1],
                        'product_id' => $product->id ?? NULL,
                        'uom' => 'PCS'
                    ]; 
                }

                foreach($this->keys as $key => $location) {
                    $this->inventory_data[$data_key][$location->id] = $row[$key];
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
            'no.',
            'description',
        ];
    
        $err = 0;
        foreach ($requiredHeaders as $index => $requiredHeader) {
            if (trim(strtolower($header[$index])) !== strtolower($requiredHeader)) {
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
