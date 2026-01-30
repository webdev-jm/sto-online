<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

use App\Models\Sale;
use App\Models\SalesUpload;
use App\Models\Customer;
use App\Models\SMSProduct;
use App\Models\Channel;
use App\Models\Salesman;
use App\Models\Location;

use App\Http\Resources\SalesResource;
use App\Http\Traits\ApiBranchKeyChecker;

use App\Http\Traits\ProductMappingTrait;

class SalesController extends Controller
{
    use ApiBranchKeyChecker;
    use ProductMappingTrait;

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
            ->paginate(10);

        return SalesResource::collection($sales);
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
                function($attribute, $value, $fail) use($account_branch) {
                    $sku_code = $value;

                    $mapping_result = $this->productMapping($account_branch->account_id, $sku_code);
                    $sku_code = $mapping_result[0];

                    if(strpos(trim($sku_code ?? ''), '-')) {
                        $sku_arr = explode('-', $sku_code);
                        $sku_code = end($sku_arr);
                    }

                    // check if existed
                    $check = SMSProduct::where('stock_code', $sku_code)
                        ->first();
                    if(empty($check)) {
                        $fail('SKU code '.$sku_code.' is not in the system.');
                    }
                }
            ],
            'channel_code' => [
                'required',
                function($attribute, $value, $fail) use($account_branch) {
                    // check if existed
                    $check = Channel::where('code', $value)
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
            'warehouse_code' => [
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
            'date' => [
                'required'
            ],
            'invoice_number' => [
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

        $type = 1;

        $mapping_result = $this->productMapping($account_branch->account_id, $request->sku_code);
        $request->sku_code = $mapping_result[0];
        $type = $mapping_result[1] ?? $type;

        if(strpos(trim($request->sku_code ?? ''), '-')) {
            $sku_arr = explode('-', $request->sku_code);
            if($sku_arr[0] == 'FG') { // Free Goods
                $request->sku_code = end($sku_arr);
                // process when free goods
                $type = 2;
            }
            if($sku_arr[0] == 'PRM') { // Promo
                $request->sku_code = end($sku_arr);
                // process when promo
                $type = 3;
            }
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
            ->first();
        // Salesman
        $salesman = Salesman::where('code', $request->salesman_code)
            ->where('account_branch_id', $account_branch->id)
            ->first();
        // Location
        $location = Location::where('code', $request->warehouse_code)
            ->where('account_branch_id', $account_branch->id)
            ->first();

        $current_date = date('Y-m-d');

        $sales_upload = SalesUpload::where('account_id', $account_branch->account_id)
            ->where('account_branch_id', $account_branch->id)
            ->where(DB::raw('DATE(created_at)'), $current_date)
            ->where('type', 1)
            ->first();
        if(empty($sales_upload)) {
            $sales_upload = new SalesUpload([
                'account_id' => $account_branch->account_id,
                'account_branch_id' => $account_branch->id,
                'user_id' => auth()->user()->id,
                'sku_count' => 0,
                'total_quantity' => 0,
                'total_price_vat' => 0,
                'total_amount' => 0,
                'total_amount_vat' => 0,
                'total_cm_quantity' => 0,
                'total_cm_price_vat' => 0,
                'total_cm_amount' => 0,
                'total_cm_amount_vat' => 0,
                'type' => 1
            ]);
            $sales_upload->save();
        }

        $total_cm_quantity = $sales_upload->total_cm_quantity;
        $total_cm_price_vat = $sales_upload->total_cm_price_vat;
        $total_cm_amount = $sales_upload->total_cm_amount;
        $total_cm_amount_vat = $sales_upload->total_cm_amount_vat;
        $total_quantity = $sales_upload->total_quantity;
        $total_price_vat = $sales_upload->total_price_vat;
        $total_amount = $sales_upload->total_amount;
        $total_amount_vat = $sales_upload->total_amount_vat;

        $category = 0;
        if(!empty($request->invoice_number) && strpos($request->invoice_number, '-')) {
            $invoice_number_str_arr = explode('-', $request->invoice_number);
            if($invoice_number_str_arr[0] == 'PSC') { // credit memo
                $category = 1;
            }
        }

        $sale = new Sale([
            'sales_upload_id' => $sales_upload->id,
            'account_id' => $account_branch->account_id,
            'account_branch_id' => $account_branch->id,
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'channel_id' => $channel->id,
            'salesman_id' => $salesman->id,
            'location_id' => $location->id,
            'user_id' => auth()->user()->id,
            'date' => $request->date,
            'document_number' => $request->invoice_number,
            'uom' => $request->uom,
            'quantity' => $request->quantity,
            'price_inc_vat' => $request->price_inc_vat,
            'amount' => $request->amount,
            'amount_inc_vat' => $request->amount_inc_vat,
            'type' => $type,
            'category' => $category
        ]);
        $sale->save();

        // check if not FG or PROMO
        if($type == 1) {
            if($category == 1) { // Credit Memo
                $total_cm_quantity += $request->quantity;
                $total_cm_price_vat += $request->price_inc_vat;
                $total_cm_amount += $request->amount;
                $total_cm_amount_vat += $request->amount_inc_vat;
            } else { // Invoice
                $total_quantity += $request->quantity;
                $total_price_vat += $request->price_inc_vat;
                $total_amount += $request->amount;
                $total_amount_vat += $request->amount_inc_vat;
            }
        }

        $sales_upload->update([
            'sku_count' => $sales_upload->sku_count + 1,
            'total_quantity' => $total_quantity,
            'total_price_vat' => $total_price_vat,
            'total_amount' => $total_amount,
            'total_amount_vat' => $total_amount_vat,
            'total_cm_quantity' => $total_cm_quantity,
            'total_cm_price_vat' => $total_cm_price_vat,
            'total_cm_amount' => $total_cm_amount,
            'total_cm_amount_vat' => $total_cm_amount_vat,
        ]);

        DB::setDefaultConnection($account_branch->account->db_data->connection_name);

        DB::statement('CALL generate_sales_report(?, ?, ?, ?)', [$account_branch->account_id, $account_branch->id, date('Y', strtotime($request->date)), date('n', strtotime($request->date))]);

        DB::setDefaultConnection('mysql');

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
                function($attribute, $value, $fail) use($account_branch) {
                    $sku_code = $value;

                    $mapping_result = $this->productMapping($account_branch->account_id, $sku_code);
                    $sku_code = $mapping_result[0];

                    if(strpos(trim($sku_code ?? ''), '-')) {
                        $sku_arr = explode('-', $sku_code);
                        $sku_code = end($sku_arr);
                    }

                    // check if existed
                    $check = SMSProduct::where('stock_code', $sku_code)
                        ->first();
                    if(empty($check)) {
                        $fail('SKU code '.$sku_code.' is not in the system.');
                    }
                }
            ],
            'channel_code' => [
                'required',
                function($attribute, $value, $fail) use($account_branch) {
                    // check if existed
                    $check = Channel::where('code', $value)
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
            'warehouse_code' => [
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
            'date' => [
                'required'
            ],
            'invoice_number' => [
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

        $type = 1;

        $mapping_result = $this->productMapping($account_branch->account_id, $request->sku_code);
        $request->sku_code = $mapping_result[0];
        $type = $mapping_result[1] ?? $type;

        if(strpos(trim($request->sku_code ?? ''), '-')) {
            $sku_arr = explode('-', $request->sku_code);
            if($sku_arr[0] == 'FG') { // Free Goods
                $request->sku_code = end($sku_arr);
                // process when free goods
                $type = 2;
            }
            if($sku_arr[0] == 'PRM') { // Promo
                $request->sku_code = end($sku_arr);
                // process when promo
                $type = 3;
            }
        }

        $category = 0;
        if(!empty($request->invoice_number) && strpos($request->invoice_number, '-')) {
            $invoice_number_str_arr = explode('-', $request->invoice_number);
            if($invoice_number_str_arr[0] == 'PSC') { // credit memo
                $category = 1;
            }
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
            ->first();
        // Salesman
        $salesman = Salesman::where('code', $request->salesman_code)
            ->where('account_branch_id', $account_branch->id)
            ->first();
        // Location
        $location = Location::where('code', $request->warehouse_code)
            ->where('account_branch_id', $account_branch->id)
            ->first();

        DB::setDefaultConnection($account_branch->account->db_data->connection_name);

        // check
        $sale = Sale::where('account_branch_id', $account_branch->id)
            ->where('id', $id)
            ->first();

        if($sale->date != $request->date) {
            DB::statement('CALL generate_sales_report(?, ?, ?, ?)', [
                $account_branch->account_id, $account_branch->id,
                date('Y', strtotime($sale->date)),
                date('n', strtotime($sale->date))
            ]);
        }

        if(!empty($sale)) {
            $sales_upload = $sale->sales_upload;

            $total_cm_quantity = $sales_upload->total_cm_quantity;
            $total_cm_price_vat = $sales_upload->total_cm_price_vat;
            $total_cm_amount = $sales_upload->total_cm_amount;
            $total_cm_amount_vat = $sales_upload->total_cm_amount_vat;
            $total_quantity = $sales_upload->total_quantity;
            $total_price_vat = $sales_upload->total_price_vat;
            $total_amount = $sales_upload->total_amount;
            $total_amount_vat = $sales_upload->total_amount_vat;

            // check if not FG or PROMO
            if($sale->type == 1) {
                if($sale->category == 1) { // Credit Memo
                    $total_cm_quantity -= $sale->quantity;
                    $total_cm_price_vat -= $sale->price_inc_vat;
                    $total_cm_amount -= $sale->amount;
                    $total_cm_amount_vat -= $sale->amount_inc_vat;
                } else { // Invoice
                    $total_quantity -= $sale->quantity;
                    $total_price_vat -= $sale->price_inc_vat;
                    $total_amount -= $sale->amount;
                    $total_amount_vat -= $sale->amount_inc_vat;
                }
            }

            $sale->update([
                'customer_id' => $customer->id,
                'product_id' => $product->id,
                'channel_id' => $channel->id,
                'salesman_id' => $salesman->id,
                'location_id' => $location->id,
                'date' => $request->date,
                'document_number' => $request->invoice_number,
                'uom' => $request->uom,
                'quantity' => $request->quantity,
                'price_inc_vat' => $request->price_inc_vat,
                'amount' => $request->amount,
                'amount_inc_vat' => $request->amount_inc_vat,
            ]);

            // check if not FG or PROMO
            if($type == 1) {
                if($category == 1) { // Credit Memo
                    $total_cm_quantity += $request->quantity;
                    $total_cm_price_vat += $request->price_inc_vat;
                    $total_cm_amount += $request->amount;
                    $total_cm_amount_vat += $request->amount_inc_vat;
                } else { // Invoice
                    $total_quantity += $request->quantity;
                    $total_price_vat += $request->price_inc_vat;
                    $total_amount += $request->amount;
                    $total_amount_vat += $request->amount_inc_vat;
                }
            }

            $sales_upload->update([
                'total_quantity' => $total_quantity,
                'total_price_vat' => $total_price_vat,
                'total_amount' => $total_amount,
                'total_amount_vat' => $total_amount_vat,
                'total_cm_quantity' => $total_cm_quantity,
                'total_cm_price_vat' => $total_cm_price_vat,
                'total_cm_amount' => $total_cm_amount,
                'total_cm_amount_vat' => $total_cm_amount_vat,
            ]);

            DB::statement('CALL generate_sales_report(?, ?, ?, ?)', [
                $account_branch->account_id, $account_branch->id,
                date('Y', strtotime($request->date)),
                date('n', strtotime($request->date))
            ]);

            DB::setDefaultConnection('mysql');

            return $this->successResponse(new SalesResource($sale));
        } else {
            return $this->validationError('Data not found.');
        }
    }
}
