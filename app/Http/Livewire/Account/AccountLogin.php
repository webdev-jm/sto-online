<?php

namespace App\Http\Livewire\Account;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class AccountLogin extends Component
{
    public $password;

    public function mount() {
        
    }

    public function render()
    {
        return view('livewire.account.account-login');
    }
}
