<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

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
    public function index()
    {
        $accounts = DB::connection('mysql')
            ->table('account_user as au')
            ->join(env('DB_DATABASE_2').'.accounts as a', 'a.id', '=', 'au.account_id')
            ->where('au.user_id', auth()->user()->id)
            ->paginate(16, ['*'], 'account-page')->onEachSide(1);

        return view('home')->with([
            'accounts' => $accounts
        ]);
    }

    public function profile() {
        return view('profile');
    }
}
