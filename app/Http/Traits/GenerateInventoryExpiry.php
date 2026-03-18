<?php

namespace App\Http\Traits;

use App\Models\Account;
use Illuminate\Support\Facades\DB;

trait GenerateInventoryExpiry {

    public function generateInventoryExpiry($account_id, $account_branch_id, $year, $month) {
        $account = Account::find($account_id);
        $connection = $account->db_data->connection_name;

        $db = DB::connection($connection);

        $inventories = $db->table('inventories')
            ->join('inventory_uploads', 'inventories.inventory_upload_id', '=', 'inventory_uploads.id')
            ->where('inventories.account_id', $account_id)
            ->where('inventories.account_branch_id', $account_branch_id)
            ->whereYear('inventory_uploads.date', $year)
            ->whereMonth('inventory_uploads.date', $month)
            ->whereNotNull('inventories.expiry_date')
            ->select('inventories.*')
            ->get();

        foreach ($inventories as $inventory) {

            $inventory_expiry = $db->table('inventory_expiries')
                ->where('account_id', $account_id)
                ->where('account_branch_id', $account_branch_id)
                ->where('inventory_id', $inventory->id)
                ->where('product_id', $inventory->product_id)
                ->where('location_id', $inventory->location_id)
                ->where('expiry_date', $inventory->expiry_date)
                ->first();

            if (!$inventory_expiry) {
                $db->table('inventory_expiries')
                    ->insert([
                        'account_id'        => $account_id,
                        'account_branch_id' => $account_branch_id,
                        'inventory_id'      => $inventory->id,
                        'product_id'        => $inventory->product_id,
                        'location_id'       => $inventory->location_id,
                        'quantity'          => $inventory->inventory,
                        'expiry_date'       => $inventory->expiry_date,
                    ]);
            } else {
                $db->table('inventory_expiries')
                    ->where('id', $inventory_expiry->id)
                    ->update([
                        'quantity' => $inventory->inventory,
                    ]);
            }
        }
    }
}
