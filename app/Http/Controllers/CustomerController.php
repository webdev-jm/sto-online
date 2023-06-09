<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Area;
use App\Models\Channel;
use Illuminate\Http\Request;

use App\Http\Requests\CustomerAddRequest;
use App\Http\Requests\CustomerUpdateRequest;

use Illuminate\Support\Facades\Session;

class CustomerController extends Controller
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

        $customers = Customer::orderBy('created_at', 'DESC')
            ->where('account_id', $account->id)
            ->paginate(10)->onEachSide(1);

        return view('pages.customers.index')->with([
            'account' => $account,
            'customers' => $customers
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

        // AREA OPTIONS
        $areas = Area::where('account_id', $account->id)->get();
        $areas_arr = array();
        foreach($areas as $area) {
            $areas_arr[$area->id] = '['.$area->code.'] '.$area->name;
        }

        // CHANNEL OPTIONS
        $channels = Channel::where('account_id', $account->id)->get();
        $channel_arr = array();
        foreach($channels as $channel) {
            $channel_arr[$channel->id] = '['.$channel->code.'] '.$channel->name;
        }

        return view('pages.customers.create')->with([
            'account' => $account,
            'areas' => $areas_arr,
            'channels' => $channel_arr
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\CustomerAddRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CustomerAddRequest $request)
    {
        // check account
        $account = $this->checkAccount();
        if ($account instanceof \Illuminate\Http\RedirectResponse) {
            return $account->with([
                'message_error' => 'Please select an account.'
            ]);
        }

        $customer = new Customer([
            'account_id' => $account->id,
            'area_id' => $request->area_id,
            'channel_id' => $request->channel_id,
            'code' => $request->code,
            'name' => $request->name
        ]);
        $customer->save();
        
        // logs
        activity('create')
        ->performedOn($customer)
        ->log(':causer.name has created customer ['.$account->short_name.'] :subject.code :subject.name');

        return redirect()->route('customer.index')->with([
            'message_success' => 'Customer '.$customer->name.' was created.'
        ]);

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Customer  $customer
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

        $customer = Customer::findOrFail(decrypt($id));
        
        return view('pages.customers.show')->with([
            'account' => $account,
            'customer' => $customer
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Customer  $customer
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

        $customer = Customer::findOrFail(decrypt($id));

        // AREA OPTIONS
        $areas = Area::where('account_id', $account->id)->get();
        $areas_arr = array();
        foreach($areas as $area) {
            $areas_arr[$area->id] = '['.$area->code.'] '.$area->name;
        }

        // CHANNEL OPTIONS
        $channels = Channel::where('account_id', $account->id)->get();
        $channel_arr = array();
        foreach($channels as $channel) {
            $channel_arr[$channel->id] = '['.$channel->code.'] '.$channel->name;
        }

        return view('pages.customers.edit')->with([
            'account' => $account,
            'customer' => $customer,
            'areas' => $areas_arr,
            'channels' => $channel_arr
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\CustomerUpdateRequest  $request
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function update(CustomerUpdateRequest $request, $id)
    {
        // check account
        $account = $this->checkAccount();
        if ($account instanceof \Illuminate\Http\RedirectResponse) {
            return $account->with([
                'message_error' => 'Please select an account.'
            ]);
        }

        $customer = Customer::findOrFail(decrypt($id));
        $changes_arr['old'] = $customer->getOriginal();

        $customer->update([
            'area_id' => $request->area_id,
            'channel_id' => $request->channel_id,
            'code' => $request->code,
            'name' => $request->name,
        ]);

        $changes_arr['changes'] = $customer->getChanges();

        // logs
        activity('update')
        ->performedOn($customer)
        ->withProperties($changes_arr)
        ->log(':causer.name has updated customer ['.$account->short_name.'] :subject.code :subject.name');

        return back()->with([
            'message_success' => 'Customer '.$customer->name.' was updated.'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function destroy(Customer $customer)
    {
        //
    }
}
