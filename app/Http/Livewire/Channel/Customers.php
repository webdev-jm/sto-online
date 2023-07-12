<?php

namespace App\Http\Livewire\Channel;

use Livewire\Component;
use App\Models\Customer;
use Illuminate\Support\Facades\Session;

use Livewire\WithPagination;

class Customers extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $channel;
    public $search;

    public $account, $account_branch;

    public function updatedSearch() {
        $this->resetPage('customer-page');
    }

    public function mount($channel) {
        $this->channel = $channel;

        $this->account = Session::get('account');
        $this->account_branch = Session::get('account_branch');
    }

    public function render()
    {
        $customers = Customer::where('account_id', $this->account->id)
            ->where('account_branch_id', $this->account_branch->id)
            ->where('channel_id', $this->channel->id)
            ->when(!empty($this->search), function($query) {
                $query->where(function($qry) {
                    $qry->where('code', 'like', '%'.$this->search.'%')
                        ->orWhere('name', 'like', '%'.$this->search.'%');
                });
            })
            ->paginate(10, ['*'], 'customer-page')->onEachSide(1);

        return view('livewire.channel.customers')->with([
            'customers' => $customers
        ]);
    }
}
