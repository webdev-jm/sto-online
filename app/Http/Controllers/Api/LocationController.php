<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'error' => $check['error'],
            ], 422);
        }

        $account_branch = $check['account_branch'];
        if(!empty($account_branch)) {
            $locations = Location::where('account_branch_id', $account_branch->id)
                ->select(
                    'id',
                    'code',
                    'name',
                    'created_at',
                    'updated_at',
                )
                ->where('account_id', $account_branch->account_id)
                ->get();
        }

        return response()->json([
            'success' => true,
            'message' => $check['error'],
            'locations' => $locations
        ], 200);
    }
    
    public function create(Request $request) {
        $check = $this->checkBranchKey($request->header('BRANCH-KEY'));
        
        if(!empty($check['error'])) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'error' => $check['error']
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'code' => [
                'required'
            ],
            'name' => [
                'required'
            ]
        ]);

        if($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $account_branch = $check['account_branch'];
        $account = $account_branch->account;

        // check for duplication
        $check = Location::where('code', $request->code)
            ->first();
        if(empty($check)) {
            // create new location
            $location = new Location([
                'account_id' => $account->id,
                'account_branch_id' => $account_branch->id,
                'code' => $request->code,
                'name' => $request->name,
            ]);
            $location->save();

            $location_data = new LocationResource($location);

            $success = true;
        } else {
            $location_data = 'Location '.$request->code.' already exists.';
            $success = false;
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
            ]
        ];

        return response()->json([
            'success' => $success,
            'data' => $data,
            'location' => $location_data,
        ]);
    }

    public function show(Request $request) {
        $check = $this->checkBranchKey($request->header('BRANCH-KEY'));
        
        if(!empty($check['error'])) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'error' => $check['error']
            ], 422);
        }

        if(!empty($request->id)) {
            $account_branch = $check['account_branch'];
            if(!empty($account_branch)) {
                $location = Location::where('account_branch_id', $account_branch->id)
                    ->where('id', $request->id)
                    ->first();
                
                if(!empty($location)) {

                    return response()->json([
                        'success' => true,
                        'message' => $check['error'],
                        'locations' => new LocationResource($location),
                    ], 200);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => $check['error'],
                        'locations' => 'data not found',
                    ], 200);
                }

            }
        } else {
            return response()->json([
                'success' => false,
                'error' => 'id is required.'
            ]);
        }
        
    }

    public function update(Request $request, $id) {
        $check = $this->checkBranchKey($request->header('BRANCH-KEY'));
        
        if(empty($id)) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'error' => 'id is required.'
            ], 422);
        }

        if(!empty($check['error'])) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'error' => $check['error']
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'code' => [
                'required'
            ],
            'name' => [
                'required'
            ]
        ]);

        if($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // check if exists
        $account_branch = $check['account_branch'];
        if(!empty($account_branch)) {
            $location = Location::where('account_branch_id', $account_branch->id)
                ->where('id', $request->id)
                ->first();
            
            if(!empty($location)) {

                $location->update([
                    'code' => $request->code,
                    'name' => $request->name
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'location has been updated',
                    'locations' => new LocationResource($location),
                ], 200);

            } else {
                return response()->json([
                    'success' => false,
                    'message' => $check['error'],
                    'locations' => 'data not found',
                ], 200);
            }
        }
    }
}
