<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

use App\Models\StockTransfer;

use App\Http\Traits\AccountChecker;

class StockTransferController extends Controller
{
    use AccountChecker;
    
    public function index() {
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

        $stock_transfers = StockTransfer::where('account_branch_id', $account_branch->id)
            ->paginate(20)
            ->onEachSide(1);

        return view('pages.stock-transfers.index')->with([
            'account' => $account,
            'account_branch' => $account_branch,
            'stock_transfers' => $stock_transfers
        ]);
    }

    public function upload() {
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

        return view('pages.stock-transfers.upload')->with([
            'account' => $account,
            'account_branch' => $account_branch
        ]);
    }
}
