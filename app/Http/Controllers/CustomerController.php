<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Salesman;
use App\Models\SalesmanCustomer;
use App\Models\Channel;
use App\Models\CustomerUbo;
use App\Models\CustomerUboDetail;

use App\Models\SMSAccount;
use App\Models\AccountBranch;

use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;

use App\Http\Requests\CustomerAddRequest;
use App\Http\Requests\CustomerUpdateRequest;

use Illuminate\Support\Facades\Session;

use App\Http\Traits\AccountChecker;

set_time_limit(3600); // one hour

class CustomerController extends Controller
{
    use AccountChecker;

    public function parked() {
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

        $customers = Customer::orderBy('created_at', 'DESC')
            ->where('account_id', $account->id)
            ->where('account_branch_id', $account_branch->id)
            ->where('status', 1)
            ->with('salesman')
            ->paginate(10);

        return view('pages.customers.parked')->with([
            'account' => $account,
            'account_branch' => $account_branch,
            'customers' => $customers
        ]);
    }

    public function validate_customer($id) {
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

        $customer = Customer::findOrFail(decrypt($id));
        $customer_ubo = $customer->ubo->first();
        if(empty($customer_ubo)) {
            $customer_ubo_details = $customer->ubo_detail;
            $ubo = $customer_ubo_details->first();
            $customer_ubo = $ubo->customer_ubo ?? [];
        }

        return view('pages.customers.validate')->with([
            'account' => $account,
            'account_branch' => $account_branch,
            'customer' => $customer,
            'customer_ubo' => $customer_ubo,
            'customer_ubo_details' => $customer_ubo_details ?? []
        ]);
    }

    public function same_customer($customer_id, $ubo_id) {
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

        $customer_id = decrypt($customer_id);
        $customer = Customer::findOrFail($customer_id);
        $ubo = CustomerUbo::where('ubo_id', $ubo_id)->first();
        // check if already exists as UBO child
        $ubo_detail = CustomerUboDetail::where('customer_id', $customer_id)
            ->first();
        if(empty($ubo_detail)) { // add
            $similarity = $this->checkSimilarity($ubo->name, $customer->name);
            $address_similarity = $this->checkSimilarity($ubo->address, $customer->address);

            $ubo_detail = new CustomerUboDetail([
                'account_id' => $account->id,
                'account_branch_id' => $account_branch->id,
                'customer_ubo_id' => $ubo->id,
                'customer_id' => $customer->id,
                'ubo_id' => $ubo->ubo_id,
                'name' => $customer->name,
                'address' => $customer->address,
                'similarity' => $similarity,
                'address_similarity' => $address_similarity
            ]);
            $ubo_detail->save();
        }

        // update customer status
        $customer->update([
            'status' => 0
        ]);

        // update sales status
        $customer->sales()->update([
            'status' => 0
        ]);

        return redirect()->route('customer.parked')->with([
            'message_success' => 'Customer UBO has been updated.'
        ]);
    }

    public function different_customer($customer_id) {
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

        $customer_id = decrypt($customer_id);
        $customer = Customer::findOrFail($customer_id);
        // remove from ubo details
        $ubo_detail = CustomerUboDetail::where('customer_id', $customer->id)
            ->delete();

        // get last UBO ID and increment by 1
        $last_ubo_id = CustomerUbo::max('ubo_id');
        $ubo_id = $last_ubo_id ? $last_ubo_id + 1 : 1;
        
        // add or update customer ubo
        $ubo = CustomerUbo::updateOrInsert(
            [
                'account_id' => $account->id,
                'account_branch_id' => $account_branch->id,
                'customer_id' => $customer->id,
                'ubo_id' => $ubo_id
            ],
            [
                'name' => $customer->name,
                'address' => $customer->address
            ]
        );

        $customer->update([
            'status' => 0
        ]);

        $customer->sales()->update([
            'status' => 0
        ]);

        return redirect()->route('customer.parked')->with([
            'message_success' => 'Customer UBO has been updated.'
        ]);

    }

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
            ->with('salesman', 'channel')
            ->where('account_id', $account->id)
            ->where('account_branch_id', $account_branch->id)
            ->where('status', 0)
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
        $salesmen = Salesman::where('account_id', $account->id)
            ->where('account_branch_id', $account_branch->id)
            ->get();
        $salesmen_arr = array();
        foreach($salesmen as $salesman) {
            $salesmen_arr[$salesman->id] = '['.$salesman->code.'] '.$salesman->name;
        }

        // CHANNEL OPTIONS
        $channels = Channel::where('account_id', $account->id)
            ->where('account_branch_id', $account_branch->id)
            ->get();
        $channel_arr = array();
        foreach($channels as $channel) {
            $channel_arr[$channel->id] = '['.$channel->code.'] '.$channel->name;
        }

        return view('pages.customers.create')->with([
            'account' => $account,
            'account_branch' => $account_branch,
            'salesmen' => $salesmen_arr,
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
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

        $customer = new Customer([
            'account_id' => $account->id,
            'account_branch_id' => $account_branch->id,
            'salesman_id' => $request->salesman_id,
            'channel_id' => $request->channel_id,
            'code' => $request->code,
            'name' => $request->name,
            'address' => $request->address,
            'street' => $request->street,
            'brgy' => $request->barangay,
            'city' => $request->city,
            'province' => $request->province
        ]);
        $customer->save();

        // add new salesman history
        $new_history = new SalesmanCustomer([
            'salesman_id' => $request->salesman_id,
            'customer_id' => $customer->id,
            'start_date' => date('Y-m-d'),
            'end_date' => NULL
        ]);
        $new_history->save();
        
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

        // CHANNEL OPTIONS
        $channels = Channel::where('account_id', $account->id)
            ->where('account_branch_id', $account_branch->id)
            ->get();
        $channel_arr = array();
        foreach($channels as $channel) {
            $channel_arr[$channel->id] = '['.$channel->code.'] '.$channel->name;
        }

        return view('pages.customers.edit')->with([
            'account' => $account,
            'account_branch' => $account_branch,
            'customer' => $customer,
            'salesmen' => $salesmen_arr,
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
            'channel_id' => $request->channel_id,
            'code' => $request->code,
            'name' => $request->name,
            'address' => $request->address,
            'street' => $request->street,
            'brgy' => $request->barangay,
            'city' => $request->city,
            'province' => $request->province,
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

    public function generateUBO($account_id, $branch_id) {
        // Get customers with eager-loaded relationships
        $customers = Customer::where('account_id', $account_id)
            ->where('account_branch_id', $branch_id)
            ->doesntHave('ubo')
            ->doesntHave('ubo_detail')
            ->get();

        foreach ($customers as $customer) {
            if(empty($customer->ubo->count())) { // Check if UBO does not exist
                // Find potential duplicates with high similarity
                $potential_duplicate = CustomerUbo::whereRaw('CalculateLevenshteinSimilarity(name, ?) >= 90', [$customer->name])
                    ->whereRaw('CalculateLevenshteinSimilarity(address, ?) >= 90', [$customer->address])
                    ->where('customer_id', '<>', $customer->id)
                    ->where('account_id', $customer->account_id)
                    ->where('account_branch_id', $customer->account_branch_id)
                    ->first();

                if(!empty($potential_duplicate)) {
                    $ubo_id = $potential_duplicate->ubo_id;
                    
                    $percent = $this->checkSimilarity($potential_duplicate->name, $customer->name);
                    $address_pc = $this->checkSimilarity($potential_duplicate->address, $customer->address);

                    CustomerUboDetail::updateOrInsert(
                        [
                            'account_id' => $customer->account_id,
                            'account_branch_id' => $customer->account_branch_id,
                            'customer_ubo_id' => $potential_duplicate->id,
                            'customer_id' => $customer->id,
                            'ubo_id' => $ubo_id,
                        ],
                        [
                            'name' => $customer->name,
                            'address' => $customer->address,
                            'similarity' => $percent,
                            'address_similarity' => $address_pc,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]
                    );

                    $customer->update([
                        'status' => 1
                    ]);

                    $customer->sales()->update([
                        'status' => 1
                    ]);
                } else {
                    // Insert and assign UBO ID for similar customers
                    $last_ubo_id = CustomerUbo::max('ubo_id');
                    $ubo_id = $last_ubo_id ? $last_ubo_id + 1 : 1;

                    // Create a new UBO
                    CustomerUbo::updateOrInsert([
                            'account_id' => $customer->account_id,
                            'account_branch_id' => $customer->account_branch_id,
                            'customer_id' => $customer->id,
                        ],
                        [
                            'ubo_id' => $ubo_id ?? 1,
                            'name' => $customer->name,
                            'address' => $customer->address,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]
                    );

                    $customer->update([
                        'status' => 0
                    ]);

                    $customer->sales()->update([
                        'status' => 0
                    ]);
                }
            }
        }

        return 'UBO has been generated.';
    }

    private function checkSimilarity($str1, $str2) {
        // remove spaces before comparing
        $str1 = str_replace(' ', '', $str1);
        $str2 = str_replace(' ', '', $str2);

        // Calculate Levenshtein distance
        $distance = levenshtein(strtoupper($str1), strtoupper($str2));

        // Calculate maximum length
        $max_length = max(strlen($str1), strlen($str2));

        // Check if the maximum length is zero to avoid division by zero
        if ($max_length == 0) {
            return 0; // or any other appropriate value
        }

        // Calculate similarity percentage
        $similarity = 1 - ($distance / $max_length);
        $similarity = $similarity * 100;

        return $similarity;
    }

    public function uboJob() {
        return view('pages.ubo-jobs.index');
    }

    public function uboJobRun() {

    }
}
