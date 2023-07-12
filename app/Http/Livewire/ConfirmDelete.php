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
use App\Models\SalesUpload;
use App\Models\InventoryUpload;
use App\Models\Channel;

use Spatie\Permission\Models\Role;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

use App\Http\Traits\GenerateMonthlyInventory;

class ConfirmDelete extends Component
{
    use GenerateMonthlyInventory;

    public $password;
    public $error_message;
    public $model;
    public $name;
    public $route;

    public $account;
    public $account_branch;

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
            

            // delete related data
            if($this->route == '/sales') {
                $dates = $this->model->sales()->select('date')->distinct()->pluck('date')->toArray();

                DB::statement("SET SQL_SAFE_UPDATES = 0;");
                $this->model->sales()->delete();
                $this->model->delete();
                DB::statement("SET SQL_SAFE_UPDATES = 1;");

                $dates_arr = array();
                foreach($dates as $date) {
                    $year = date('Y', strtotime($date));
                    $month = date('n', strtotime($date));

                    $dates_arr[$year][$month] = $date;
                }

                foreach($dates_arr as $year => $months) {
                    foreach($months as $month => $date) {
                        DB::statement('CALL generate_sales_report(?, ?, ?, ?)', [$this->account->id, $this->account_branch->id, $year, $month]);
                    }
                }
                
            } else if($this->route == '/inventory') { // Inventory
                DB::statement("SET SQL_SAFE_UPDATES = 0;");
                $this->model->inventories()->delete();
                $date = $this->model->date;
                $this->model->delete();
                DB::statement("SET SQL_SAFE_UPDATES = 1;");

                $year = date('Y', strtotime($date));
                $month = date('n', strtotime($date));

                $this->setMonthlyInventory($this->account->id, $this->account_branch->id, $year, $month);

            } else {
                $this->model->delete();
            }

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
            case 'SalesUpload':
                $this->model = SalesUpload::findOrFail(decrypt($model_id));
                $this->name = 'sales upload '.$this->model->user->name.' sku: '.$this->model->sku_count;
                $this->route = '/sales';
                break;
            case 'InventoryUpload':
                $this->model = InventoryUpload::findOrFail(decrypt($model_id));
                $this->name = 'inventory upload '.$this->model->user->name.' inventory: '.$this->model->total_inventory;
                $this->route = '/inventory';
                break;
            case 'Channel':
                $this->model = Channel::findOrFail(decrypt($model_id));
                $this->name = 'Channel '.$this->model->name;
                $this->route = '/channel';
                break;
        }
    }

    public function mount() {
        $this->account = Session::get('account');
        $this->account_branch = Session::get('account_branch');
    }

    public function render()
    {
        return view('livewire.confirm-delete');
    }
}
