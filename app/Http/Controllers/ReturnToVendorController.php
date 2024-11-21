<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

use App\Http\Traits\AccountChecker;

use App\Models\ReturnToVendor;
use App\Models\ReturnToVendorProduct;

class ReturnToVendorController extends Controller
{
    use AccountChecker;
    
    public function index() {
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

        $return_to_vendors = ReturnToVendor::where('account_branch_id', $account_branch->id)
            ->paginate(10)
            ->onEachSide(1);

        return view('pages.return-to-vendors.index')->with([
            'account_branch' => $account_branch,
            'account' => $account,
            'return_to_vendors' => $return_to_vendors
        ]);
    }

    public function create() {
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

        Session::forget('rtv_products');

        return view('pages.return-to-vendors.create')->with([
            'account' => $account,
            'account_branch' => $account_branch
        ]);
    }

    public function upload() {
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

        return view('pages.return-to-vendors.upload')->with([
            'account' => $account,
            'account_branch' => $account_branch,
        ]);
    }

    public function store(Request $request) {
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

        $request->validate([
            'rtv_number' => [
                'required'
            ],
            'document_number' => [
                'required'
            ],
            'ship_date' => [
                'required'
            ],
            'reason' => [
                'required'
            ],
            'ship_to_name' => [
                'required'
            ],
            'ship_to_address' => [
                'required'
            ],
        ]);

        $rtv_products = Session::get('rtv_products');

        if(!empty($rtv_products)) {
            $rtv = new ReturnToVendor([
                'account_id' => $account_branch->account_id,
                'account_branch_id' => $account_branch->id,
                'rtv_number' => $request->rtv_number,
                'document_number' => $request->document_number,
                'ship_date' => $request->ship_date,
                'entry_date' => date('Y-m-d'),
                'reason' => $request->reason,
                'remarks' => NULL,
                'ship_to_name' => $request->ship_to_name,
                'ship_to_address' => $request->ship_to_address,
            ]);
            $rtv->save();

            foreach($rtv_products as $product) {
                if(!empty($product['sku_code']) && !empty($product['quantity'])) {
                    $rtv_product = new ReturnToVendorProduct([
                        'return_to_vendor_id' => $rtv->id,
                        'sku_code' => $product['sku_code'],
                        'other_sku_code' => $product['other_sku_code'],
                        'description' => $product['description'],
                        'uom' => $product['uom'],
                        'quantity' => $product['quantity'],
                        'cost' => $product['cost'],
                    ]);
                    $rtv_product->save();
                }
            }

            return redirect()->route('rtv.index')->with([
                'message_success' => 'Return to vendor '.$rtv->rtv_number.' has been created.'
            ]);
        } else {
            return back()->with([
                'message_error' => 'Return to vendor products is required please add product/s before submitting.'
            ]);
        }
    }
}
