<?php

namespace App\Http\Livewire\ProductMapping;

use Livewire\Component;
use Livewire\WithPagination;

use App\Models\ProductMapping;
use App\Models\SMSProduct;

class MappingList extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $account;
    public $mapping_arr;

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
}
