<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SalesUpload;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

use App\Http\Traits\AccountChecker;

use App\Exports\SalesLineExport;
use Maatwebsite\Excel\Facades\Excel;

class SaleController extends Controller
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

        $sales_uploads = SalesUpload::orderBy('created_at', 'DESC')
            ->with('user')
            ->when(auth()->user()->can('sales restore'), function($query) {
                $query->withTrashed();
            })
            ->where('account_id', $account->id)
            ->where('account_branch_id', $account_branch->id)
            ->when(!empty($search), function($query) use($search) {
                $query->where(function($qry) use($search) {
                    $qry->whereHas('user', function($qry1) use($search) {
                        $qry1->where('name', 'like', '%'.$search.'%');
                    })
                    ->orWhere('sku_count', 'like', '%'.$search.'%');
                });
            })
            ->paginate(10)->onEachSide(1)
            ->appends(request()->query());

        return view('pages.sales.index')->with([
            'account' => $account,
            'account_branch' => $account_branch,
            'sales_uploads' => $sales_uploads,
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

        return view('pages.sales.create')->with([
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
        // Validate the uploaded file
        $validatedData = $request->validate([
            'file' => 'required|file|max:10240', // Adjust the max file size if needed
        ]);

        // Store the uploaded file
        $path = $request->file('file')->store('uploads');

        // You can perform additional logic here, such as saving the file path to the database
        // or performing further processing on the file

        return response()->json(['message' => 'File uploaded successfully']);
    }

    public function uploads(Request $request) {
        // Get the uploaded file from the request
        $file = $request->file('file');

        // Process the uploaded file here
        // You can save it to the database, perform additional validation, etc.

        return response()->json(['message' => 'File processed successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Sale  $sale
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

        $sales_upload = SalesUpload::findOrFail(decrypt($id));

        return view('pages.sales.show')->with([
            'account' => $account,
            'account_branch' => $account_branch,
            'sales_upload' => $sales_upload
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Sale  $sale
     * @return \Illuminate\Http\Response
     */
    public function edit(Sale $sale)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Sale  $sale
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Sale $sale)
    {
        //
    }

    public function restore($id) {
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

        $sales_upload = SalesUpload::withTrashed()->findOrFail(decrypt($id));

        DB::statement("SET SQL_SAFE_UPDATES = 0;");
        $sales_upload->restore();
        $sales_upload->sales()->restore();
        DB::statement("SET SQL_SAFE_UPDATES = 1;");

        $dates = $sales_upload->sales()->select('date')->distinct()->pluck('date')->toArray();

        $dates_arr = array();
        foreach($dates as $date) {
            $year = date('Y', strtotime($date));
            $month = date('n', strtotime($date));

            $dates_arr[$year][$month] = $date;
        }

        foreach($dates_arr as $year => $months) {
            foreach($months as $month => $date) {
                DB::statement('CALL generate_sales_report(?, ?, ?, ?)', [$account->id, $account_branch->id, $year, $month]);
            }
        }

        activity('restored')
            ->performedOn($sales_upload)
            ->log(':causer.name has restored sales upload by '.$sales_upload->user->name);

        return back()->with([
            'message_success' => 'Sales upload by '.$sales_upload->user->name.' has been restored.'
        ]);
    }

    public function export($id) {
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

        $sales_upload = SalesUpload::findOrFail(decrypt($id));

        return Excel::download(new SalesLineExport($sales_upload), 'STO Sales-'.time().'.xlsx');
    }
}
