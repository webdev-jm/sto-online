<?php

namespace App\Http\Livewire\PurchaseOrder;

use Livewire\Component;

use Illuminate\Support\Facades\Session;

class Filter extends Component
{
    public $account_branch;
    public $show_filter;
    public $filters;

    public function applyFilter() {
        Session::put('po_filters', $this->filters);
        return redirect()->route('purchase-order.index');
    }

    public function clearFilter() {
        $this->reset('filters');
        Session::forget('po_filters');
        return redirect()->route('purchase-order.index');
    }

    public function showFilter() {
        if($this->show_filter) {
            $this->show_filter = false;
        } else {
            $this->show_filter = true;
        }
    }

    public function mount($account_branch) {
        $this->account_branch = $account_branch;
        $this->show_filter = false;
        if(session('po_filters')) {
            $this->filters = session('po_filters');
        }
    }

    public function render()
    {
        return view('livewire.purchase-order.filter');
    }
}
