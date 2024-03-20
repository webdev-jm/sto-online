<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use App\Models\AccountBranch;
use App\Models\Location;

use App\Http\Traits\ApiBranchKeyChecker;

use App\Http\Resources\LocationResource;

class LocationController extends Controller
{
    use ApiBranchKeyChecker;

    public function index(Request $request) {
        $check = $this->checkBranchKey($request->header('BRANCH-KEY'));
        
        if(!empty($check['error'])) {
            return $this->validationError($check['error']);
        }

        $account_branch = $check['account_branch'];
        $locations = Location::where('account_id', $account_branch->account_id)
            ->paginate(10);

        return LocationResource::collection($locations);
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
                Rule::unique((new Location)->getTable())->where('account_branch_id', $account_branch->id)
            ],
            'name' => [
                'required'
            ]
        ]);

        if($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        // check for duplication
        $check = Location::where('code', $request->code)
            ->first();
        if(empty($check)) {
            // create new location
            $location = new Location([
                'account_id' => $account_branch->account->id,
                'account_branch_id' => $account_branch->id,
                'code' => $request->code,
                'name' => $request->name,
            ]);
            $location->save();

            $location_data = new LocationResource($location);
        } else {
            $location_data = 'Location '.$request->code.' already exists.';
        }

        $data = [
            'code' => $account_branch->code,
            'name' => $account_branch->name,
            'created_at' => $account_branch->created_at,
            'updated_at' => $account_branch->updated_at,
            'account' => [
                'account_code' => $account->account_code,
                'account_name' => $account->account_name,
                'short_name'   => $account->short_name,
                'created_at' => $account->created_at,
                'updated_at' => $account->updated_at,
            ],
            'location' => $location_data
        ];

        return $this->successResponse($data);
    }

    public function show(Request $request, $id) {
        $check = $this->checkBranchKey($request->header('BRANCH-KEY'));
        
        if(!empty($check['error'])) {
            return $this->validationError($check['error']);
        }

        if(!empty($id)) {
            $account_branch = $check['account_branch'];
            if(!empty($account_branch)) {
                $location = Location::where('account_branch_id', $account_branch->id)
                    ->where('id', $id)
                    ->first();
                
                if(!empty($location)) {
                    return $this->successResponse(new LocationResource($location));
                } else {
                    return $this->successResponse('data not found');
                }

            }
        } else {
            return $this->validationError('id is required.');
        }
    }

    public function update(Request $request, $id) {
        $check = $this->checkBranchKey($request->header('BRANCH-KEY'));
        
        if(empty($id)) {
            return $this->validationError('id is required');
        }

        if(!empty($check['error'])) {
            return $this->validationError($check['error']);
        }

        $account_branch = $check['account_branch'];
        $validator = Validator::make($request->all(), [
            'code' => [
                'required',
                Rule::unique((new Salesman)->getTable())->where('account_branch_id', $account_branch->id)->ignore($id)
            ],
            'name' => [
                'required'
            ]
        ]);

        if($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        // check if exists
        if(!empty($account_branch)) {
            $location = Location::where('account_branch_id', $account_branch->id)
                ->where('id', $id)
                ->first();
            
            if(!empty($location)) {

                $location->update([
                    'code' => $request->code,
                    'name' => $request->name
                ]);
                
                return $this->successResponse(new LocationResource($location));
            } else {
                return $this->successResponse('data not found');
            }
        }
    }
}
