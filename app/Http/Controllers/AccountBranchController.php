<?php

namespace App\Http\Controllers;

use App\Models\AccountBranch;
use App\Models\SMSAccount;
use App\Models\BeviArea;

use App\Http\Requests\AccountBranchAddRequest;
use App\Http\Requests\AccountBranchEditRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class AccountBranchController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $search = trim($request->get('search') ?? '');

        $account_branches = AccountBranch::orderBy('created_at', 'DESC')
            ->with('account')
            ->when(!empty($search), function($query) use($search) {
                $query->whereHas('account', function($qry) use($search) {
                    $qry->where('short_name', 'like', '%'.$search.'%')
                        ->orWhere('account_code', 'like', '%'.$search.'%');
                })
                ->orWhere('code', 'like', '%'.$search.'%')
                ->orWhere('name', 'like', '%'.$search.'%');
            })
            ->paginate(10)->onEachSide(1)
            ->appends(request()->query());

        return view('pages.account-branches.index')->with([
            'search' => $search,
            'account_branches' => $account_branches
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $bevi_areas = BeviArea::get();
        $areas = array();
        foreach($bevi_areas as $area) {
            $areas[$area->id] = '['.$area->code.'] '.$area->name;
        }

        return view('pages.account-branches.create')->with([
            'areas' => $areas
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\AccountBranchAddRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(AccountBranchAddRequest $request)
    {
        $account_branch = new AccountBranch([
            'account_id' => $request->account_id,
            'bevi_area_id' => $request->bevi_area_id,
            'code' => $request->code,
            'name' => $request->name
        ]);
        $account_branch->save();

        $schema = 'kojiesanadmin_sto_online_'.$request->account_id.'_db';

        // create database
        DB::statement('CREATE DATABASE IF NOT EXISTS '.$schema);

        \Config::set('database.connections.account_'.$request->account_id.'_db', [
            'driver' => 'mysql',
            'url' => NULL,
            'host' => '127.0.0.1',
            'port' => 3306,
            'database' => $schema,
            'username' => 'root',
            'password' => '',
            'unix_socket' => '',
            'charset' => 'utf8',
            'collation' => 'utf8_general_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => 'InnoDB',
            'pool' => [
                'min_connections' => 1,
                'max_connections' => 10,
                'max_idle_time' => 30,
            ],
        ]);

        DB::setDefaultConnection('account_'.$request->account_id.'_db');
        Artisan::call('migrate');
        DB::setDefaultConnection(config('database.default'));

        // logs
        activity('create')
        ->performedOn($account_branch)
        ->log(':causer.name has created account branch :subject.name');

        return redirect()->route('account-branch.index')->with([
            'message_success' => 'Branch '.$account_branch->name.' was created.'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AccountBranch  $accountBranch
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $account_branch = AccountBranch::findOrFail(decrypt($id));
        
        return view('pages.account-branches.show')->with([
            'account_branch' => $account_branch
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AccountBranch  $accountBranch
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $account_branch = AccountBranch::findOrFail(decrypt($id));

        $bevi_areas = BeviArea::get();
        $areas = array();
        foreach($bevi_areas as $area) {
            $areas[$area->id] = '['.$area->code.'] '.$area->name;
        }

        return view('pages.account-branches.edit')->with([
            'account_branch' => $account_branch,
            'areas' => $areas
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AccountBranch  $accountBranch
     * @return \Illuminate\Http\Response
     */
    public function update(AccountBranchEditRequest $request, $id)
    {
        $account_branch = AccountBranch::findOrFail(decrypt($id));

        $changes_arr['old'] = $account_branch->getOriginal();

        $account_branch->update([
            'account_id' => $request->account_id,
            'bevi_area_id' => $request->bevi_area_id,
            'code' => $request->code,
            'name' => $request->name
        ]);
        
        $changes_arr['changes'] = $account_branch->getChanges();

        activity('update')
        ->performedOn($account_branch)
        ->withProperties($changes_arr)
        ->log(':causer.name has updated account branch :subject.name');

        return redirect()->route('account-branch.show', encrypt($account_branch->id))->with([
            'message_success' => 'Branch '.$account_branch->name.' was updated.'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AccountBranch  $accountBranch
     * @return \Illuminate\Http\Response
     */
    public function destroy(AccountBranch $accountBranch)
    {
        //
    }

    public function ajax(Request $request) {
        $search = $request->search;
        $response = SMSAccount::AccountAjax($search);
        return response()->json($response);
    }

    public function getAjax($id) {
        $account = SMSAccount::findOrFail($id);
        return response()->json($account);
    }

    public function generateToken($id) {
        $id = decrypt($id);
        $account_branch = AccountBranch::findOrFail($id);

        $account_branch->generateBranchToken();

        return redirect()->route('account-branch.show', encrypt($account_branch->id))->with([
            'message_success' => 'New token has been generated. '
        ]);
    }
}
