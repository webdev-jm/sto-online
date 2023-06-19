<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SalesUpload;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Session;

class SaleController extends Controller
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

        $sales_uploads = SalesUpload::orderBy('created_at', 'DESC')
            ->where('account_id', $account->id)
            ->paginate(10)->onEachSide(1);

        return view('pages.sales.index')->with([
            'account' => $account,
            'sales_uploads' => $sales_uploads
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

        return view('pages.sales.create')->with([
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
        // check account
        $account = $this->checkAccount();
        if ($account instanceof \Illuminate\Http\RedirectResponse) {
            return $account->with([
                'message_error' => 'Please select an account.'
            ]);
        }

        $sales_upload = SalesUpload::findOrFail(decrypt($id));

        return view('pages.sales.show')->with([
            'account' => $account,
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Sale  $sale
     * @return \Illuminate\Http\Response
     */
    public function destroy(Sale $sale)
    {
        //
    }
}
