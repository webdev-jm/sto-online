<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Models\AccountBranch;
use App\Models\Salesman;
use App\Models\District;

use App\Http\Traits\ApiBranchKeyChecker;
use App\Http\Resources\SalesmanResource;

use Illuminate\Validation\Rule;

class SalesmanController extends Controller
{
    use ApiBranchKeyChecker;

    public function index(Request $request) {
        $check = $this->checkBranchKey($request->header('BRANCH-KEY'));
        
        if(!empty($check['error'])) {
            return $this->validationError($check['error']);
        }

        $account_branch = $check['account_branch'];
        $sales = Salesman::where('account_branch_id', $account_branch->id)
            ->paginate(10);

        return SalesmanResource::collection($sales);
    }

    public function create(Request $request) {
        $check = $this->checkBranchKey($request->header('BRANCH-KEY'));
        
        if(!empty($check['error'])) {
            return $this->validationError($check['error']);
        }

        $account_branch = $check['account_branch'];
        $validator = Validator::make($request->all(), [
            'code' => [
                'required',
                Rule::unique($account_branch->account->db_data->connection_name.'.'.(new Salesman)->getTable())->where('account_branch_id', $account_branch->id)
            ],
            'name' => [
                'required'
            ],
            'type' => [
                'required'
            ],
            'district_code' => [
                'required',
                function($attribute, $value, $fail) use($account_branch) {
                    // check if existed
                    $check = District::where('account_branch_id', $account_branch->id)
                        ->where('district_code', $value)
                        ->first();
                    if(empty($check)) {
                        $fail('District code '.$value.' is not in the system.');
                    }
                }
            ]
        ]);

        if($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $district = District::where('account_branch_id', $account_branch->id)
            ->where('district_code', $request->district_code)
            ->first();

        $salesman = new Salesman([
            'account_id' => $account_branch->account_id,
            'account_branch_id' => $account_branch->id,
            'district_id' => $district->id,
            'code' => $request->code,
            'name' => $request->name,
            'type' => $request->type
        ]);
        $salesman->save();

        $salesman_data = new SalesmanResource($salesman);

        return $this->successResponse($salesman_data);
        
    }

    public function show(Request $request, $id) {
        $check = $this->checkBranchKey($request->header('BRANCH-KEY'));
        
        if(!empty($check['error'])) {
            return $this->validationError($check['error']);
        }

        $account_branch = $check['account_branch'];

        if(!empty($id)) {
            
            $salesman = Salesman::where('account_branch_id', $account_branch->id)
                ->where('id', $id)
                ->first();

            if(!empty($salesman)) {
                return $this->successResponse(new SalesmanResource($salesman));
            } else {
                return $this->validationError('Data not found');
            }
        } else {
            return $this->validationError('id is required');
        }
    }

    public function update(Request $request, $id) {
        $check = $this->checkBranchKey($request->header('BRANCH-KEY'));
        
        if(!empty($check['error'])) {
            return $this->validationError($check['error']);
        }

        $account_branch = $check['account_branch'];
        $validator = Validator::make($request->all(), [
            'code' => [
                'required',
                Rule::unique($account_branch->account->db_data->connection_name.'.'.(new Salesman)->getTable())->where('account_branch_id', $account_branch->id)->ignore($id)
            ],
            'name' => [
                'required'
            ]
        ]);

        if($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        if(empty($id)) {
            return $this->validationError('id is required');
        }

        $salesman = Salesman::where('account_branch_id', $account_branch->id)
            ->where('id', $id)
            ->first();
        if(!empty($salesman)) {
            $salesman->update([
                'code' => $request->code,
                'name' => $request->name
            ]);
            
            return $this->successResponse(new SalesmanResource($salesman));
        } else {
            return $this->validationError('data not found.');
        }
    }
}
