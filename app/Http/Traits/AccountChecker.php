<?php
namespace App\Http\Traits;

use Illuminate\Support\Facades\Session;

trait AccountChecker {

    public function checkAccount() {
        $account = Session::get('account');
        if(empty($account)) {
            return redirect()->route('home');
        } 
    
        return $account;
    }
}