<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use App\Models\District;
use App\Models\Area;
use App\Http\Resources\DistrictResource;

use App\Http\Traits\ApiBranchKeyChecker;

class DistrictController extends Controller
{
    use ApiBranchKeyChecker;

    public function index(Request $request) {
        $check = $this->checkBranchKey($request->header('BRANCH-KEY'));
        
        if(!empty($check['error'])) {
            return $this->validationError($check['error']);
        }

        $account_branch = $check['account_branch'];

        $districts = District::where('account_branch_id', $account_branch->id)
            ->paginate(10);

        return DistrictResource::collection($districts);
    }

    public function create(Request $request) {
        $check = $this->checkBranchKey($request->header('BRANCH-KEY'));
        
        if(!empty($check['error'])) {
            return $this->validationError($check['error']);
        }

        $account_branch = $check['account_branch'];
        
        $validator = Validator::make($request->all(), [
            'district_code' => [
                'required',
                Rule::unique($account_branch->account->db_data->connection_name.'.'.(new District)->getTable())->where('account_branch_id', $account_branch->id)
            ],
            'area_codes' => [
                'required'
            ]
        ]);

        if($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        // check areas codes
        $area_codes_arr = explode(',', $request->area_codes);
        $area_ids = array();
        foreach($area_codes_arr as $code) {
            $area = Area::where('account_branch_id', $account_branch->id)
                ->where('code', $code)
                ->first();
            if(!empty($area)) {
                $area_ids[] = $area->id;
            }
        }

        $district = new District([
            'account_branch_id' => $account_branch->id,
            'district_code' => $request->district_code,
        ]);
        $district->save();
        $district->areas()->sync($area_ids);

        return $this->successResponse(new DistrictResource($district));
    }

    public function show(Request $request, $id) {
        $check = $this->checkBranchKey($request->header('BRANCH-KEY'));
        
        if(!empty($check['error'])) {
            return $this->validationError($check['error']);
        }

        if(empty($id)) {
            return $this->validationError('id is required.');
        }

        $account_branch = $check['account_branch'];
        $district = District::where('account_branch_id', $account_branch->id)
            ->where('id', $id)
            ->first();

        if(!empty($district)) {
            return $this->successResponse(new DistrictResource($district));
        } else {
            return $this->validationError('Data not found.');
        }
    }

    public function update(Request $request, $id) {
        $check = $this->checkBranchKey($request->header('BRANCH-KEY'));
        
        if(!empty($check['error'])) {
            return $this->validationError($check['error']);
        }

        if(empty($id)) {
            return $this->validationError('id is required');
        }

        $account_branch = $check['account_branch'];
        $validator = Validator::make($request->all(), [
            'district_code' => [
                'required',
                Rule::unique($account_branch->account->db_data->connection_name.'.'.(new District)->getTable())->where('account_branch_id', $account_branch->id)->ignore($id)
            ],
            'area_codes' => [
                'required'
            ]
        ]);

        if($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        // check areas codes
        $area_codes_arr = explode(',', $request->area_codes);
        $area_ids = array();
        foreach($area_codes_arr as $code) {
            $area = Area::where('account_branch_id', $account_branch->id)
                ->where('code', $code)
                ->first();
            if(!empty($area)) {
                $area_ids[] = $area->id;
            }
        }

        $district = District::where('account_branch_id', $account_branch->id)
            ->where('id', $id)
            ->first();
        if(!empty($district)) {
            $district->update([
                'district_code' => $request->district_code,
            ]);
            $district->areas()->sync($area_ids);

            return $this->successResponse(new DistrictResource($district));
        } else {
            return $this->validationError('data not found.');
        }
    }
}
