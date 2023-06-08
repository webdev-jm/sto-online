<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Http\Requests\AreaAddRequest;
use App\Http\Requests\AreaUpdateRequest;

use Illuminate\Support\Facades\Session;

class AreaController extends Controller
{
    public function checkAccount() {
        $account = Session::get('account');
        if(empty($account)) {
            return redirect()->route('home');
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

        $areas = Area::orderBy('created_at', 'DESC')
            ->where('account_id', $account->id)
            ->paginate(10)->onEachSide(1);

        return view('pages.areas.index')->with([
            'account' => $account,
            'areas' => $areas
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

        return view('pages.areas.create')->with([
            'account' => $account
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\AreaAddRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(AreaAddRequest $request)
    {
        // check account
        $account = $this->checkAccount();
        
        $area = new Area([
            'account_id' => $account->id,
            'code' => $request->code,
            'name' => $request->name
        ]);
        $area->save();

         // logs
         activity('create')
         ->performedOn($area)
         ->log(':causer.name has created area ['.$account->short_name.'] :subject.code :subject.name');

        return redirect()->route('area.index')->with([
            'message_success' => 'Area '.$area->name.' was created.'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Area  $area
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // check account
        $account = $this->checkAccount();

        $area = Area::findOrFail(decrypt($id));

        return view('pages.areas.show')->with([
            'account' => $account,
            'area' => $area
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Area  $area
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // check account
        $account = $this->checkAccount();

        $area = Area::findOrfail(decrypt($id));

        return view('pages.areas.edit')->with([
            'account' => $account,
            'area' => $area
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\AreaUpdateRequest  $request
     * @param  \App\Models\Area  $area
     * @return \Illuminate\Http\Response
     */
    public function update(AreaUpdateRequest $request, $id)
    {
         // check account
         $account = $this->checkAccount();

         $area = Area::findOrfail(decrypt($id));
         $changes_arr['old'] = $area->getOriginal();

         $area->update([
            'code' => $request->code,
            'name' => $request->name
         ]);

         $changes_arr['changes'] = $area->getChanges();

         // logs
        activity('update')
        ->performedOn($area)
        ->withProperties($changes_arr)
        ->log(':causer.name has updated user ['.$account->short_name.'] :subject.code :subject.name');

         return back()->with([
            'message_success' => 'Area '.$area->name.' has been updated.'
         ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Area  $area
     * @return \Illuminate\Http\Response
     */
    public function destroy(Area $area)
    {
        //
    }
}
