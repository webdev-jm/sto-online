<?php

namespace App\Http\Livewire\Sales;

use Livewire\Component;
use App\Models\Salesman;
use App\Models\Customer;

use Illuminate\Support\Facades\Session;

class CustomerMaintenance extends Component
{
    public $account;
    public $account_branch;
    public $salesmen;
    
    public $customer_code;
    public $customer_name;
    public $customer_address;
    public $salesman_id;

    protected $listeners = [
        'maintainCustomer' => 'maintainCustomer',
    ];

    public function saveCustomer() {
        $this->validate([
            'customer_code' => [
                'required'
            ],
            'customer_name' => [
                'required'
            ],
            'customer_address' => [
                'required'
            ],
            'salesman_id' => [
                'required'
            ]
        ]);

        $customer = new Customer([
            'account_id' => $this->account->id,
            'account_branch_id' => $this->account_branch->id,
            'code' => $this->customer_code,
            'name' => $this->customer_name,
            'address' => $this->customer_address,
            'salesman_id' => $this->salesman_id,
        ]);
        $customer->save();

        $this->emit('checkData');

        $this->dispatchBrowserEvent('closeCustomerModal');

        $this->reset([
            'customer_code',
            'customer_name',
            'customer_address',
            'salesman_id',
        ]);
    }

    public function maintainCustomer($customer_code) {
        $this->customer_code = $customer_code;
    }

    public function mount() {
        $this->account = Session::get('account');
        $this->account_branch = Session::get('account_branch');
        // salesman options
        $salesmen = Salesman::where('account_id', $this->account->id)
            ->where('account_branch_id', $this->account_branch->id)
            ->get();
        foreach($salesmen as $salesman) {
            $this->salesmen[$salesman->id] = '['.$salesman->code.'] '.$salesman->name;
        }
        
    }

    public function render()
    {
        return view('livewire.sales.customer-maintenance');
    }
}
