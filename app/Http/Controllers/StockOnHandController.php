<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockOnHand;

use Illuminate\Support\Facades\Session;

use App\Http\Traits\AccountChecker;

class StockOnHandController extends Controller
{
    use AccountChecker;

    public function index(Request $request) {
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

        $stock_on_hands = StockOnHand::where('account_branch_id', $account_branch->id)
            ->paginate(10)->onEachSide(1);

        return view('pages.stock-on-hands.index')->with([
            'account' => $account,
            'account_branch' => $account_branch,
            'stock_on_hands' => $stock_on_hands,
        ]);

    }

    public function upload() {
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

        return view('pages.stock-on-hands.upload')->with([
            'account' => $account,
            'account_branch' => $account_branch,
        ]);
    }
}
