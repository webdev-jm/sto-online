<?php

namespace App\Http\Controllers;

use App\Models\InventorySales;
use App\Http\Requests\StoreInventorySalesRequest;
use App\Http\Requests\UpdateInventorySalesRequest;

use App\Models\SMSAccount;
use App\Models\SMSBranch;

class InventorySalesController extends Controller
{

    public function branches($id) {
        $account = SMSAccount::findOrFail(decrypt($id));

        $branches = SMSBranch::orderBy('branch_code', 'ASC')
            ->where('account_id', $account->id)
            ->paginate(16, ['*'], 'branch-page')->onEachSide(1);

        return view('pages.inventory-sales.branches')->with([
            'account' => $account,
            'branches' => $branches
        ]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        $branch = SMSBranch::findOrFail(decrypt($id));
        
        return view('pages.iventory-sales.index')->with([
            'branch' => $branch
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreInventorySalesRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreInventorySalesRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\InventorySales  $inventorySales
     * @return \Illuminate\Http\Response
     */
    public function show(InventorySales $inventorySales)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\InventorySales  $inventorySales
     * @return \Illuminate\Http\Response
     */
    public function edit(InventorySales $inventorySales)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateInventorySalesRequest  $request
     * @param  \App\Models\InventorySales  $inventorySales
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateInventorySalesRequest $request, InventorySales $inventorySales)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\InventorySales  $inventorySales
     * @return \Illuminate\Http\Response
     */
    public function destroy(InventorySales $inventorySales)
    {
        //
    }
}
