<?php

namespace App\Http\Livewire\UboJob;

use Livewire\Component;
use Livewire\WithPagination;

use App\Models\AccountBranch;

use App\Jobs\CustomerUboJob;

class Run extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $account_id;
    public $branch_id;
    public $search;

    protected $listeners = [
        'selectAccount' => 'setAccount'
    ];

    public function setAccount($account_id) {
        $this->reset('branch_id');
        $this->account_id = $account_id;
    }

    public function updatedSearch() {
        $this->resetPage('branch-page');
    }

    public function selectBranch($branch_id) {
        $this->branch_id = decrypt($branch_id);
    }
    
    public function runJob() {
        if(!empty($this->branch_id) && !empty($this->account_id)) {
            // dd($this->account_id.' - '.$this->branch_id);
            CustomerUboJob::dispatch($this->account_id, $this->branch_id);
        }

        return redirect()->route('ubo-job.index');
    }

    public function render()
    {
        $branches = AccountBranch::where('account_id', $this->account_id)
            ->when(!empty($this->search), function($query) {
                $query->where(function($qry) {
                    $qry->where('code', 'like', '%'.$this->search.'%')
                        ->orWhere('name', 'like', '%'.$this->search.'%');
                });
            })
            ->paginate(12, ['*'], 'branch-page')
            ->onEachSide(1);

        return view('livewire.ubo-job.run')->with([
            'branches' => $branches
        ]);
    }
}
