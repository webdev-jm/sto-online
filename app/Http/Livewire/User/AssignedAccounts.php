<?php

namespace App\Http\Livewire\User;

use Livewire\Component;
use Livewire\WithPagination;

use App\Models\SMSAccount;

use Illuminate\Support\Facades\DB;

class AssignedAccounts extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $user;
    public $selected;
    public $search;

    public function updatedSearch() {
        $this->resetPage('account-page');
    }

    public function assign() {
        $this->user->accounts()->sync($this->selected);
    }

    public function selectAll() {
        $accounts = SMSAccount::orderBy('account_code')
            ->when(!empty($this->search), function($query) {
                $query->where('account_code', 'like', '%'.$this->search.'%')
                    ->orWhere('short_name', 'like', '%'.$this->search.'%');
            })
            ->get();
        
        $this->reset('selected');

        foreach($accounts as $account) {
            $this->selected[] = $account->id;
        }
    }

    public function clear() {
        $this->reset('selected');
    }

    public function selectAccount($account_id) {
        $account_id = decrypt($account_id);
        if(!empty($this->selected) && in_array($account_id, $this->selected)) {
            unset($this->selected[array_search($account_id, $this->selected)]);
        } else {
            $this->selected[] = $account_id;
        }
    }

    public function mount($user) {
        $this->user = $user;
    
        // get selected account
        $accounts = DB::connection('mysql')
            ->table('account_user as au')
            ->join(env('DB_DATABASE_2').'.accounts as a', 'a.id', '=', 'au.account_id')
            ->where('au.user_id', $this->user->id)
            ->get();

        foreach($accounts as $account) {
            $this->selected[] = $account->id;
        }
    }

    public function render()
    {
        $accounts = SMSAccount::orderBy('account_code')
            ->when(!empty($this->search), function($query) {
                $query->where('account_code', 'like', '%'.$this->search.'%')
                    ->orWhere('short_name', 'like', '%'.$this->search.'%');
            })
            ->paginate(16, ['*'], 'account-page')->onEachSide(1);

        return view('livewire.user.assigned-accounts')->with([
            'accounts' => $accounts
        ]);
    }
}
