<?php

namespace App\Http\Livewire\Sales;

use Livewire\Component;
use Livewire\WithPagination;

use App\Exports\SalesLineExport;
use Maatwebsite\Excel\Facades\Excel;

class ProductsView extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $sales_upload;
    public $account;

    public function mount($sales_upload) {
        $this->sales_upload = $sales_upload;
    }

    public function export() {
        return Excel::download(new SalesLineExport($this->sales_upload), 'STO Sales-'.time().'.xlsx');
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
