<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Salesman;
use App\Models\SalesmanCustomer;
use Illuminate\Http\Request;

use App\Http\Requests\CustomerAddRequest;
use App\Http\Requests\CustomerUpdateRequest;

use Illuminate\Support\Facades\Session;

use App\Http\Traits\AccountChecker;

class CustomerController extends Controller
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

        $customers = Customer::orderBy('created_at', 'DESC')
            ->when(auth()->user()->can('customer restore'), function($query) {
                $query->withTrashed();
            })
            ->with('salesman')
            ->where('account_id', $account->id)
            ->where('account_branch_id', $account_branch->id)
            ->when(!empty($search), function($query) use($search) {
                $query->where(function($qry) use($search) {
                    $qry->where('code', 'like', '%'.$search.'%')
                        ->orWhere('name', 'like', '%'.$search.'%')
                        ->orWhere('address', 'like', '%'.$search.'%')
                        ->orWhereHas('salesman', function($qry1) use($search) {
                            $qry1->where('code', 'like', '%'.$search.'%');
                        });
                });
            })
            ->paginate(10)->onEachSide(1)
            ->appends(request()->query());

        return view('pages.customers.index')->with([
            'account' => $account,
            'account_branch' => $account_branch,
            'customers' => $customers,
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
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

        // SALESMEN OPTIONS
        $salesmen = Salesman::where('account_id', $account->id)->get();
        $salesmen_arr = array();
        foreach($salesmen as $salesman) {
            $salesmen_arr[$salesman->id] = '['.$salesman->code.'] '.$salesman->name;
        }

        return view('pages.customers.create')->with([
            'account' => $account,
            'account_branch' => $account_branch,
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
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

        $customer = new Customer([
            'account_id' => $account->id,
            'account_branch_id' => $account_branch->id,
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
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

        $customer = Customer::with('salesman')->findOrFail(decrypt($id));
        
        return view('pages.customers.show')->with([
            'account' => $account,
            'account_branch' => $account_branch,
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
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

        $customer = Customer::findOrFail(decrypt($id));

       // SALESMEN OPTIONS
       $salesmen = Salesman::where('account_id', $account->id)->get();
       $salesmen_arr = array();
       foreach($salesmen as $salesman) {
           $salesmen_arr[$salesman->id] = '['.$salesman->code.'] '.$salesman->name;
       }

        return view('pages.customers.edit')->with([
            'account' => $account,
            'account_branch' => $account_branch,
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
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

        $customer = Customer::findOrFail(decrypt($id));
        $changes_arr['old'] = $customer->getOriginal();

        // record customer salesman history
        if(!empty($request->salesman_id) && $customer->salesman_id != $request->salesman_id) {
            // update end date of previous salesman
            $prev_history = SalesmanCustomer::where('customer_id', $customer->id)
                ->where('salesman_id', $customer->salesman_id)
                ->whereNull('end_date')
                ->first();

            $prev_history->update([
                'end_date' => date('Y-m-d')
            ]);

            // add new salesman history
            $new_history = new SalesmanCustomer([
                'salesman_id' => $request->salesman_id,
                'customer_id' => $customer->id,
                'start_date' => date('Y-m-d'),
                'end_date' => NULL
            ]);
            $new_history->save();
        }

        $customer->update([
            'area_id' => $request->area_id,
            'channel_id' => $request->channel_id,
            'salesman_id' => $request->salesman_id,
            'code' => $request->code,
            'name' => $request->name,
            'address' => $request->address,
        ]);

        $changes_arr['changes'] = $customer->getChanges();

        // logs
        activity('update')
        ->performedOn($customer)
        ->withProperties($changes_arr)
        ->log(':causer.name has updated customer ['.$account->short_name.'] :subject.code :subject.name');

        return redirect()->route('customer.show', encrypt($customer->id))->with([
            'message_success' => 'Customer '.$customer->name.' was updated.'
        ]);
    }

    public function restore($id) {
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

        $customer = Customer::withTrashed()->findOrFail(decrypt($id));

        $customer->restore();

        activity('restore')
            ->performedOn($customer)
            ->log(':causer.name has restored customer '.$customer->name);

        return back()->with([
            'message_success' => 'Customer '.$customer->name.' has been restored.'
        ]);
    }
}
