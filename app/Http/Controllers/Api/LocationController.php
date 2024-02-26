<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\AccountBranch;
use App\Models\Location;

use App\Http\Traits\ApiBranchKeyChecker;

class LocationController extends Controller
{
    use ApiBranchKeyChecker;

    public function index(Request $request) {
        return response()->json([$request->header('BRANCH_KEY')]);
        $err = $this->checkBranchKey($request->BRANCH_KEY);
    
        if(!empty($err)) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'error' => $err,
            ], 422);
        }
    }
    
    public function create(Request $request) {
        $validator = Validator::make($request->all(), [
            'BRANCH_KEY' => [
                'required'
            ],
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

        $account_branch = AccountBranch::findByToken($request->BRANCH_KEY);
        $account = $account_branch->account;

        if(empty($account_branch)) {
            return response()->json([
                'success' => false,
                'error' => 'The provided BRANCH KEY is invalid.'
            ]);
        }

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

            $success = true;
        } else {
            $location = 'Location '.$request->code.' already exists.';
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
            'location' => $location,
        ]);
    }
}
