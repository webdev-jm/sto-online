<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\PurchaseOrder;

use Illuminate\Support\Facades\Session;
use App\Http\Traits\AccountChecker;

class PurchaseOrderController extends Controller
{
    use AccountChecker;

    public function index() {
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

        Session::forget('po_upload_data');

        $purchase_orders = PurchaseOrder::where('account_branch_id', $account_branch->id)
            ->paginate(20)->onEachSide(1);

        return view('pages.purchase-orders.index')->with([
            'account' => $account,
            'account_branch' => $account_branch,
            'purchase_orders' => $purchase_orders
        ]);
    }

    public function upload() {
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

        return view('pages.purchase-orders.upload')->with([
            'account' => $account,
            'account_branch' => $account_branch,
        ]);
    }

    public function storeFiles(Request $request) {
        dd($request);
    }
}
