<?php

namespace App\Http\Livewire\Sales;

use Livewire\Component;
use Livewire\WithPagination;

class ProductsView extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $sales_upload;
    public $account;

    public function mount($sales_upload) {
        $this->sales_upload = $sales_upload;
    }

    public function render()
    {
        $sales = $this->sales_upload->sales()
            ->with(['salesman', 'location', 'product', 'customer'])
            ->paginate(15)->onEachSide(1);

        return view('livewire.sales.products-view')->with([
            'sales' => $sales
        ]);
    }
}
