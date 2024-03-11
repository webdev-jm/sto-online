<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

use App\Models\Sale;
use App\Models\Customer;
use App\Models\SMSProduct;
use App\Models\Channel;
use App\Models\Salesman;
use App\Models\Location;

use App\Http\Resources\SalesResource;
use App\Http\Traits\ApiBranchKeyChecker;

class SalesController extends Controller
{
    use ApiBranchKeyChecker;
    
    public function index(Request $request) {
        $check = $this->checkBranchKey($request->header('BRANCH-KEY'));
        
        if(!empty($check['error'])) {
            return $this->validationError($check['error']);
        }

        $account_branch = $check['account_branch'];

        if(empty($request->year)) {
            return $this->validationError('year is required.');
        }

        $sales = Sale::where('account_branch_id', $account_branch->id)
            ->when(!empty($request->month), function($query) use($request) {
                $query->where(DB::raw('MONTH(date)'), $request->month);
            })
            ->where(DB::raw('YEAR(date)'), $request->year)
            ->get();

        return $this->successResponse(SalesResource::collection($sales));
    }

    public function create(Request $request) {
        $check = $this->checkBranchKey($request->header('BRANCH-KEY'));
        
        if(!empty($check['error'])) {
            return $this->validationError($check['error']);
        }

        $account_branch = $check['account_branch'];

        $validator = Validator::make($request->all(), [
            'customer_code' => [
                'required',
                function($attribute, $value, $fail) use($account_branch) {
                    // check if existed
                    $check = Customer::where('account_branch_id', $account_branch->id)
                        ->where('code', $value)
                        ->first();
                    if(empty($check)) {
                        $fail('Customer code '.$value.' is not in the system.');
                    }
                }
            ],
            'sku_code' => [
                'required',
                function($attribute, $value, $fail) {
                    // check if existed
                    $check = SMSProduct::where('stock_code', $value)
                        ->first();
                    if(empty($check)) {
                        $fail('SKU code '.$value.' is not in the system.');
                    }
                }
            ],
            'channel_code' => [
                'required',
                function($attribute, $value, $fail) use($account_branch) {
                    // check if existed
                    $check = Channel::where('account_branch_id', $account_branch->id)
                        ->where('code', $value)
                        ->first();
                    if(empty($check)) {
                        $fail('Channel code '.$value.' is not in the system.');
                    }
                }
            ],
            'salesman_code' => [
                'required',
                function($attribute, $value, $fail) use($account_branch) {
                    // check if existed
                    $check = Salesman::where('account_branch_id', $account_branch->id)
                        ->where('code', $value)
                        ->first();
                    if(empty($check)) {
                        $fail('Salesman code '.$value.' is not in the system.');
                    }
                }
            ],
            'location_code' => [
                'required',
                function($attribute, $value, $fail) use($account_branch) {
                    // check if existed
                    $check = Location::where('account_branch_id', $account_branch->id)
                        ->where('code', $value)
                        ->first();
                    if(empty($check)) {
                        $fail('Location code '.$value.' is not in the system.');
                    }
                }
            ],
            'type' => [
                'required'
            ],
            'date' => [
                'required'
            ],
            'document_number' => [
                'required'
            ],
            'category' => [
                'required'
            ],
            'uom' => [
                'required'
            ],
            'quantity' => [
                'required'
            ],
            'price_inc_vat' => [
                'required'
            ],
            'amount' => [
                'required'
            ],
            'amount_inc_vat' => [
                'required'
            ]
        ]);

        if($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        // check data
        // Customer
        $customer = Customer::where('code', $request->customer_code)
            ->where('account_branch_id', $account_branch->id)
            ->first();
        // Product
        $product = SMSProduct::where('stock_code', $request->sku_code)
            ->first();
        // Channel
        $channel = Channel::where('code', $request->channel_code)
            ->where('account_branch_id', $account_branch->id)
            ->first();
        // Salesman
        $salesman = Salesman::where('code', $request->salesman_code)
            ->where('account_branch_id', $account_branch->id)
            ->first();
        // Location
        $location = Location::where('code', $request->location_code)
            ->where('account_branch_id', $account_branch->id)
            ->first();

        $sale = new Sale([
            'sales_upload_id' => NULL,
            'account_id' => $account_branch->account_id,
            'account_branch_id' => $account_branch->id,
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'channel_id' => $channel->id,
            'salesman_id' => $salesman->id,
            'location_id' => $location->id,
            'user_id' => auth()->user()->id,
            'type' => $request->type,
            'date' => $request->date,
            'document_number' => $request->document_number,
            'category' => $request->category,
            'uom' => $request->uom,
            'quantity' => $request->quantity,
            'price_inc_vat' => $request->price_inc_vat,
            'amount' => $request->amount,
            'amount_inc_vat' => $request->amount_inc_vat,
        ]);
        $sale->save();

        return $this->successResponse(new SalesResource($sale));
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

        $sale = Sale::where('account_branch_id', $account_branch->id)
            ->where('id', $id)
            ->first();
        if(!empty($sale)) {
            return $this->successResponse(new SalesResource($sale));
        } else {
            $this->validationError('Data not found.');
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
            'customer_code' => [
                'required',
                function($attribute, $value, $fail) use($account_branch) {
                    // check if existed
                    $check = Customer::where('account_branch_id', $account_branch->id)
                        ->where('code', $value)
                        ->first();
                    if(empty($check)) {
                        $fail('Customer code '.$value.' is not in the system.');
                    }
                }
            ],
            'sku_code' => [
                'required',
                function($attribute, $value, $fail) {
                    // check if existed
                    $check = SMSProduct::where('stock_code', $value)
                        ->first();
                    if(empty($check)) {
                        $fail('SKU code '.$value.' is not in the system.');
                    }
                }
            ],
            'channel_code' => [
                'required',
                function($attribute, $value, $fail) use($account_branch) {
                    // check if existed
                    $check = Channel::where('account_branch_id', $account_branch->id)
                        ->where('code', $value)
                        ->first();
                    if(empty($check)) {
                        $fail('Channel code '.$value.' is not in the system.');
                    }
                }
            ],
            'salesman_code' => [
                'required',
                function($attribute, $value, $fail) use($account_branch) {
                    // check if existed
                    $check = Salesman::where('account_branch_id', $account_branch->id)
                        ->where('code', $value)
                        ->first();
                    if(empty($check)) {
                        $fail('Salesman code '.$value.' is not in the system.');
                    }
                }
            ],
            'location_code' => [
                'required',
                function($attribute, $value, $fail) use($account_branch) {
                    // check if existed
                    $check = Location::where('account_branch_id', $account_branch->id)
                        ->where('code', $value)
                        ->first();
                    if(empty($check)) {
                        $fail('Location code '.$value.' is not in the system.');
                    }
                }
            ],
            'type' => [
                'required'
            ],
            'date' => [
                'required'
            ],
            'document_number' => [
                'required'
            ],
            'category' => [
                'required'
            ],
            'uom' => [
                'required'
            ],
            'quantity' => [
                'required'
            ],
            'price_inc_vat' => [
                'required'
            ],
            'amount' => [
                'required'
            ],
            'amount_inc_vat' => [
                'required'
            ],
        ]);

        if($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        // check data
        // Customer
        $customer = Customer::where('code', $request->customer_code)
            ->where('account_branch_id', $account_branch->id)
            ->first();
        // Product
        $product = SMSProduct::where('stock_code', $request->sku_code)
            ->first();
        // Channel
        $channel = Channel::where('code', $request->channel_code)
            ->where('account_branch_id', $account_branch->id)
            ->first();
        // Salesman
        $salesman = Salesman::where('code', $request->salesman_code)
            ->where('account_branch_id', $account_branch->id)
            ->first();
        // Location
        $location = Location::where('code', $request->location_code)
            ->where('account_branch_id', $account_branch->id)
            ->first();

        // check
        $sale = Sale::where('account_branch_id', $account_branch->id)
            ->where('id', $id)
            ->first();
        if(!empty($sale)) {
            $sale->update([
                'customer_id' => $customer->id,
                'product_id' => $product->id,
                'channel_id' => $channel->id,
                'salesman_id' => $salesman->id,
                'location_id' => $location->id,
                'type' => $request->type,
                'date' => $request->date,
                'document_number' => $request->document_number,
                'category' => $request->category,
                'uom' => $request->uom,
                'quantity' => $request->quantity,
                'price_inc_vat' => $request->price_inc_vat,
                'amount' => $request->amount,
                'amount_inc_vat' => $request->amount_inc_vat,
            ]);

            return $this->successResponse(new SalesResource($sale));
        } else {
            return $this->validationError('Data not found.');
        }
    }
}
