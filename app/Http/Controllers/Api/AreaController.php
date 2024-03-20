<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use App\Models\Area;
use App\Http\Resources\AreaResource;

use App\Http\Traits\ApiBranchKeyChecker;

class AreaController extends Controller
{
    use ApiBranchKeyChecker;

    public function index(Request $request) {
        $check = $this->checkBranchKey($request->header('BRANCH-KEY'));
        
        if(!empty($check['error'])) {
            return $this->validationError($check['error']);
        }

        $account_branch = $check['account_branch'];
        $areas = Area::where('account_branch_id', $account_branch->id)
            ->paginate(10);
        
        return AreaResource::collection($areas);
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
                Rule::unique((new Area)->getTable())->where('account_branch_id', $account_branch->id)
            ],
            'name' => [
                'required'
            ]
        ]);

        if($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $area = new Area([
            'account_id' => $account_branch->account_id,
            'account_branch_id' => $account_branch->id,
            'code' => $request->code,
            'name' => $request->name,
        ]);
        $area->save();

        return $this->successResponse(new AreaResource($area));
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
        $area = Area::where('account_branch_id', $account_branch->id)
            ->where('id', $id)
            ->first();
        
        if(!empty($area)) {
            return $this->successResponse(new AreaResource($area));
        } else {
            return $this->validationError('data not found');
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
            'code' => [
                'required',
                Rule::unique((new Area)->getTable())->where('account_branch_id', $account_branch->id)->ignore($id)
            ],
            'name' => [
                'required'
            ]
        ]);

        if($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $area = Area::where('account_branch_id', $account_branch->id)
            ->where('id', $id)
            ->first();
        if(!empty($area)) {
            $area->update([
                'code' => $request->code,
                'name' => $request->name,
            ]);

            return $this->successResponse(new AreaResource($area));
        } else {
            return $this->validationError('data not found');
        }
    }
}