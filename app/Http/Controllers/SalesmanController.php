<?php

namespace App\Http\Controllers;

use App\Models\Salesman;
use App\Models\Area;
use Illuminate\Http\Request;

use App\Http\Requests\SalesmanAddRequest;
use App\Http\Requests\SalesmanUpdateRequest;

use Illuminate\Support\Facades\Session;

use App\Http\Traits\AccountChecker;

class SalesmanController extends Controller
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

        $salesmen = Salesman::orderBy('created_at', 'DESC')
            ->where('account_id', $account->id)
            ->where('account_branch_id', $account_branch->id)
            ->when(!empty($search), function($query) use($search) {
                $query->where('code', 'like', '%'.$search.'%')
                    ->orWhere('name', 'like', '%'.$search.'%');
            })
            ->paginate(10)->onEachSide(1)
            ->appends(request()->query());

        return view('pages.salesmen.index')->with([
            'account' => $account,
            'account_branch' => $account_branch,
            'salesmen' => $salesmen,
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

        $areas = Area::where('account_id', $account->id)->get();
        $area_arr = array();
        foreach($areas as $area) {
            $area_arr[$area->id] = '['.$area->code.'] '.$area->name;
        }

        return view('pages.salesmen.create')->with([
            'account' => $account,
            'account_branch' => $account_branch,
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
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

        $salesman = new Salesman([
            'account_id' => $account->id,
            'account_branch_id' => $account_branch->id,
            'code' => $request->code,
            'name' => $request->name
        ]);
        $salesman->save();

        // logs
        activity('create')
        ->performedOn($salesman)
        ->log(':causer.name has created saleman ['.$account->short_name.'] :subject.code :subject.name');

        return redirect()->route('salesman.index')->with([
            'message_success' => 'Salesman '.$salesman->name.' was created.'
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
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

        $salesman = Salesman::findOrFail(decrypt($id));

        return view('pages.salesmen.show')->with([
            'account' => $account,
            'account_branch' => $account_branch,
            'salesman' => $salesman
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Salesman  $salesman
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

        $salesman = Salesman::findOrFail(decrypt($id));

        $areas = Area::where('account_id', $account->id)->get();
        $area_arr = array();
        foreach($areas as $area) {
            $area_arr[$area->id] = '['.$area->code.'] '.$area->name;
        }

        return view('pages.salesmen.edit')->with([
            'account' => $account,
            'account_branch' => $account_branch,
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
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

        $salesman = Salesman::findOrFail(decrypt($id));
        $changes_arr['old'] = $salesman->getOriginal();

        $salesman->update([
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
