<?php

namespace App\Http\Livewire\Sales;

use Livewire\Component;
use App\Models\Location;

use Illuminate\Support\Facades\Session;

class LocationMaintenance extends Component
{
    public $account;
    
    public $location_code;
    public $location_name;

    protected $listeners = [
        'maintainLocation' => 'maintainLocation',
    ];

    public function saveLocation() {
        $this->validate([
            'location_code' => [
                'required'
            ],
            'location_name' => [
                'required'
            ],
        ]);

        $location = new Location([
            'account_id' => $this->account->id,
            'code' => $this->location_code,
            'name' => $this->location_name,
        ]);
        $location->save();

        $this->emit('checkData');

        $this->dispatchBrowserEvent('closeLocationModal');

        $this->reset([
            'location_code',
            'location_name',
        ]);
    }

    public function maintainLocation($location_code) {
        $this->location_code = $location_code;
    }

    public function mount() {
        $this->account = Session::get('account');
    }

    public function render()
    {
        return view('livewire.sales.location-maintenance');
    }
}
