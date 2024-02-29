<?php 

namespace App\Http\Traits;

use App\Models\AccountBranch;

trait ApiBranchKeyChecker {

    public function checkBranchKey($branch_key) {

        $err = array();

        if(empty(strlen(trim($branch_key)))) {
            $err['BRANCH_KEY'] = 'BRANCH KEY is required.';
        }

        $account_branch = AccountBranch::findByToken($branch_key);

        if(empty($account_branch)) {
            $err['INVALID_BRANCH_KEY'] = 'The provided BRANCH KEY is invalid.';
        }

        return [
            'status' => !empty($err),
            'account_branch' => $account_branch,
            'error' => $err,
        ];
    }
}