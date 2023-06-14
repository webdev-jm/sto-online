<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SMSAccount;
use App\Models\SMSBranch;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        $search = trim($request->get('search'));

        Session::forget('account');

        $accounts = DB::connection('mysql')
            ->table('account_user as au')
            ->join(env('DB_DATABASE_2').'.accounts as a', 'a.id', '=', 'au.account_id')
            ->where('au.user_id', auth()->user()->id)
            ->when(!empty($search), function($query) use($search) {
                $query->where('a.short_name', 'like', '%'.$search.'%')
                    ->orWhere('a.account_code', 'like', '%'.$search.'%');
            })
            ->paginate(16, ['*'], 'account-page')->onEachSide(1);

        return view('home')->with([
            'accounts' => $accounts,
            'search' => $search
        ]);
    }

    public function appMenu($id) {
        $account = SMSAccount::findOrFail(decrypt($id));

        // check if account is assigned to user
        $check = auth()->user()->accounts()->where('id', $account->id)->first();
        if(empty($check)) {
            return redirect()->route('home')->with([
                'message_error' => 'this account was not assigned to you. Please message the system admin for more details.'
            ]);
        }

        Session::put('account', $account);

        return view('pages.app-menu.index')->with([
            'account' => $account
        ]);
    }

    public function profile() {
        return view('profile');
    }
}
