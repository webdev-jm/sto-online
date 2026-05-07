<?php

namespace App\Http\Traits;

use App\Models\AccountBranch;

trait ApiBranchKeyChecker {

    public function checkBranchKey($branch_key) {

        $err = array();

        if(empty($branch_key)) {
            $err['BRANCH_KEY'] = 'BRANCH KEY is required.';
        }

        $account_branch = AccountBranch::findByToken($branch_key);

        if(empty($account_branch)) {
            $err['INVALID_BRANCH_KEY'] = 'The provided BRANCH KEY is invalid.';

            return [
                'status' => !empty($err),
                'account_branch' => $account_branch,
                'error' => $err,
            ];
        }

        if(! auth()->check()) {
            $err['UNAUTHENTICATED'] = 'User is not authenticated.';

            return [
                'status' => true,
                'account_branch' => null,
                'error' => $err,
            ];
        }

        if(empty($account_branch->account) || empty($account_branch->account->db_data)) {
            $err['ACCOUNT_CONFIG'] = 'Account database configuration is missing.';

            return [
                'status' => true,
                'account_branch' => null,
                'error' => $err,
            ];
        }

        // check if account branch is assigned to the user
        $check = $account_branch->users()->where('id', auth()->user()->id)->first();
        if(empty($check)) {
            $err['USER_ASSIGNED'] = 'Branch was not assigned to the user';
        }

        auth()->user()->update([
            'account_id' => $account_branch->account_id
        ]);

        return [
            'status' => !empty($err),
            'account_branch' => $account_branch,
            'error' => $err,
        ];
    }

    public function validationError($error) {
        return response()->json([
            'success' => false,
            'message' => 'Validation Error',
            'error' => $error,
        ], 422);
    }

    public function successResponse($data) {
        return response()->json([
            'success' => true,
            'data' => $data,
        ], 200);
    }
}
