<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Illuminate\Support\Facades\DB;

use App\Http\Traits\GenerateMonthlyInventory;

use App\Models\Inventory;
use App\Models\InventoryUpload;
use App\Models\Account;

ini_set('memory_limit', '-1');
ini_set('max_execution_time', 0);
ini_set('sqlsrv.ClientBufferMaxKBSize','1000000'); // Setting to 512M
ini_set('pdo_sqlsrv.client_buffer_max_kb_size','1000000');

class InventoryImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use GenerateMonthlyInventory;

    public $inventory_data;
    public $account_id;
    public $account_branch_id;
    public $user_id;
    public $upload_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($inventory_data, $account_id, $account_branch_id, $user_id, $upload_id)
    {
        $this->inventory_data = $inventory_data;
        $this->account_id = $account_id;
        $this->account_branch_id = $account_branch_id;
        $this->user_id = $user_id;
        $this->upload_id = $upload_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $account = Account::findOrFail($this->account_id);
        \Config::set('database.connections.'.$account->db_data->connection_name, [
            'driver' => 'mysql',
            'url' => NULL,
            'host' => '127.0.0.1',
            'port' => 3306,
            'database' => $account->db_data->database_name,
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => '',
            'charset' => 'utf8',
            'collation' => 'utf8_general_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => 'InnoDB',
            'pool' => [
                'min_connections' => 1,
                'max_connections' => 10,
                'max_idle_time' => 30,
            ],
        ]);

        DB::setDefaultConnection($account->db_data->connection_name);

        if(!empty($this->inventory_data)) {
            $inventory_upload = InventoryUpload::find($this->upload_id);

            $total_inventory = 0;
            foreach($this->inventory_data as $data) {
                // check
                if($data['check'] == 0) {
                    $total_inventory += (int)$data['quantity'];

                    $inventory = new Inventory([
                        'account_id' => $this->account_id,
                        'account_branch_id' => $this->account_branch_id,
                        'inventory_upload_id' => $inventory_upload->id,
                        'location_id' => $data['location']['id'],
                        'product_id' => $data['product_id'],
                        'type' => $data['type'],
                        'uom' => $data['uom'],
                        'inventory' => $data['quantity'],
                        'expiry_date' => $data['expiry_date'] ?? null,
                    ]);
                    $inventory->save();
                }
            }

            $inventory_upload->update([
                'total_inventory' => $total_inventory
            ]);

            // generate monthly inventory
            $this->setMonthlyInventory($this->account_id, $this->account_branch_id, date('Y', strtotime($inventory_upload->date)), (int)date('m', strtotime($inventory_upload->date)));

            DB::setDefaultConnection('mysql');

            // logs
            activity('upload')
                ->performedOn($inventory_upload)
                ->log(':causer.name has uploaded inventory data.');

        }
    }
}
