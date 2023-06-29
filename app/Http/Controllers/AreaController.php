<?php

namespace App\Http\Controllers;

use App\Models\Area;
use Illuminate\Http\Request;
use App\Http\Requests\AreaAddRequest;
use App\Http\Requests\AreaUpdateRequest;

use Illuminate\Support\Facades\Session;

use App\Http\Traits\AccountChecker;

class AreaController extends Controller
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

        $areas = Area::orderBy('created_at', 'DESC')
            ->where('account_id', $account->id)
            ->where('account_branch_id', $account_branch->id)
            ->when(!empty($search), function($query) use($search) {
                $query->where('code', 'like', '%'.$search.'%')
                    ->orWhere('name', 'like', '%'.$search.'%');
            })
            ->paginate(10)->onEachSide(1)
            ->appends(request()->query());

        return view('pages.areas.index')->with([
            'account' => $account,
            'account_branch' => $account_branch,
            'areas' => $areas,
            'search' => $search
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

        return view('pages.areas.create')->with([
            'account' => $account,
            'account_branch' => $account_branch
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
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');
        
        $area = new Area([
            'account_id' => $account->id,
            'account_branch_id' => $account_branch->id,
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
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

        $area = Area::findOrFail(decrypt($id));

        return view('pages.areas.show')->with([
            'account' => $account,
            'account_branch' => $account_branch,
            'area' => $area,
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
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

        $area = Area::findOrfail(decrypt($id));

        return view('pages.areas.edit')->with([
            'account' => $account,
            'account_branch' => $account_branch,
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
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

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
        ->log(':causer.name has updated area ['.$account->short_name.'] :subject.code :subject.name');

         return redirect()->route('area.show', encrypt($area->id))->with([
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
