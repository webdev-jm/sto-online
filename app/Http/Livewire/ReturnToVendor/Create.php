<?php

namespace App\Http\Livewire\ReturnToVendor;

use Livewire\Component;
use Illuminate\Support\Facades\Session;

class Create extends Component
{
    public $products;

    public function addLine() {
        $this->products[] = [
            'sku_code' => '',
            'other_sku_code' => '',
            'description' => '',
            'uom' => '',
            'quantity' => '',
            'cost' => '',
        ];

        $this->updateSession();
    }

    public function removeLine($key) {
        unset($this->products[$key]);
        $this->updateSession();
    }

    public function updatedProducts() {
        $this->updateSession();
    }

    private function updateSession() {
        Session::put('rtv_products', $this->products);
    }

    public function mount() {
        $rtv_products = Session::get('rtv_products');
        if(!empty($rtv_products)) {
            foreach($rtv_products as $product) {
                $this->products[] = [
                    'sku_code' => $product['sku_code'],
                    'other_sku_code' => $product['other_sku_code'],
                    'description' => $product['description'],
                    'uom' => $product['uom'],
                    'quantity' => $product['quantity'],
                    'cost' => $product['cost'],
                ];
            }
        } else {
            $this->products[] = [
                'sku_code' => '',
                'other_sku_code' => '',
                'description' => '',
                'uom' => '',
                'quantity' => '',
                'cost' => '',
            ];
        }
    }

    public function render()
    {
        return view('livewire.return-to-vendor.create');
    }
}
