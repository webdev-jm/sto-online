<?php

namespace App\Http\Livewire\Account;

use Livewire\Component;
use Livewire\WithPagination;

use App\Models\AccountUploadTemplate;

class AccountTemplate extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $account;

    public function mount($account) {
        $this->account = $account;
    }

    public function render()
    {
        $account_templates = AccountUploadTemplate::where('account_id', $this->account->id)
            ->paginate(10, ['*'], 'template-page')
            ->onEachSide(1);

        return view('livewire.account.account-template')->with([
            'account_templates' => $account_templates
        ]);
    }
}
