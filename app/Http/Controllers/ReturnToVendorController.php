<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

use App\Http\Traits\AccountChecker;

use App\Models\ReturnToVendor;

class ReturnToVendorController extends Controller
{
    use AccountChecker;
    
    public function index() {
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

        $return_to_vendors = ReturnToVendor::where('account_branch_id', $account_branch->id)
            ->paginate(10)
            ->onEachSide(1);

        return view('pages.return-to-vendors.index')->with([
            'account_branch' => $account_branch,
            'account' => $account,
            'return_to_vendors' => $return_to_vendors
        ]);
    }

    public function create() {
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

        return view('pages.return-to-vendors.create')->with([
            'account' => $account,
            'account_branch' => $account_branch
        ]);
    }

    public function upload() {
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

        return view('pages.return-to-vendors.upload')->with([
            'account' => $account,
            'account_branch' => $account_branch,
        ]);
    }

    public function store(Request $request) {

    }
}
