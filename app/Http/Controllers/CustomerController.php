<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Salesman;
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

        $customers = Customer::orderBy('created_at', 'DESC')
            ->with('salesman')
            ->where('account_id', $account->id)
            ->when(!empty($search), function($query) use($search) {
                $query->where('code', 'like', '%'.$search.'%')
                    ->orWhere('name', 'like', '%'.$search.'%')
                    ->orWhere('address', 'like', '%'.$search.'%')
                    ->orWhereHas('salesman', function($qry) use($search) {
                        $qry->where('code', 'like', '%'.$search.'%');
                    });
            })
            ->paginate(10)->onEachSide(1)
            ->appends(request()->query());

        return view('pages.customers.index')->with([
            'account' => $account,
            'customers' => $customers,
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

        // SALESMEN OPTIONS
        $salesmen = Salesman::where('account_id', $account->id)->get();
        $salesmen_arr = array();
        foreach($salesmen as $salesman) {
            $salesmen_arr[$salesman->id] = '['.$salesman->code.'] '.$salesman->name;
        }

        return view('pages.customers.create')->with([
            'account' => $account,
            'salesmen' => $salesmen_arr,
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
            'salesman_id' => $request->salesman_id,
            'code' => $request->code,
            'name' => $request->name,
            'address' => $request->address,
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

       // SALESMEN OPTIONS
       $salesmen = Salesman::where('account_id', $account->id)->get();
       $salesmen_arr = array();
       foreach($salesmen as $salesman) {
           $salesmen_arr[$salesman->id] = '['.$salesman->code.'] '.$salesman->name;
       }

        return view('pages.customers.edit')->with([
            'account' => $account,
            'customer' => $customer,
            'salesmen' => $salesmen_arr
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
