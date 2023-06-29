<?php

namespace App\Http\Livewire\Customer;

use Livewire\Component;
use Livewire\WithPagination;

use App\Models\Sale;

use Illuminate\Support\Facades\Session;

class SalesDetails extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $customer;
    public $search;

    public $account;
    public $account_branch;

    public function updatedSearch() {
        $this->resetPage('sales-page');
    }

    public function mount($customer) {
        $this->customer = $customer;

        $this->account = Session::get('account');
        $this->account_branch = Session::get('account_branch');
    }

    public function render()
    {
        $sales = Sale::orderBy('date', 'DESC')
            ->with(['customer', 'product', 'salesman', 'location'])
            ->where('customer_id', $this->customer->id)
            ->where('account_id', $this->account->id)
            ->where('account_branch_id', $this->account_branch->id)
            ->when(!empty($this->search), function($query) {
                $query->where('date', 'like', '%'.$this->search.'%')
                    ->orWhere('document_number', 'like', '%'.$this->search.'%')
                    ->orWhere('uom', 'like', '%'.$this->search.'%')
                    ->orWhereHas('salesman', function($qry) {
                        $qry->where('code', 'like', '%'.$this->search.'%')
                            ->orWhere('name', 'like', '%'.$this->search.'%');
                    })
                    // ->orWhereHas('product', function($qry) {
                    //     $qry->where('stock_code', 'like', '%'.$this->search.'%')
                    //         ->orWhere('description', 'like', '%'.$this->search.'%');
                    // })
                    ->orWhereHas('location', function($qry) {
                        $qry->where('code', 'like', '%'.$this->search.'%')
                            ->orWhere('name', 'like', '%'.$this->search.'%');
                    });
            })
            ->paginate(10, ['*'], 'sales-page')
            ->onEachSide(1);

        return view('livewire.customer.sales-details')->with([
            'sales' => $sales
        ]);
    }
}
