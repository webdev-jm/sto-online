<?php

namespace App\Http\Traits;

use App\Models\Account;
use Illuminate\Support\Facades\DB;
use App\Http\Traits\GenerateInventoryExpiry;

trait GenerateMonthlyInventory {
    use GenerateInventoryExpiry;

    public function setMonthlyInventory($account_id, $account_branch_id, $year, $month) {
        $account = Account::find($account_id);
        $connection = $account->db_data->connection_name;

        $db = DB::connection($connection);

        $inventory_upload = $db->table('inventory_uploads')
            ->where('account_id', $account_id)
            ->where('account_branch_id', $account_branch_id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->orderBy('date', 'DESC')
            ->first();

        if (!empty($inventory_upload)) {

            $inventories = $db->table('inventories')
                ->where('inventory_upload_id', $inventory_upload->id)
                ->get();

            foreach ($inventories as $inventory) {

                $monthly_inventory = $db->table('monthly_inventories')
                    ->where('account_id', $account_id)
                    ->where('account_branch_id', $account_branch_id)
                    ->where('inventory_id', $inventory->id)
                    ->where('product_id', $inventory->product_id)
                    ->where('type', $inventory->type)
                    ->where('year', $year)
                    ->where('month', $month)
                    ->first();

                $data = [
                    'account_id'        => $account_id,
                    'account_branch_id' => $account_branch_id,
                    'location_id'       => $inventory->location_id,
                    'product_id'        => $inventory->product_id,
                    'inventory_id'      => $inventory->id,
                    'year'              => $year,
                    'month'             => $month,
                    'type'              => $inventory->type,
                    'uom'               => $inventory->uom,
                    'total'             => $inventory->inventory,
                    'created_at'        => now(),
                    'updated_at'        => now()
                ];

                if (!empty($monthly_inventory)) { // update
                    $db->table('monthly_inventories')
                        ->where('id', $monthly_inventory->id)
                        ->update($data);
                } else { // create
                    $db->table('monthly_inventories')
                        ->insert($data);
                }
            }
        }

        $this->generateInventoryExpiry($account_id, $account_branch_id, $year, $month);
    }
}
