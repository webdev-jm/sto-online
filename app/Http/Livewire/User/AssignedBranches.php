<?php

namespace App\Http\Livewire\User;

use Livewire\Component;
use Livewire\WithPagination;

use App\Models\AccountBranch;

use Illuminate\Support\Facades\DB;

class AssignedBranches extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $user;
    public $selected;
    public $search;

    public $form_message;

    public function updatedSearch() {
        $this->resetPage('branch-page');
    }

    public function assign() {
        $this->user->account_branches()->sync($this->selected);

        $this->form_message = 'Accounts has been assigned.';
    }

    public function selectAll() {
        $branches = AccountBranch::orderBy('account_id')
            ->when(!empty($this->search), function($query) {
                $query->where('code', 'like', '%'.$this->search.'%')
                    ->orWhere('name', 'like', '%'.$this->search.'%')
                    ->orWhereHas('account', function($qry) {
                        $qry->where('account_code', 'like', '%'.$this->search.'%')
                            ->where('short_name', 'like', '%'.$this->search.'%');
                    });
            })
            ->get();
        
        $this->reset('selected');

        foreach($branches as $branch) {
            $this->selected[] = $branch->id;
        }
    }

    public function clear() {
        $this->reset('selected');
    }

    public function selectBranch($branch_id) {
        $branch_id = decrypt($branch_id);
        if(!empty($this->selected) && in_array($branch_id, $this->selected)) {
            unset($this->selected[array_search($branch_id, $this->selected)]);
        } else {
            $this->selected[] = $branch_id;
        }
    }

    public function mount($user) {
        $this->user = $user;
    
        // get selected account
        foreach($this->user->account_branches as $branch) {
            $this->selected[] = $branch->id;
        }
    }

    public function render()
    {
        $account_ids = auth()->user()->accounts()->pluck('id')->toArray();

        $branches = AccountBranch::orderBy('account_id')
            ->whereIn('account_id', $account_ids)
            ->when(!empty($this->search), function($query) {
                $query->where('code', 'like', '%'.$this->search.'%')
                    ->orWhere('name', 'like', '%'.$this->search.'%')
                    ->orWhereHas('account', function($qry) {
                        $qry->where('account_code', 'like', '%'.$this->search.'%')
                            ->where('short_name', 'like', '%'.$this->search.'%');
                    });
            })
            ->paginate(10, ['*'], 'branh-page')->onEachSide(1);

        return view('livewire.user.assigned-branches')->with([
            'branches' => $branches
        ]);
    }
}
