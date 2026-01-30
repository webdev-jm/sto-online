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

use App\Models\Province;
use App\Models\Municipality;
use App\Models\Barangay;

use App\Http\Traits\ApiBranchKeyChecker;
use App\Http\Traits\ChannelMappingTrait;

class CustomerController extends Controller
{
    use ApiBranchKeyChecker;
    use ChannelMappingTrait;

    public function index(Request $request) {
        $check = $this->checkBranchKey($request->header('BRANCH-KEY'));

        if(!empty($check['error'])) {
            return $this->validationError($check['error']);
        }

        $account_branch = $check['account_branch'];

        $customers = Customer::where('account_branch_id', $account_branch->id)
            ->paginate(10);

        return CustomerResource::collection($customers);
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
                    $mapping_result = $this->channelMapping($account_branch->account_id, $value);
                    $value = $mapping_result[0];

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
            'code' => [
                'required',
                Rule::unique($account_branch->account->db_data->connection_name.'.'.(new Customer)->getTable())->where('account_branch_id', $account_branch->id)
            ],
            'name' => [
                'required'
            ],
            'address' => [
                'required'
            ],
            'street' => [
                'max:255'
            ],
            'brgy' => [
                'required',
                'max:255'
            ],
            'city' => [
                'required',
                'max:255'
            ],
            'province' => [
                'required',
                'max:255'
            ],
        ]);

        if($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $mapping_result = $this->channelMapping($account_branch->account_id, $request->channel_code);
        $request->channel_code = $mapping_result[0];

        $channel = Channel::where('code', $request->channel_code)
            ->first();
        $salesman = Salesman::where('account_branch_id', $account_branch->id)
            ->where('code', $request->salesman_code)
            ->first();

        $province = Province::where('province_name', $request->province)->first();
        $province_id = NULL;
        if(!empty($province)) {
            $province_id = $province->id;
        }

        $city = Municipality::where('municipality_name', $request->city)->first();
        $city_id = NULL;
        if(!empty($city)) {
            $city_id = $city->id;
        }

        $barangay = Barangay::where('barangay_name', $request->brgy)->first();
        $barangay_id = NULL;
        if(!empty($barangay)) {
            $barangay_id = $barangay->id;
        }

        $customer = new Customer([
            'account_id' => $account_branch->account_id,
            'account_branch_id' => $account_branch->id,
            'channel_id' => $channel->id,
            'salesman_id' => $salesman->id,
            'province_id' => $province_id,
            'municipality_id' => $city_id,
            'barangay_id' => $barangay_id,
            'code' => $request->code,
            'name' => $request->name,
            'address' => $request->address,
            'street' => $request->street,
            'brgy' => $request->brgy,
            'city' => $request->city,
            'province' => $request->province,
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
                    $mapping_result = $this->channelMapping($account_branch->account_id, $value);
                    $value = $mapping_result[0];

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
            'code' => [
                'required',
                Rule::unique($account_branch->account->db_data->connection_name.'.'.(new Customer)->getTable())->where('account_branch_id', $account_branch->id)->ignore($id)
            ],
            'name' => [
                'required',
            ],
            'address' => [
                'required',
            ],
            'street' => [
                'max:255',
            ],
            'brgy' => [
                'required',
                'max:255',
            ],
            'city' => [
                'required',
                'max:255',
            ],
            'province' => [
                'required',
                'max:255',
            ],
        ]);

        if($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $customer = Customer::where('account_branch_id', $account_branch->id)
            ->where('id', $id)
            ->first();

        if(!empty($customer)) {

            $mapping_result = $this->channelMapping($account_branch->account_id, $request->channel_code);
            $request->channel_code = $mapping_result[0];

            $channel = Channel::where('code', $request->channel_code)
                ->first();
            $salesman = Salesman::where('account_branch_id', $account_branch->id)
                ->where('code', $request->salesman_code)
                ->first();

            $province = Province::where('province_name', $request->province)->first();
            $province_id = NULL;
            if(!empty($province)) {
                $province_id = $province->id;
            }

            $city = Municipality::where('municipality_name', $request->city)
                ->where('province_id', $province_id)
                ->first();
            $city_id = NULL;
            if(!empty($city)) {
                $city_id = $city->id;
            }

            $barangay = Barangay::where('barangay_name', $request->brgy)
                ->where('municipality_id', $city_id)
                ->first();
            $barangay_id = NULL;
            if(!empty($barangay)) {
                $barangay_id = $barangay->id;
            }

            $customer->update([
                'channel_id' => $channel->id,
                'salesman_id' => $salesman->id,
                'province_id' => $province_id,
                'municipality_id' => $city_id,
                'barangay_id' => $barangay_id,
                'code' => $request->code,
                'name' => $request->name,
                'address' => $request->address,
                'street' => $request->street,
                'brgy' => $request->brgy,
                'city' => $request->city,
                'province' => $request->province,
            ]);

            return $this->successResponse(new CustomerResource($customer));
        } else {
            return $this->validationError('data not found.');
        }
    }

    private function checkSimilarity($str1, $str2) {
        $similarity = 0;
        if(strlen($str1) > 0 && strlen($str2) > 0) {
            $distance = levenshtein(strtoupper($str1), strtoupper($str2));
            $max_length = max(strlen($str1), strlen($str2));
            $similarity = 1 - ($distance / $max_length);
            $similarity = $similarity * 100;
        }

        return $similarity;
    }
}
