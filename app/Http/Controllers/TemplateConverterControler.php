<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Session;
use App\Http\Traits\AccountChecker;

class TemplateConverterControler extends Controller
{
    use AccountChecker;
    
    public function index() {
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

        return view('pages.template-converter.index')->with([
            'account' => $account,
            'account_branch' => $account_branch
        ]);
    }
}
