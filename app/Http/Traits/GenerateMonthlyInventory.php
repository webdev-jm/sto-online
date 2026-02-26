<?php

namespace App\Http\Traits;

use App\Models\InventoryUpload;
use App\Models\MonthlyInventory;

use Illuminate\Support\Facades\DB;

use App\Http\Traits\GenerateInventoryExpiry;

trait GenerateMonthlyInventory {
    use GenerateInventoryExpiry;

    public function setMonthlyInventory($account_id, $account_branch_id, $year, $month) {
        $inventory_upload = InventoryUpload::with('inventories')
            ->where('account_id', $account_id)
            ->where('account_branch_id', $account_branch_id)
            ->where(DB::raw('YEAR(date)'), $year)
            ->where(DB::raw('MONTH(date)'), $month)
            ->orderBy('date', 'DESC')
            ->first();

        if(!empty($inventory_upload)) {

            foreach($inventory_upload->inventories as $inventory) {
                // check
                $monthly_inventory = MonthlyInventory::where('account_id', $account_id)
                    ->where('account_branch_id', $account_branch_id)
                    ->where('inventory_id', $inventory->id)
                    ->where('product_id', $inventory->product_id)
                    ->where('type', $inventory->type)
                    ->where('year', $year)
                    ->where('month', $month)
                    ->first();

                if(!empty($monthly_inventory)) { // update
                    $monthly_inventory->update([
                        'account_id' => $account_id,
                        'account_branch_id' => $account_branch_id,
                        'location_id' => $inventory->location_id,
                        'product_id' => $inventory->product_id,
                        'inventory_id' => $inventory->id,
                        'year' => $year,
                        'month' => $month,
                        'type' => $inventory->type,
                        'uom' => $inventory->uom,
                        'total' => $inventory->inventory,
                    ]);
                } else { // create new
                    $monthly_inventory = new MonthlyInventory([
                        'account_id' => $account_id,
                        'account_branch_id' => $account_branch_id,
                        'location_id' => $inventory->location_id,
                        'product_id' => $inventory->product_id,
                        'inventory_id' => $inventory->id,
                        'year' => $year,
                        'month' => $month,
                        'type' => $inventory->type,
                        'uom' => $inventory->uom,
                        'total' => $inventory->inventory,
                    ]);
                    $monthly_inventory->save();
                }
            }

        }

        $this->generateInventoryExpiry($account_id, $account_branch_id, $year, $month);
    }

}
