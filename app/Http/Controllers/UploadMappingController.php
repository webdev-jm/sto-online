<?php

namespace App\Http\Controllers;

use App\Models\Account;

class UploadMappingController extends Controller
{
    public function index()
    {
        $accounts = Account::orderBy('account_code', 'asc')
            ->paginate(12);

        return view('pages.upload-mapping.index')->with([
            'accounts' => $accounts,
        ]);
    }

    public function entry($id)
    {
        $account = Account::findOrFail(decrypt($id));

        return view('pages.upload-mapping.entry')->with([
            'account' => $account,
        ]);
    }
}
