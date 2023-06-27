<?php
namespace App\Http\Traits;

use Illuminate\Support\Facades\Session;

trait AccountChecker {

     /**
     * Check if an account is selected.
     *
     * @return \Illuminate\Http\RedirectResponse|string|null
     */
    public function checkAccount()
    {
        $account = session('account');
        if (!isset($account) || empty($account)) {
            return redirect()->route('home')->with([
                'error_message' => 'Please select an account.'
            ]);
        }

        return $account;
    }

    /**
     * Check if a branch is selected.
     *
     * @return \Illuminate\Http\RedirectResponse|AccountBranch|string|null
     */
    public function checkBranch()
    {
        $account = $this->checkAccount();
        if ($account instanceof \Illuminate\Http\RedirectResponse) {
            return $account->with([
                'message_error' => 'Please select an account.'
            ]);
        }

        $account_branch = session('account_branch');
        if (!isset($account_branch) || empty($account_branch)) {
            return redirect()->route('branches', encrypt($account->id))->with([
                'message_error' => 'Please select a branch.'
            ]);
        }

        return $account_branch;
    }
}