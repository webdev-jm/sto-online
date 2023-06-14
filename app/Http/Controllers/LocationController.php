<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use App\Http\Requests\LocationAddRequest;
use App\Http\Requests\LocationUpdateRequest;

use Illuminate\Support\Facades\Session;

class LocationController extends Controller
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
            return $account->with([
                'message_error' => 'Please select an account.'
            ]);
        }

        $locations = Location::orderBy('created_at', 'DESC')
            ->where('account_id', $account->id)
            ->paginate(10)->onEachSide(1);

        return view('pages.locations.index')->with([
            'locations' => $locations,
            'account' => $account
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
            return $account->with([
                'message_error' => 'Please select an account.'
            ]);
        }

        return view('pages.locations.create')->with([
            'account' => $account
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\LocationAddRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(LocationAddRequest $request)
    {
        // check account
        $account = $this->checkAccount();
        if ($account instanceof \Illuminate\Http\RedirectResponse) {
            return $account->with([
                'message_error' => 'Please select an account.'
            ]);
        }

        $location = new Location([
            'account_id' => $account->id,
            'code' => $request->code,
            'name' => $request->name
        ]);
        $location->save();

        // logs
        activity('create')
        ->performedOn($location)
        ->log(':causer.name has created location ['.$account->short_name.'] :subject.code :subject.name');

        return redirect()->route('location.index')->with([
            'message_success' => 'Location '.$location->name.' was created.'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Location  $location
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
         // check account
         $account = $this->checkAccount();
         if ($account instanceof \Illuminate\Http\RedirectResponse) {
             return $account->with([
                 'message_error' => 'Please select an account.'
             ]);
         }
 
         $location = Location::findOrFail(decrypt($id));

         return view('pages.locations.show')->with([
            'account' => $account,
            'location' => $location
         ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Location  $location
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // check account
        $account = $this->checkAccount();
        if ($account instanceof \Illuminate\Http\RedirectResponse) {
            return $account->with([
                'message_error' => 'Please select an account.'
            ]);
        }

        $location = Location::findOrFail(decrypt($id));

        return view('pages.locations.edit')->with([
            'account' => $account,
            'location' => $location
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\LocationUpdateRequest  $request
     * @param  \App\Models\Location  $location
     * @return \Illuminate\Http\Response
     */
    public function update(LocationUpdateRequest $request, $id)
    {
        // check account
        $account = $this->checkAccount();
        if ($account instanceof \Illuminate\Http\RedirectResponse) {
            return $account->with([
                'message_error' => 'Please select an account.'
            ]);
        }

        $location = Location::findOrFail(decrypt($id));
        $changes_arr['old'] = $location->getOriginal();

        $location->update([
            'code' => $request->code,
            'name' => $request->name
        ]);

        $changes_arr['changes'] = $location->getChanges();

        // logs
        activity('update')
        ->performedOn($location)
        ->withProperties($changes_arr)
        ->log(':causer.name has updated location ['.$account->short_name.'] :subject.code :subject.name');

        return back()->with([
            'message_success' => 'Location '.$location->name.' was updated.'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Location  $location
     * @return \Illuminate\Http\Response
     */
    public function destroy(Location $location)
    {
        //
    }
}
