<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Http\Traits\GenerateMonthlyInventory;

use App\Models\Inventory;
use App\Models\InventoryUpload;

class InventoryImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use GenerateMonthlyInventory;

    public $inventory_data;
    public $account_id;
    public $account_branch_id;
    public $user_id;
    public $upload_id;
    public $keys;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($inventory_data, $account_id, $account_branch_id, $user_id, $upload_id, $keys)
    {
        $this->inventory_data = $inventory_data;
        $this->account_id = $account_id;
        $this->account_branch_id = $account_branch_id;
        $this->user_id = $user_id;
        $this->upload_id = $upload_id;
        $this->keys = $keys;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if(!empty($this->inventory_data)) {
            $inventory_upload = InventoryUpload::find($this->upload_id);

            $total_inventory = 0;
            foreach($this->inventory_data as $data) {

                // check
                if($data['check'] == 0) {
                    foreach($this->keys as $key => $location) {
                        $total_inventory += $data[$location['id']];

                        $inventory = new Inventory([
                            'account_id' => $this->account_id,
                            'account_branch_id' => $this->account_branch_id,
                            'inventory_upload_id' => $inventory_upload->id,
                            'location_id' => $location['id'],
                            'product_id' => $data['product_id'],
                            'type' => $data['type'],
                            'uom' => $data['uom'],
                            'inventory' => $data[$location['id']],
                        ]);
                        $inventory->save();
                    }
                }
            }

            $inventory_upload->update([
                'total_inventory' => $total_inventory
            ]);

            // logs
            activity('upload')
            ->performedOn($inventory_upload)
            ->log(':causer.name has uploaded inventory data.');

            // generate monthly inventory
            $this->setMonthlyInventory($this->account_id, $this->account_branch_id, date('Y'), (int)date('m'));
        }
    }
}
