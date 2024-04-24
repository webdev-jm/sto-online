<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Models\Area;
use Illuminate\Http\Request;

use App\Http\Requests\DistrictAddRequest;
use App\Http\Requests\DistrictEditRequest;

use Illuminate\Support\Facades\Session;

use App\Http\Traits\AccountChecker;

class DistrictController extends Controller
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

        $search = trim($request->get('search') ?? '');

        $districts = District::orderBy('created_at', 'DESC')
            ->when(auth()->user()->can('district restore'), function($query) {
                $query->withTrashed();
            })
            ->where('account_branch_id', $account_branch->id)
            ->when(!empty($search), function($query) use($search) {
                $query->where('district_code', 'like', '%'.$search.'%');
            })
            ->paginate(10)->onEachSide(1)
            ->appends(request()->query());
        
        return view('pages.districts.index')->with([
            'account' => $account,
            'account_branch' => $account_branch,
            'districts' => $districts,
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

        $areas = Area::get();

        return view('pages.districts.create')->with([
            'account' => $account,
            'account_branch' => $account_branch,
            'areas' => $areas
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(DistrictAddRequest $request)
    {
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

        $district = new District([
            'account_branch_id' => $account_branch->id,
            'district_code' => $request->district_code
        ]);
        $district->save();

        // assign areas
        $district->areas()->sync($request->areas);

        // log
        activity('create')
            ->performedOn($district)
            ->log(':causer.name has created a district :subject.code');

        return redirect()->route('district.index')->with([
            'message_success' => 'District '.$district->district_code.' has been created.'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\District  $district
     * @return \Illuminate\Http\Response
     */
    public function show(District $district)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\District  $district
     * @return \Illuminate\Http\Response
     */
    public function edit(District $district)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\District  $district
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, District $district)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\District  $district
     * @return \Illuminate\Http\Response
     */
    public function destroy(District $district)
    {
        //
    }
}
