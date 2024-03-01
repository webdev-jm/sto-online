<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Models\AccountBranch;
use App\Models\Salesman;

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
        $sales = Salesman::select(
                'id',
                'code',
                'name',
                'created_at',
                'updated_at'
            )
            ->where('account_branch_id', $account_branch->id)
            ->get();

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
                'required',
                Rule::unique((new Salesman)->getTable())
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
            
                return $this->successResponse(new SalesmanResource($salesman));

        } else {
            return $this->validationError('id is required');
        }
    }

    public function update(Request $request, $id) {
        $check = $this->checkBranchKey($request->header('BRANCH-KEY'));
        
        if(!empty($check['error'])) {
            return $this->validationError($check['error']);
        }

        $validator = Validator::make($request->all(), [
            'code' => [
                'required',
                Rule::unique((new Salesman)->getTable())->ignore($id)
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

        $account_branch = $check['account_branch'];
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
