<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\InventoryUpload;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

use App\Http\Traits\AccountChecker;

class InventoryController extends Controller
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

        $inventory_uploads = InventoryUpload::orderBy('created_at', 'DESC')
            ->with('user')
            ->where('account_id', $account->id)
            ->where('account_branch_id', $account_branch->id)
            ->when(!empty($search), function($query) use($search) {
                $query->whereIn('user', function($qry) use($search) {
                    $qry->where('name', 'like', '%'.$search.'%');
                });
            })
            ->paginate(10)->onEachSide(1)
            ->appends(request()->query());

        return view('pages.inventories.index')->with([
            'inventory_uploads' => $inventory_uploads,
            'account' => $account,
            'account_branch' => $account_branch,
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

        return view('pages.inventories.create')->with([
            'account' => $account,
            'account_branch' => $account_branch
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Inventory  $inventory
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

        $inventory_upload = InventoryUpload::findOrFail(decrypt($id));

        $inventory_locations = DB::table('inventories as i')
            ->select(
                'l.code',
                DB::raw('SUM(i.inventory) as total')
            )
            ->leftJoin('locations as l', 'l.id', '=', 'i.location_id')
            ->where('i.account_id', $account->id)
            ->where('i.account_branch_id', $account_branch->id)
            ->where('i.inventory_upload_id', $inventory_upload->id)
            ->groupBy('l.code')
            ->get();

        return view('pages.inventories.show')->with([
            'inventory_upload' => $inventory_upload,
            'account' => $account,
            'account_branch' => $account_branch,
            'inventory_locations' => $inventory_locations
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Inventory  $inventory
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

        $inventory_upload = InventoryUpload::findOrFail(decrypt($id));

        $inventory_locations = DB::table('inventories as i')
            ->select(
                'l.code',
                DB::raw('SUM(i.inventory) as total')
            )
            ->leftJoin('locations as l', 'l.id', '=', 'i.location_id')
            ->where('i.account_id', $account->id)
            ->where('i.account_branch_id', $account_branch->id)
            ->where('i.inventory_upload_id', $inventory_upload->id)
            ->groupBy('l.code')
            ->get();

        return view('pages.inventories.edit')->with([
            'inventory_upload' => $inventory_upload,
            'account' => $account,
            'account_branch' => $account_branch,
            'inventory_locations' => $inventory_locations
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Inventory  $inventory
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Inventory $inventory)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Inventory  $inventory
     * @return \Illuminate\Http\Response
     */
    public function destroy(Inventory $inventory)
    {
        //
    }
}
