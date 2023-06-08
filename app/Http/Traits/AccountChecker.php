<?php
namespace App\Http\Traits;

use Illuminate\Support\Facades\Session;

trait AccountChecker {

    public function check() {
        // check session
        if(empty(Session::get('account'))) {
            return redirect()->route('home');
        }
    }
}