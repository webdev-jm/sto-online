<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\AccountBranch;

use App\Http\Resources\AccountBranchResource;
use App\Http\Traits\ApiBranchKeyChecker;

class AccountBranchController extends Controller
{
    use ApiBranchKeyChecker;

    public function index(Request $request) {
        $branches = auth()->user()->account_branches()
            ->paginate(10);

        return AccountBranchResource::collection($branches);
    }

    public function generateKey(Request $request) {
        $check = $this->checkBranchKey($request->header('BRANCH-KEY'));
        
        if(!empty($check['error'])) {
            return $this->validationError($check['error']);
        }

        $account_branch = $check['account_branch'];

        $account_branch->generateBranchToken();

        return $this->successResponse($account_branch);
    }
}
