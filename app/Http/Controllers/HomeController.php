<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SMSAccount;
use App\Models\SMSBranch;
use App\Models\AccountBranch;
use App\Models\Sale;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

use App\Http\Traits\AccountChecker;

class HomeController extends Controller
{
    use AccountChecker;

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
     * @param Request $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        $search = trim($request->get('search') ?? '');

        Session::forget('account');

        $accounts = DB::connection('mysql')
            ->table('account_user as au')
            ->join(env('DB_DATABASE_2').'.accounts as a', 'a.id', '=', 'au.account_id')
            ->where('au.user_id', auth()->user()->id)
            ->when(!empty($search), function($query) use($search) {
                $query->where(function($qry) use($search) {
                    $qry->where('a.short_name', 'like', '%'.$search.'%')
                        ->orWhere('a.account_code', 'like', '%'.$search.'%');
                });
            })
            ->paginate(16, ['*'], 'account-page')->onEachSide(1);

        return view('home')->with([
            'accounts' => $accounts,
            'search' => $search
        ]);
    }

    /**
     * Display the application menu.
     *
     * @param $id
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function appMenu($id)
    {
        $account_branch = AccountBranch::findOrFail(decrypt($id));
        $check = auth()->user()->account_branches()->where('account_branch_id', $account_branch->id)->exists();
        if (!$check) {
            return redirect()->route('branches', encrypt($account_branch->id))->with([
                'message_error' => 'This branch was not assigned to you. Please message the system admin for assistance.'
            ]);
        }

        session()->put('account_branch', $account_branch);

        // Sales Report Data
        $results = Sale::select(
                DB::raw('MONTH(date) as month'),
                DB::raw('SUM(IF(category = 0, amount, NULL)) as sales_total'),
                DB::raw('SUM(IF(category = 1, amount, NULL)) as cm_total')
            )
            ->where('account_id', $account_branch->account->id)
            ->where('account_branch_id', $account_branch->id)
            ->where('type', 1) // 1 default 2 FG 3 PROMO
            ->groupBy('month')
            ->orderBy('month', 'ASC')
            ->get();
        
        $sales_data = array();
        $cm_data = array();
        $categories = array();
        foreach($results as $result) {
            $sales_data[] = (float)$result->sales_total;
            $cm_data[] = (float)$result->cm_total;
            $categories[] = date('F', strtotime('2023-'.($result->month < 10 ? '0'.(int)$result->month : $result->month).'-01'));
        }

        array_unique($categories);

        $chart_data = [
            [
                'name' => 'Sales',
                'data' => $sales_data
            ],
            [
                'name' => 'Credit Memo',
                'data' => $cm_data
            ],
        ];

        return view('pages.app-menu.index')->with([
            'account' => $account_branch->account,
            'account_branch' => $account_branch,
            'categories' => $categories,
            'chart_data' => $chart_data
        ]);
    }

    /**
     * Display the branches for a given account.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function branches(Request $request, $id)
    {
        Session::forget('account_branch');

        $account = SMSAccount::findOrFail(decrypt($id));
        $check = auth()->user()->accounts()->where('id', $account->id)->exists();
        if (!$check) {
            return redirect()->route('home')->with([
                'message_error' => 'This account was not assigned to you. Please message the system admin for assistance.'
            ]);
        }

        session()->put('account', $account);

        $search = trim($request->get('search') ?? '');

        $branches = auth()->user()
            ->account_branches()
            ->where('account_id', $account->id)
            ->where(function ($query) use ($search) {
                $query->where('code', 'like', '%'.$search.'%')
                    ->orWhere('name', 'like', '%'.$search.'%');
            })
            ->orderBy('created_at', 'DESC')
            ->paginate(10)
            ->onEachSide(1)
            ->appends(request()->query());

        return view('branches')->with([
            'account' => $account,
            'search' => $search,
            'branches' => $branches,
        ]);
    }

    /**
     * Display the user profile.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function profile()
    {
        return view('profile');
    }
}
