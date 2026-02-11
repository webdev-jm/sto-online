<?php

namespace App\Http\Livewire\Sales;

use Livewire\Component;
use App\Models\Salesman;
use App\Models\Customer;
use App\Models\Barangay;
use App\Models\Municipality;
use App\Models\Province;
use App\Models\Channel;

use Illuminate\Support\Facades\Session;

class CustomerMaintenance extends Component
{
    public $account;
    public $account_branch;
    public $salesmen;
    public $channels;
    public $provinces;
    public $cities;
    public $barangays;
    
    public $customer_code;
    public $customer_name;
    public $customer_address;
    public $salesman_id;
    public $channel_id;
    public $province_id;
    public $city_id;
    public $barangay_id;
    public $street;
    public $postal_code;

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
            ],
            'channel_id' => [
                'required'
            ],
            'province_id' => [
                'required'
            ],
            'city_id' => [
                'required'
            ],
            'barangay_id' => [
                'required'
            ],
            'street' => [
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
            'channel_id' => $this->channel_id,
            'province_id' => $this->province_id,
            'municipality_id' => $this->city_id,
            'barangay_id' => $this->barangay_id,
            'street' => $this->street,
            'brgy' => $this->barangays[$this->barangay_id] ?? NULL,
            'city' => $this->cities[$this->city_id] ?? NULL,
            'province' => $this->provinces[$this->province_id] ?? NULL,
            'postal_code' => $this->postal_code ?? NULL
        ]);
        $customer->save();

        $this->dispatch('checkData');

        $this->dispatch('closeCustomerModal');

        $this->reset([
            'customer_code',
            'customer_name',
            'customer_address',
            'salesman_id',
            'channel_id',
            'province_id',
            'city_id',
            'barangay_id',
            'street',
            'postal_code',
        ]);
    }

    public function maintainCustomer($customer_code) {
        $this->customer_code = $customer_code;
    }

    public function updatedProvinceId() {
        if(!empty($this->province_id)) {
            $cities = Municipality::where('province_id', $this->province_id)
                ->get();
            $this->cities = array();
            foreach($cities as $city) {
                $this->cities[$city->id] = $city->municipality_name;
            }

            $this->reset(['city_id', 'barangay_id']);
        }
    }

    public function updatedCityId() {
        if(!empty($this->city_id)) {
            $barangays = Barangay::where('municipality_id', $this->city_id)
                ->get();
            $this->barangays = array();
            foreach($barangays as $barangay) {
                $this->barangays[$barangay->id] = $barangay->barangay_name;
            }
            
            $this->reset('barangay_id');
        }
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

        $provinces = Province::orderBy('province_name', 'ASC')->get();
        $this->provinces = array();
        foreach($provinces as $province) {
            $this->provinces[$province->id] = $province->province_name;
        }

        $channels = Channel::get();
        $this->channels = array();
        foreach($channels as $channel) {
            $this->channels[$channel->id] = '['.$channel->code.'] '.$channel->name;
        }
    }

    public function render()
    {
        return view('livewire.sales.customer-maintenance');
    }
}
