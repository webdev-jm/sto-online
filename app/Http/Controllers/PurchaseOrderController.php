<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\PurchaseOrder;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use App\Http\Traits\AccountChecker;

class PurchaseOrderController extends Controller
{
    use AccountChecker;

    public function index(Request $request) {
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

        Session::forget('po_upload_data');

        $filters = Session::get('po_filters');

        $purchase_orders = PurchaseOrder::where('account_branch_id', $account_branch->id)
            ->when(!empty($filters['date_type']) && !empty($filters['from']) && !empty($filters['to']), function($query) use ($filters) {
                if ($filters['date_type'] == 'order_date') {
                    $query->whereBetween('order_date', [$filters['from'], $filters['to']]);
                } else if ($filters['date_type'] == 'ship_date') {
                    $query->whereBetween('ship_date', [$filters['from'], $filters['to']]);
                } else if($filters['date_type'] == 'created_at') {
                    $query->whereBetween(DB::raw('DATE(created_at)'), [$filters['from'], $filters['to']]);
                }
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20)->onEachSide(1);

        $total_data = PurchaseOrder::select(
                DB::raw('SUM(total_quantity) as quantity'),
                DB::raw('SUM(total_sales) as sales'),
                DB::raw('SUM(grand_total) as total')
            )
            ->when(!empty($filters['date_type']) && !empty($filters['from']) && !empty($filters['to']), function($query) use ($filters) {
                if ($filters['date_type'] == 'order_date') {
                    $query->whereBetween('order_date', [$filters['from'], $filters['to']]);
                } else if ($filters['date_type'] == 'ship_date') {
                    $query->whereBetween('ship_date', [$filters['from'], $filters['to']]);
                } else if($filters['date_type'] == 'created_at') {
                    $query->whereBetween(DB::raw('DATE(created_at)'), [$filters['from'], $filters['to']]);
                }
            })
            ->where('account_branch_id', $account_branch->id)
            ->get();

        return view('pages.purchase-orders.index')->with([
            'account' => $account,
            'account_branch' => $account_branch,
            'purchase_orders' => $purchase_orders,
            'total_data' => $total_data[0]
        ]);
    }

    public function upload() {
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

        return view('pages.purchase-orders.upload')->with([
            'account' => $account,
            'account_branch' => $account_branch,
        ]);
    }

    public function create() {
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

        return view('pages.purchase-orders.create')->with([
            'account_branch' => $account_branch,
            'account' => $account
        ]);
    }

    public function show($id) {
        $account_branch = $this->checkBranch();
        if ($account_branch instanceof \Illuminate\Http\RedirectResponse) {
            return $account_branch;
        }
        $account = Session::get('account');

        $id = decrypt($id);
        $purchase_order = PurchaseOrder::findOrFail($id);

        return view('pages.purchase-orders.show')->with([
            'account' => $account,
            'account_branch' => $account_branch,
            'purchase_order' => $purchase_order
        ]);
    }
}
