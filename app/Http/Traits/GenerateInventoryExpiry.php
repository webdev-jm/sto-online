<?php

namespace App\Http\Traits;

use App\Models\Inventory;
use App\Models\InventoryExpiry;

use Illuminate\Support\Facades\DB;

trait GenerateInventoryExpiry {

    public function generateInventoryExpiry($account_id, $account_branch_id, $year, $month) {
        $inventories = Inventory::where('account_id', $account_id)
            ->where('account_branch_id', $account_branch_id)
            ->whereHas('inventory_upload', function($query) use ($year, $month) {
                $query->where(DB::raw('YEAR(date)'), $year)
                    ->where(DB::raw('MONTH(date)'), $month);
            })
            ->where('expiry_date', '!=', null)
            ->get();

        foreach($inventories as $inventory) {
            // check in expiry table
            $inventory_expiry = InventoryExpiry::where('account_id', $account_id)
                ->where('account_branch_id', $account_branch_id)
                ->where('inventory_id', $inventory->id)
                ->where('product_id', $inventory->product_id)
                ->where('location_id', $inventory->location_id)
                ->where('expiry_date', $inventory->expiry_date)
                ->first();
            if (!$inventory_expiry) {
                // Create new expiry record if not exists
                InventoryExpiry::create([
                    'account_id' => $account_id,
                    'account_branch_id' => $account_branch_id,
                    'inventory_id' => $inventory->id,
                    'product_id' => $inventory->product_id,
                    'location_id' => $inventory->location_id,
                    'quantity' => $inventory->inventory,
                    'expiry_date' => $inventory->expiry_date
                ]);
            } else {
                // Update existing expiry record if exists
                $inventory_expiry->update([
                    'quantity' => $inventory->inventory,
                ]);
            }
        }
    }
}
