<?php

namespace App\Http\Livewire\Inventory;

use Livewire\Component;

use App\Models\Inventory;

use Illuminate\Support\Facades\Session;

class UploadProgress extends Component
{
    public $upload_data;
    public $total;
    public $count;

    public function checkProgress() {
        $count = Inventory::where('account_id', $this->account->id)
            ->where('account_branch_id', $this->account_branch->id)
            ->where('inventory_upload_id', $this->upload_data['upload_id'])
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

        return view('livewire.inventory.upload-progress');
    }
}
