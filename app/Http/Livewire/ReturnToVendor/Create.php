<?php

namespace App\Http\Livewire\ReturnToVendor;

use Livewire\Component;

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
    }

    public function removeLine($key) {
        unset($this->products[$key]);
    }

    public function mount() {
        $this->products[] = [
            'sku_code' => '',
            'other_sku_code' => '',
            'description' => '',
            'uom' => '',
            'quantity' => '',
            'cost' => '',
        ];
    }

    public function render()
    {
        return view('livewire.return-to-vendor.create');
    }
}
