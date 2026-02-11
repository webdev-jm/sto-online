<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Session;

use App\Http\Traits\AccountChecker;

class ReportController extends Controller
{
    use AccountChecker;

    public function index() {

        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

        return view('pages.reports.index')->with([
            'account_branch' => $account_branch,
            'account' => $account
        ]);
    }
    public function vmi_report() {
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

        return view('pages.reports.vmi')->with([
            'account_branch' => $account_branch,
            'account' => $account
        ]);
    }

    public function sto_report() {
        return view('pages.reports.sto');
    }
}
