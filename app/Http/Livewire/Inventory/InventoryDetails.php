<?php

namespace App\Http\Livewire\Inventory;

use Livewire\Component;
use Livewire\WithPagination;

use App\Models\Inventory;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class InventoryDetails extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $inventory_upload;
    public $account;
    public $type;

    public function editLine($product_id) {
        
    }

    public function mount($inventory_upload, $type) {
        $this->inventory_upload = $inventory_upload;
        $this->type = $type;

        $this->account = Session::get('account');
    }

    public function render()
    {
        $location_ids = Inventory::select('location_id')->distinct()
            ->where('account_id', $this->account->id)
            ->where('inventory_upload_id', $this->inventory_upload->id)
            ->get();

        DB::statement("SET sql_mode = ''");

        $inventories = DB::table('inventories as i')
            ->select(
                'i.product_id',
                'p.stock_code',
                'p.description',
                'i.uom'
            );

        foreach($location_ids as $location_id) {
            $inventories->selectRaw('SUM(IF(l.id = ?, i.inventory, NULL)) as location_'.$location_id->location_id, [$location_id->location_id]);
        }
        $inventories->leftJoin('inventory_uploads as iu', 'iu.id', '=', 'i.inventory_upload_id')
            ->leftJoin('locations as l', 'l.id', '=', 'i.location_id')
            ->leftJoin(env('DB_DATABASE_2').'.products as p', 'p.id', '=', 'i.product_id')
            ->where('i.account_id', $this->account->id)
            ->where('i.inventory_upload_id', $this->inventory_upload->id)
            ->whereNull('i.deleted_at')
            ->groupBy('p.stock_code');

        $inventories = $inventories->paginate(15, ['*'], 'inventory-page');

        return view('livewire.inventory.inventory-details')->with([
            'inventories' => $inventories,
            'location_ids' => $location_ids,
        ]);
    }
}
