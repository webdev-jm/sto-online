<?php

namespace App\Http\Livewire\Salesman;

use Livewire\Component;
use Livewire\WithPagination;

class Customers extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $salesman;
    public $customer_count;

    public function mount($salesman) {
        $this->salesman = $salesman;
        $this->customer_count = $this->salesman->customers()->count();
    }

    public function render()
    {
        $customers = $this->salesman->customers()
            ->paginate(10, ['*'], 'customer-page')
            ->onEachSide(1);

        return view('livewire.salesman.customers')->with([
            'customers' => $customers
        ]);
    }
}
