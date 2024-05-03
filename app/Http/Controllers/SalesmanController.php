<?php

namespace App\Http\Controllers;

use App\Models\Salesman;
use App\Models\District;
use Illuminate\Http\Request;

use App\Http\Requests\SalesmanAddRequest;
use App\Http\Requests\SalesmanUpdateRequest;

use Illuminate\Support\Facades\Session;

use App\Http\Traits\AccountChecker;

class SalesmanController extends Controller
{
    use AccountChecker;

    public $salesman_types_arr = [
        'DIRECT BOOKING' => 'DIRECT BOOKING',
        'VAN SALESMAN' => 'VAN SALESMAN',
        'PRE-BOOKING' => 'PRE-BOOKING',
    ];

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

        $salesmen = Salesman::orderBy('created_at', 'DESC')
            ->when(auth()->user()->can('salesman restore'), function($query) {
                $query->withTrashed();
            })
            ->where('account_id', $account->id)
            ->where('account_branch_id', $account_branch->id)
            ->when(!empty($search), function($query) use($search) {
                $query->where(function($qry) use($search) {
                    $qry->where('code', 'like', '%'.$search.'%')
                        ->orWhere('name', 'like', '%'.$search.'%');
                });
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

        $districts = District::where('account_branch_id', $account_branch->id)->get();
        $districts_arr = array();
        foreach($districts as $district) {
            $districts_arr[$district->id] = $district->district_code;
        }

        return view('pages.salesmen.create')->with([
            'account' => $account,
            'account_branch' => $account_branch,
            'districts' => $districts_arr,
            'salesman_types_arr' => $this->salesman_types_arr
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
            'district_id' => $request->district_id,
            'code' => $request->code,
            'name' => $request->name,
            'type' => $request->type
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

        $districts = District::where('account_branch_id', $account_branch->id)->get();
        $districts_arr = array();
        foreach($districts as $district) {
            $districts_arr[$district->id] = $district->district_code;
        }

        return view('pages.salesmen.edit')->with([
            'account' => $account,
            'account_branch' => $account_branch,
            'salesman' => $salesman,
            'districts' => $districts_arr,
            'salesman_types_arr' => $this->salesman_types_arr
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
            'district_id' => $request->district_id,
            'code' => $request->code,
            'name' => $request->name,
            'type' => $request->type
        ]);

        $changes_arr['changes'] = $salesman->getChanges();

        // logs
        activity('update')
            ->performedOn($salesman)
            ->withProperties($changes_arr)
            ->log(':causer.name has updated salesman ['.$account->short_name.'] :subject.code :subject.name');

        return redirect()->route('salesman.show', encrypt($salesman->id))->with([
            'message_success' => 'Salesman '.$salesman->name.' was updated.'
        ]);
    }

    public function restore($id) {
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

        $salesman = Salesman::withTrashed()->findOrFail(decrypt($id));

        $salesman->restore();

        activity('restore')
            ->performedOn($salesman)
            ->log(':causer.name has restored salesman '.$salesman->name);

        return back()->with([
            'message_success' => 'Salesman '.$salesman->name.' has been restored.'
        ]);
    }
}
