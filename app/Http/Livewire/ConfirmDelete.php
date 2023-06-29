<?php

namespace App\Http\Livewire;

use Livewire\Component;

use Illuminate\Support\Facades\Hash;

use App\Models\Customer;
use App\Models\Location;
use App\Models\Area;
use App\Models\Salesman;
use App\Models\User;
use App\Models\AccountBranch;

use Spatie\Permission\Models\Role;

class ConfirmDelete extends Component
{
    public $password;
    public $error_message;
    public $model;
    public $name;
    public $route;

    protected $listeners = [
        'setDeleteModel' => 'setModel'
    ];

    public function submitForm() {
        $this->error_message = '';

        $this->validate([
            'password' => 'required'
        ]);

        // check password
        if(!Hash::check($this->password, auth()->user()->password)) { // invalid
            $this->error_message = 'incorrect password.';
        } else { // delete function
            $this->model->delete();

            activity('delete')
            ->performedOn($this->model)
            ->withProperties($this->model)
            ->log(':causer.name has deleted '.$this->name);

            return redirect()->to($this->route)->with([
                'message_success' => $this->name.' was deleted.'
            ]);
        }

    }

    public function setModel($type, $model_id) {
        switch($type) {
            case 'Customer':
                $this->model = Customer::findOrFail(decrypt($model_id));
                $this->name = 'customer '.$this->model->name;
                $this->route = '/customer';
                break;
            case 'Location':
                $this->model = Location::findOrFail(decrypt($model_id));
                $this->name = 'location '.$this->model->name;
                $this->route = '/location';
                break;
            case 'Area':
                $this->model = Area::findOrFail(decrypt($model_id));
                $this->name = 'area '.$this->model->name;
                $this->route = '/area';
                break;
            case 'Salesman':
                $this->model = Salesman::findOrFail(decrypt($model_id));
                $this->name = 'salesman '.$this->model->name;
                $this->route = '/salesman';
                break;
            case 'User':
                $this->model = User::findOrFail(decrypt($model_id));
                $this->name = 'user '.$this->model->name;
                $this->route = '/user';
                break;
            case 'Role':
                $this->model = Role::findOrFail(decrypt($model_id));
                $this->name = 'role '.$this->model->name;
                $this->route = '/role';
                break;
            case 'AccountBranch':
                $this->model = AccountBranch::findOrFail(decrypt($model_id));
                $this->name = 'branch '.$this->model->name;
                $this->route = '/account-branch';
                break;
        }
    }

    public function render()
    {
        return view('livewire.confirm-delete');
    }
}
