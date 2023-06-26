<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\InventoryUpload;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
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
    public function index(Request $request)
    {
        // check account
        $account = $this->checkAccount();
        if ($account instanceof \Illuminate\Http\RedirectResponse) {
            return $account->with([
                'message_error' => 'Please select an account.'
            ]);
        }

        $search = trim($request->get('search'));

        $inventory_uploads = InventoryUpload::orderBy('created_at', 'DESC')
            ->where('account_id', $account->id)
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
        // check account
        $account = $this->checkAccount();
        if ($account instanceof \Illuminate\Http\RedirectResponse) {
            return $account->with([
                'message_error' => 'Please select an account.'
            ]);
        }

        return view('pages.inventories.create')->with([
            'account' => $account
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
        // check account
        $account = $this->checkAccount();
        if ($account instanceof \Illuminate\Http\RedirectResponse) {
            return $account->with([
                'message_error' => 'Please select an account.'
            ]);
        }

        $inventory_upload = InventoryUpload::findOrFail(decrypt($id));

        $inventory_locations = DB::table('inventories as i')
            ->select(
                'l.code',
                DB::raw('SUM(i.inventory) as total')
            )
            ->leftJoin('locations as l', 'l.id', '=', 'i.location_id')
            ->where('i.account_id', $account->id)
            ->where('i.inventory_upload_id', $inventory_upload->id)
            ->groupBy('l.code')
            ->get();

        return view('pages.inventories.show')->with([
            'inventory_upload' => $inventory_upload,
            'account' => $account,
            'inventory_locations' => $inventory_locations
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Inventory  $inventory
     * @return \Illuminate\Http\Response
     */
    public function edit(Inventory $inventory)
    {
        //
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
