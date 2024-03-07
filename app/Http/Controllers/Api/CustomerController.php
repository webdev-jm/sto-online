<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use App\Http\Resources\CustomerResource;

use App\Models\Customer;
use App\Models\Channel;
use App\Models\Salesman;

use App\Http\Traits\ApiBranchKeyChecker;

class CustomerController extends Controller
{
    use ApiBranchKeyChecker;

    public function index(Request $request) {
        $check = $this->checkBranchKey($request->header('BRANCH-KEY'));
        
        if(!empty($check['error'])) {
            return $this->validationError($check['error']);
        }

        $account_branch = $check['account_branch'];

        $customers = Customer::select(
                'id',
                'code',
                'name',
                'address',
                'brgy',
                'city',
                'province',
                'country',
                'status',
                'created_at',
                'updated_at'
            )
            ->where('account_branch_id', $account_branch->id)
            ->get();

        return $this->successResponse($customers);
    }

    public function create(Request $request) {
        $check = $this->checkBranchKey($request->header('BRANCH-KEY'));
        
        if(!empty($check['error'])) {
            return $this->validationError($check['error']);
        }

        $account_branch = $check['account_branch'];

        $validator = Validator::make($request->all(), [
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
            'code' => [
                'required',
                Rule::unique((new Customer)->getTable())->where('account_branch_id', $account_branch->id)
            ],
            'name' => [
                'required'
            ],
            'address' => [
                'required'
            ],
            'brgy' => [
                'max:255'
            ],
            'city' => [
                'max:255'
            ],
            'province' => [
                'max:255'
            ],
            'country' => [
                'max:255'
            ],
        ]);

        if($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $channel = Channel::where('account_branch_id', $account_branch->id)
            ->where('code', $request->channel_code)
            ->first();
        $salesman = Salesman::where('account_branch_id', $account_branch->id)
            ->where('code', $request->salesman_code)
            ->first();

        $customer = new Customer([
            'account_id' => $account_branch->account_id,
            'account_branch_id' => $account_branch->id,
            'channel_id' => $channel->id,
            'salesman_id' => $salesman->id,
            'code' => $request->code,
            'name' => $request->name,
            'address' => $request->address,
            'brgy' => $request->brgy,
            'city' => $request->city,
            'province' => $request->province,
            'country' => $request->country
        ]);
        $customer->save();

        return $this->successResponse(new CustomerResource($customer));
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

        $customer = Customer::where('account_branch_id', $account_branch->id)
            ->where('id', $id)
            ->first();
        
        if(!empty($customer)) {
            return $this->successResponse(new CustomerResource($customer));
        } else {
            return $this->validationError('data not found.');
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
            'code' => [
                'required',
                Rule::unique((new Customer)->getTable())->where('account_branch_id', $account_branch->id)->ignore($id)
            ],
            'name' => [
                'required'
            ],
            'address' => [
                'required'
            ],
            'brgy' => [
                'max:255'
            ],
            'city' => [
                'max:255'
            ],
            'province' => [
                'max:255'
            ],
            'country' => [
                'max:255'
            ],
        ]);

        if($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $customer = Customer::where('account_branch_id', $account_branch->id)
            ->where('id', $id)
            ->first();

        if(!empty($customer)) {
            $channel = Channel::where('account_branch_id', $account_branch->id)
                ->where('code', $request->channel_code)
                ->first();
            $salesman = Salesman::where('account_branch_id', $account_branch->id)
                ->where('code', $request->salesman_code)
                ->first();

            $customer->update([
                'channel_id' => $channel->id,
                'salesman_id' => $salesman->id,
                'code' => $request->code,
                'name' => $request->name,
                'address' => $request->address,
                'brgy' => $request->brgy,
                'city' => $request->city,
                'province' => $request->province,
                'country' => $request->country
            ]);

            return $this->successResponse(new CustomerResource($customer));
        } else {
            return $this->validationError('data not found.');
        }
    }
}
