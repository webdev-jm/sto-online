<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductMapping;
use App\Models\Account;

class ProductMappingController extends Controller
{
    public function index(Request $request) {
        $accounts = Account::orderBy('account_code', 'asc')
            ->paginate(10);

        return view('pages.product-mapping.index')->with([
            'accounts' => $accounts,
        ]);
    }

    public function entry($id) {
        $account = Account::findOrFail(decrypt($id));
        $productMappings = ProductMapping::where('account_id', $account->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('pages.product-mapping.entry')->with([
            'account' => $account,
            'productMappings' => $productMappings,
        ]);
    }
}
