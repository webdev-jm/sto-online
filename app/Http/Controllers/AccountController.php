<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountDatabase;
use App\Models\SMSAccount;
use Illuminate\Http\Request;
use App\Http\Requests\AccountAddRequest;
use App\Http\Requests\AccountEditRequest;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $search = trim($request->get('search'));

        $accounts = Account::orderBy('created_at', 'DESC')
            ->paginate(10)->onEachSide(1)
            ->appends(request()->query());
        
        return view('pages.accounts.index')->with([
            'search' => $search,
            'accounts' => $accounts
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('pages.accounts.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(AccountAddRequest $request)
    {
        $account = new Account([
            'sms_account_id' => $request->sms_account_id,
            'account_code' => $request->account_code,
            'account_name' => $request->account_name,
            'short_name' => $request->short_name,
            'account_password' => Hash::make($request->password),
        ]);
        $account->save();

        $schema = 'kojiesanadmin_sto_online_'.$account->id.'_db';

        // create database
        DB::statement('CREATE DATABASE IF NOT EXISTS '.$schema);

        \Config::set('database.connections.account_'.$account->id.'_db', [
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

        DB::setDefaultConnection('account_'.$account->id.'_db');
        Artisan::call('migrate', ['--path' => '\database/migrations/account_migrations']);

        DB::setDefaultConnection('mysql');

        $account_db = new AccountDatabase([
            'account_id' => $account->id,
            'database_name' => $schema,
            'connection_name' => 'account_'.$account->id.'_db',
        ]);
        $account_db->save();

        // logs
        activity('create')
            ->performedOn($account)
            ->log(':causer.name has created account :subject.account_code');
        

        return redirect()->route('account.index')->with([
            'message_success' => 'Account '.$account->account_code.' has been created successfully.'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Account  $account
     * @return \Illuminate\Http\Response
    */
    public function show($id)
    {
        $id = decrypt($id);
        $account = Account::FindOrFail($id);

        return view('pages.accounts.show')->with([
            'account' => $account
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Account  $account
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $id = decrypt($id);
        $account = Account::findOrFail($id);

        return view('pages.accounts.edit')->with([
            'account' => $account
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Account  $account
     * @return \Illuminate\Http\Response
     */
    public function update(AccountEditRequest $request, $id)
    {
        $id = decrypt($id);
        $account = Account::findOrFail($id);

        $changes_arr['old'] = $account->getOriginal();

        $password = $account->password;
        if(!empty($request->password)) {
            $password = Hash::make($request->password);
        }

        $account->update([
            'sms_account_id' => $request->sms_account_id,
            'account_code' => $request->account_code,
            'account_name' => $request->account_name,
            'short_name' => $request->short_name,
            'password' => $password
        ]);

        $changes_arr['changes'] = $account->getChanges();

        // logs
        activity('update')
            ->performedOn($account)
            ->withProperties($changes_arr)
            ->log(':causer.name has updated account :subject.account_code');

        return redirect()->route('account.show', encrypt($account->id))->with([
            'message_success' => 'Account '.$account->account_code.' has been updated.'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Account  $account
     * @return \Illuminate\Http\Response
     */
    public function destroy(Account $account)
    {
        //
    }

    public function smsAjax(Request $request) {
        $search = $request->search;
        $response = SMSAccount::AccountAjax($search);
        return response()->json($response);
    }

    public function smsGetAjax($id) {
        $account = SMSAccount::findOrFail($id);
        return response()->json($account);
    }

}
