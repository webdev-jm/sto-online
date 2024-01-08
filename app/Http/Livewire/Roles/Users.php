<?php

namespace App\Http\Livewire\Roles;

use Livewire\Component;
use Livewire\WithPagination;

class Users extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $role;

    public function mount($role) {
        $this->role = $role;
    }

    public function render()
    {
        $users = $this->role->users()
            ->paginate(12, ['*'], 'role-users-page')
            ->onEachSide(1);

        return view('livewire.roles.users')->with([
            'users' => $users
        ]);
    }
}
