<?php

namespace App\Http\Controllers;

use App\Models\Salesman;
use App\Models\Area;
use Illuminate\Http\Request;

use App\Http\Requests\SalesmanAddRequest;
use App\Http\Requests\SalesmanUpdateRequest;

use Illuminate\Support\Facades\Session;

class SalesmanController extends Controller
{
    private function checkAccount() {
        $account = Session::get('account');
        if(!isset($account) || empty($account)) {
            return redirect()->route('home')->with([
                'error_message' => 'Please select an account.'
            ]);
        }
    
        return $account;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // check account
        $account = $this->checkAccount();
        if ($account instanceof \Illuminate\Http\RedirectResponse) {
            return $account; // Redirect response, so return it directly
        }

        $salesmen = Salesman::orderBy('created_at', 'DESC')
            ->where('account_id', $account->id)
            ->paginate(10)->onEachSide(1);

        return view('pages.salesmen.index')->with([
            'account' => $account,
            'salesmen' => $salesmen
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // check account
        $account = $this->checkAccount();
        if ($account instanceof \Illuminate\Http\RedirectResponse) {
            return $account; // Redirect response, so return it directly
        }

        $areas = Area::where('account_id', $account->id)->get();
        $area_arr = array();
        foreach($areas as $area) {
            $area_arr[$area->id] = '['.$area->code.'] '.$area->name;
        }

        return view('pages.salesmen.create')->with([
            'account' => $account,
            'areas' => $area_arr
        ]);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\SalesmanAddRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(SalesmanAddRequest $request)
    {
        // check account
        $account = $this->checkAccount();
        if ($account instanceof \Illuminate\Http\RedirectResponse) {
            return $account; // Redirect response, so return it directly
        }

        $salesman = new Salesman([
            'account_id' => $account->id,
            'area_id' => $request->area_id,
            'code' => $request->code,
            'name' => $request->name
        ]);
        $salesman->save();

        // logs
        activity('create')
        ->performedOn($salesman)
        ->log(':causer.name has created saleman ['.$account->short_name.'] :subject.code :subject.name');

        return redirect()->route('salesman.index')->with([
            'message_success' => 'Salesman '.$saleman->name.' was created.'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Salesman  $salesman
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // check account
        $account = $this->checkAccount();
        if ($account instanceof \Illuminate\Http\RedirectResponse) {
            return $account; // Redirect response, so return it directly
        }

        $salesman = Salesman::findOrFail(decrypt($id));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Salesman  $salesman
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // check account
        $account = $this->checkAccount();
        if ($account instanceof \Illuminate\Http\RedirectResponse) {
            return $account; // Redirect response, so return it directly
        }

        $salesman = Salesman::findOrFail(decrypt($id));

        $areas = Area::where('account_id', $account->id)->get();
        $area_arr = array();
        foreach($areas as $area) {
            $area_arr[$area->id] = '['.$area->code.'] '.$area->name;
        }

        return view('pages.salesmen.edit')->with([
            'account' => $account,
            'salesman' => $salesman,
            'areas' => $area_arr
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\SalesmanUpdateRequest  $request
     * @param  \App\Models\Salesman  $salesman
     * @return \Illuminate\Http\Response
     */
    public function update(SalesmanUpdateRequest $request, $id)
    {
        // check account
        $account = $this->checkAccount();
        if ($account instanceof \Illuminate\Http\RedirectResponse) {
            return $account; // Redirect response, so return it directly
        }

        $salesman = Salesman::findOrFail(decrypt($id));
        $changes_arr['old'] = $salesman->getOriginal();

        $salesman->update([
            'area_id' => $request->area_id,
            'code' => $request->code,
            'name' => $request->name
        ]);

        $changes_arr['changes'] = $salesman->getChanges();

        // logs
        activity('update')
        ->performedOn($salesman)
        ->withProperties($changes_arr)
        ->log(':causer.name has updated salesman ['.$account->short_name.'] :subject.code :subject.name');

        return back()->with([
            'message_success' => 'Salesman '.$salesman->name.' was updated.'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Salesman  $salesman
     * @return \Illuminate\Http\Response
     */
    public function destroy(Salesman $salesman)
    {
        //
    }
}
