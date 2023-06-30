<?php

namespace App\Http\Livewire\Location;

use Livewire\Component;
use Livewire\WithPagination;

use App\Models\MonthlyInventory;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class Inventories extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $location;
    public $account;
    public $account_branch;

    public $search;
    public $year;
    public $month;

    public function updatedSearch() {
        $this->resetPage('inventory-page');
    }

    public function updatedYear() {
        $this->resetPage('inventory-page');
    }

    public function updatedMonth() {
        $this->resetPage('inventory-page');
    }

    public function mount($location) {
        $this->location = $location;
        $this->account = Session::get('account');
        $this->account_branch = Session::get('account_branch');

        $this->year = date('Y');
        $this->month = (int)date('m');
    }

    public function render()
    {
        $this->search = trim($this->search);
        
        $inventories = MonthlyInventory::with(['inventory', 'product'])
            ->where('account_id', $this->account->id)
            ->where('account_branch_id', $this->account_branch->id)
            ->where('location_id', $this->location->id)
            ->where('year', $this->year)
            ->where('month', $this->month)
            ->when(!empty($this->search), function($query) {
                $query->whereIn('product_id',
                    DB::connection('sms_db')
                        ->table('products')
                        ->select('id')
                        ->where('stock_code', 'like', '%'.$this->search.'%')
                        ->orWhere('description', 'like', '%'.$this->search.'%')
                        ->orWhere('size', 'like', '%'.$this->search.'%')
                        ->pluck('id')->toArray()
                )
                ->orWhere('uom', 'like', '%'.$this->search.'%');
            })
            ->paginate(10, ['*'], 'inventory-page')
            ->onEachSide(1);

        return view('livewire.location.inventories')->with([
            'inventories' => $inventories
        ]);
    }
}
