<?php

namespace App\Http\Livewire\Customer;

use Livewire\Component;

use App\Models\Customer;

use Illuminate\Support\Facades\Session;

class UploadProgress extends Component
{
    public $upload_data;
    public $total;
    public $count;
    public $account;
    public $account_branch;

    public function checkProgress() {
        $count = Customer::where('account_id', $this->account->id)
            ->where('account_branch_id', $this->account_branch->id)
            ->count();

        $this->count = $count - $this->upload_data['start'];
    }

    public function mount($upload_data) {
        $this->upload_data = $upload_data;
        $this->total = $upload_data['total'];

        $this->account = Session::get('account');
        $this->account_branch = Session::get('account_branch');
    }

    public function render()
    {
        $this->checkProgress();

        return view('livewire.customer.upload-progress');
    }
}
