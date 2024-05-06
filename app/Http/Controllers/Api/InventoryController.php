<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use App\Models\Inventory;
use App\Models\InventoryUpload;
use App\Models\Location;
use App\Models\SMSProduct;

use App\Http\Resources\InventoryResource;
use App\Http\Traits\ApiBranchKeyChecker;

class InventoryController extends Controller
{
    use ApiBranchKeyChecker;

    public function index(Request $request) {
        $check = $this->checkBranchKey($request->header('BRANCH-KEY'));
        
        if(!empty($check['error'])) {
            return $this->validationError($check['error']);
        }

        $account_branch = $check['account_branch'];

        $inventories = Inventory::where('account_branch_id', $account_branch->id)
            ->paginate(10);

        return InventoryResource::collection($inventories);
    }

    public function create(Request $request) {
        $check = $this->checkBranchKey($request->header('BRANCH-KEY'));
        
        if(!empty($check['error'])) {
            return $this->validationError($check['error']);
        }

        $account_branch = $check['account_branch'];

        $validator = Validator::make($request->all(), [
            'warehouse_code' => [
                'required',
                function($attribute, $value, $fail) use($account_branch) {
                    $location = Location::where('account_branch_id', $account_branch->id)
                        ->where('code', $value)
                        ->first();
                    if(empty($location)) {
                        $fail('Location code '.$value.' does not exist in the system.');
                    }
                }
            ],
            'sku_code' => [
                'required',
                function($attribute, $value, $fail) {
                    $product = SMSProduct::where('stock_code', $value)
                        ->first();
                    if(empty($product)) {
                        $fail('Stock Code '.$value.' does not exist in the system.');
                    }
                }
            ],
            'inventory_date' => [
                'required',
            ],
            'type' => [
                'required',
            ],
            'uom' => [
                'required',
            ],
            'inventory' => [
                'required'
            ],
        ]);

        if($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        // type 1 - normal, 2 - free goods, 3 - promo
        $inventory_upload = InventoryUpload::where('account_branch_id', $account_branch->id)
            ->where('date', $request->inventory_date)
            ->where('user_id', auth()->user()->id)
            ->first();

        if(empty($inventory_upload)) {
            $inventory_upload = new InventoryUpload([
                'account_id' => $account_branch->account_id,
                'account_branch_id' => $account_branch->id,
                'user_id' => auth()->user()->id,
                'date' => $request->inventory_date,
                'total_inventory' => 0,
            ]);
            $inventory_upload->save();
        }

        $location = Location::where('account_branch_id', $account_branch->id)
            ->where('code', $request->warehouse_code)
            ->first();
        $product = SMSProduct::where('stock_code', $request->sku_code)
            ->first();

        // check duplicate
        $inventory = Inventory::where('account_branch_id', $account_branch->id)
            ->where('inventory_upload_id', $inventory_upload->id)
            ->where('location_id', $location->id)
            ->where('product_id', $product->id)
            ->first();
        if(empty($inventory)) {
            $inventory = new Inventory([
                'account_id' => $account_branch->account_id,
                'account_branch_id' => $account_branch->id,
                'inventory_upload_id' => $inventory_upload->id,
                'location_id' => $location->id,
                'product_id' => $product->id,
                'type' => $request->type,
                'uom' => $request->uom,
                'inventory' => $request->inventory
            ]);
            $inventory->save();

            $total_inventory = $inventory_upload->total_inventory + $inventory->inventory;
            $inventory_upload->update([
                'total_inventory' => $total_inventory
            ]);

            return $this->successResponse(new InventoryResource($inventory));
        } else {
            return $this->validationError('Data already exist.');
        }
    }

    public function show(Request $request, $id) {
        $check = $this->checkBranchKey($request->header('BRANCH-KEY'));
        
        if(!empty($check['error'])) {
            return $this->validationError($check['error']);
        }

        $account_branch = $check['account_branch'];

        if(empty($id)) {
            return $this->validationError('id is required.');
        }

        $inventory = Inventory::where('account_branch_id', $account_branch->id)
            ->where('id', $id)
            ->first();
        if(!empty($inventory)) {
            return $this->successResponse(new InventoryResource($inventory));
        } else {
            return $this->validationError('Data not found.');
        }
    }

    public function update(Request $request, $id) {
        $check = $this->checkBranchKey($request->header('BRANCH-KEY'));
        
        if(!empty($check['error'])) {
            return $this->validationError($check['error']);
        }

        $account_branch = $check['account_branch'];

        if(empty($id)) {
            return $this->validationError('id is required.');
        }

        $validator = Validator::make($request->all(), [
            'warehouse_code' => [
                'required',
                function($attribute, $value, $fail) use($account_branch) {
                    $location = Location::where('account_branch_id', $account_branch->id)
                        ->where('code', $value)
                        ->first();
                    if(empty($location)) {
                        $fail('Location code '.$value.' does not exist in the system.');
                    }
                }
            ],
            'sku_code' => [
                'required',
                function($attribute, $value, $fail) {
                    $product = SMSProduct::where('stock_code', $value)
                        ->first();
                    if(empty($product)) {
                        $fail('Stock Code '.$value.' does not exist in the system.');
                    }
                }
            ],
            'inventory_date' => [
                'required',
            ],
            'type' => [
                'required',
            ],
            'uom' => [
                'required',
            ],
            'inventory' => [
                'required'
            ],
        ]);

        if($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        // type 1 - normal, 2 - free goods, 3 - promo
        $inventory = Inventory::where('account_branch_id', $account_branch->id)
            ->where('id', $id)
            ->first();
        if(!empty($inventory)) {
            // location
            $location = Location::where('account_branch_id', $account_branch->id)
                ->where('code', $request->warehouse_code)
                ->first();
            $product = SMSProduct::where('stock_code', $request->sku_code)
                ->first();

            $inventory_upload = InventoryUpload::where('account_branch_id', $account_branch->id)
                ->where('id', $inventory->inventory_upload_id)
                ->first();

            $total_inventory = $inventory_upload->total_inventory - $inventory->inventory;

            if($inventory_upload->date == $request->inventory_date) {
                $inventory->update([
                    'location_id' => $location->id,
                    'product_id' => $product->id,
                    'type' => $request->type,
                    'uom' => $request->uom,
                    'inventory' => $request->inventory
                ]);

                $total_inventory = $total_inventory + $inventory->inventory;
                $inventory_upload->update([
                    'total_inventory' => $total_inventory
                ]);

                return $this->successResponse(new InventoryResource($inventory));
            } else {
                $inventory_upload->update([
                    'total_inventory' => $total_inventory
                ]);

                $inventory_upload = InventoryUpload::where('account_branch_id', $account_branch->id)
                    ->where('date', $request->inventory_date)
                    ->where('user_id', auth()->user()->id)
                    ->first();
                if(empty($inventory_upload)) {
                    $inventory_upload = new InventoryUpload([
                        'account_id' => $account_branch->account_id,
                        'account_branch_id' => $account_branch->id,
                        'user_id' => auth()->user()->id,
                        'date' => $request->inventory_date,
                        'total_inventory' => 0,
                    ]);
                    $inventory_upload->save();
                }

                $inventory->update([
                    'inventory_upload_id' => $inventory_upload->id,
                    'location_id' => $location->id,
                    'product_id' => $product->id,
                    'type' => $request->type,
                    'uom' => $request->uom,
                    'inventory' => $request->inventory
                ]);

                $total_inventory = $inventory_upload->total_inventory + $request->inventory;
                $inventory_upload->update([
                    'total_inventory' => $total_inventory
                ]);

                return $this->successResponse(new InventoryResource($inventory));
            }
        } else {
            return $this->validationError('Data not found.');
        }

    }
}
