<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Models\AccountBranch;
use App\Models\Salesman;

use App\Http\Traits\ApiBranchKeyChecker;
use App\Http\Resources\SalesmanResource;

class SalesmanController extends Controller
{
    use ApiBranchKeyChecker;

    public function index(Request $request) {
        $check = $this->checkBranchKey($request->header('BRANCH-KEY'));
        
        if(!empty($check['error'])) {
            return $this->validationError($check['error']);
        }

        $account_branch = $check['account_branch'];
        if(!empty($account_branch)) {
            $sales = Salesman::select(
                    'id',
                    'code',
                    'name',
                    'created_at',
                    'updated_at'
                )
                ->where('account_branch_id', $account_branch->id)
                ->get();
        }

        return $this->successResponse($sales);
    }

    public function create(Request $request) {
        $check = $this->checkBranchKey($request->header('BRANCH-KEY'));
        
        if(!empty($check['error'])) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'error' => $check['error'],
            ], 422);
            return $this->validationError($check['error']);
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
            return $this->validationError($validator->errors());
        }

        $account_branch = $check['account_branch'];

        // check for duplicate
        $check = Salesman::where('account_branch_id', $account_branch->id)
            ->where('code', $request->code)
            ->first();
        if(empty($check)) {
            $salesman = new Salesman([
                'account_id' => $account_branch->account_id,
                'account_branch_id' => $account_branch->id,
                'code' => $request->code,
                'name' => $request->name,
            ]);
            $salesman->save();

            $salesman_data = new SalesmanResource($salesman);
        } else {
            $salesman_data = 'Salesman code already exists.';
        }

        return $this->successResponse($salesman_data);
        
    }

    public function show() {
        
    }
}
