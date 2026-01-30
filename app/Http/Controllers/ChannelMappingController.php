<?php

namespace App\Http\Controllers;

use App\Models\ChannelMapping;
use App\Models\Account;
use Illuminate\Http\Request;

class ChannelMappingController extends Controller
{
    public function index(Request $request) {
        $accounts = Account::orderBy('account_code', 'asc')
            ->paginate(12);

        return view('pages.channel-mapping.index')->with([
            'accounts' => $accounts,
        ]);
    }

    public function entry($id) {
        $account = Account::findOrFail(decrypt($id));
        $channelMappings = ChannelMapping::where('account_id', $account->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('pages.channel-mapping.entry')->with([
            'account' => $account,
            'channelMappings' => $channelMappings,
        ]);
    }


}
