<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use App\Http\Requests\LocationAddRequest;
use App\Http\Requests\LocationUpdateRequest;

use Illuminate\Support\Facades\Session;

use App\Http\Traits\AccountChecker;

class LocationController extends Controller
{
    use AccountChecker;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

        $search = trim($request->get('search'));

        $locations = Location::orderBy('created_at', 'DESC')
            ->when(auth()->user()->can('location restore'), function($query) {
                $query->withTrashed();
            })
            ->where('account_id', $account->id)
            ->where('account_branch_id', $account_branch->id)
            ->when(!empty($search), function($query) use($search) {
                $query->where('code', 'like', '%'.$search.'%')
                    ->orWhere('name', 'like', '%'.$search.'%');
            })
            ->paginate(10, ['*'], 'location-page')->onEachSide(1)
            ->appends(request()->query());

        return view('pages.locations.index')->with([
            'locations' => $locations,
            'account' => $account,
            'account_branch' => $account_branch,
            'search' => $search,
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
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

        return view('pages.locations.create')->with([
            'account' => $account,
            'account_branch' => $account_branch,
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
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

        $location = new Location([
            'account_id' => $account->id,
            'account_branch_id' => $account_branch->id,
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
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');
 
         $location = Location::findOrFail(decrypt($id));

         return view('pages.locations.show')->with([
            'account' => $account,
            'account_branch' => $account_branch,
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
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

        $location = Location::findOrFail(decrypt($id));

        return view('pages.locations.edit')->with([
            'account' => $account,
            'account_branch' => $account_branch,
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
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

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

        return redirect()->route('location.show', encrypt($location->id))->with([
            'message_success' => 'Location '.$location->name.' was updated.'
        ]);
    }

    public function restore($id) {
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

        $location = Location::withTrashed()->findOrFail(decrypt($id));

        $location->restore();

        activity('restore')
            ->performedOn($location)
            ->log(':causer.name has restored location '.$location->name);

        return back()->with([
            'message_success' => 'Location '.$location->name.' has been restored.'
        ]);
    }
}
