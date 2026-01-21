<?php

namespace App\Http\Livewire\ProductMapping;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

use App\Models\ProductMapping;
use App\Models\SMSProduct;

use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class MappingList extends Component
{
    use WithFileUploads;
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $account;
    public $mapping_arr;
    public $upload_file;

    public function render()
    {
        $products = SMSProduct::orderBy('stock_code', 'asc')
            ->get();

        return view('livewire.product-mapping.mapping-list')->with([
            'products' => $products,
        ]);
    }

    public function mount() {
        $mappings = ProductMapping::orderBy('id', 'asc')
            ->get();

        if(!empty($mappings->count())) {
            foreach($mappings as $mapping) {
                $this->mapping_arr[] = [
                    'id' => $mapping->id,
                    'product_id' => $mapping->product_id,
                    'external_stock_code' => $mapping->external_stock_code,
                    'type' => $mapping->type,
                ];
            }

        } else {
            $this->mapping_arr[] = [
                'id' => NULL,
                'product_id' => NULL,
                'external_stock_code' => '',
                'type' => 1,
            ];
        }

    }

    public function addRow() {
        $this->mapping_arr[] = [
            'id' => NULL,
            'product_id' => NULL,
            'external_stock_code' => '',
            'type' => 1,
        ];
    }

    public function removeRow($index) {
        if(isset($this->mapping_arr[$index]['id']) && !empty($this->mapping_arr[$index]['id'])) {
            $mapping = ProductMapping::find($this->mapping_arr[$index]['id']);
            $mapping->forceDelete();
        }

        unset($this->mapping_arr[$index]);
        $this->mapping_arr = array_values($this->mapping_arr);
    }

    public function saveMapping($key) {
        $mapping_data = $this->mapping_arr[$key];

        if(isset($mapping_data['id']) && !empty($mapping_data['id'])) {
            $mapping = ProductMapping::find($mapping_data['id']);
        } else {
            $mapping = new ProductMapping();
        }

        $mapping->account_id = $this->account->id;
        $mapping->product_id = $mapping_data['product_id'];
        $mapping->external_stock_code = $mapping_data['external_stock_code'];
        $mapping->type = $mapping_data['type'];
        $mapping->save();

        $this->mapping_arr[$key]['id'] = $mapping->id;
    }

    public function updatedMappingArr($value, $key)
    {
        $key = explode('.', $key)[0];
        $this->saveMapping($key);
    }

    public function updatedUploadFile()
    {
       $this->validate([
            'upload_file' => 'required|mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel'
        ]);

        // Generate a unique filename
        $originalName = $this->upload_file->getClientOriginalName();
        $filename = time() . '_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $this->upload_file->getClientOriginalExtension();
        $path1 = $this->upload_file->storeAs('product-mapping-uploads', $filename);
        $path = storage_path('app').'/'.$path1;

        $excelSheets = Excel::toArray([], $path);

        $dataRows = array_slice($excelSheets[0], 2);

        foreach($dataRows as $row) {
            if(!empty($row[0]) && !empty($row[1]) && !empty($row[2])) {
                $external_stock_code = trim($row[0]);
                $stock_code = trim($row[1]);
                $type = trim($row[2]);

                $product = SMSProduct::where('stock_code', $stock_code)->first();

                if(!empty($product)) {

                    $mapping = ProductMapping::where('account_id', $this->account->id)
                        ->where('external_stock_code', $external_stock_code)
                        ->first();

                    if(!$mapping) {
                        $mapping = new ProductMapping();
                    }

                    $mapping->account_id = $this->account->id;
                    $mapping->external_stock_code = $external_stock_code;
                    $mapping->product_id = $product->id;
                    $mapping->type = $type;
                    $mapping->save();

                    $this->mapping_arr[] = [
                        'id' => $mapping->id,
                        'product_id' => $mapping->product_id,
                        'external_stock_code' => $mapping->external_stock_code,
                        'type' => $mapping->type,
                    ];
                }

            }
        }
    }
}
