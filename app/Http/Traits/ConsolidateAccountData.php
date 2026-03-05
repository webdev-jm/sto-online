<?php

namespace App\Http\Traits;

use App\Models\Account;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

trait ConsolidateAccountData
{

    public function setConsolidatedAccountData()
    {
        $accounts = Account::where('id', '>=', '10')->get();
        foreach($accounts as $account) {
            $years = [2025, 2026];
            foreach($years as $y) {
                foreach(range(1, 12) as $m) {
                    $allConsolidatedData = $this->consolidateAccountData($account, $y, $m);

                    Storage::disk('local')
                        ->put(
                            'reports/consolidated_account_data-'.$account->account_code.'-'.$y.'-'.$m.'.json',
                            json_encode($allConsolidatedData, JSON_PRETTY_PRINT)
                        );
                }
            }
        }
    }

    public function consolidateAccountData($account, $year = null, $month = null) {
        $account_db = $account->db_data;
        $sales_data = [];
        $inventory_data = [];

        DB::setDefaultConnection($account_db->connection_name);

        $sales_data = DB::table('sales_report as sr')
            ->select(
                DB::raw('"'.$account->account_code.'" as account_code'),
                DB::raw('"'.$account->account_name.'" as account_name'),
                DB::raw('"'.$account->account_code.' '.$account->account_name.'" as account_description'),
                'c.code as customer_code',
                'c.name as customer_name',
                'c.province',
                'c.city',
                'c.brgy',
                'year',
                'month',
                'stock_code',
                'description',
                'size',
                'brand_classification',
                'brand',
                'category',
                'uom',
                'quantity',
                'sales',
                'fg_quantity',
                'fg_sales',
                'promo_quantity',
                'promo_sales',
                'credit_memo',
                'parked_quantity',
                'parked_amount',
            )
            ->leftJoin('customers as c', 'c.id', '=', 'sr.customer_id')
            ->leftJoin(DB::connection('mysql')->getDatabaseName().'.channels as ch', 'ch.id', '=', 'c.channel_id')
            ->leftJoin('salesmen as s', 's.id', '=', 'c.salesman_id')
            ->when(!empty($year), function($query) use($year) {
                $query->where('year', $year);
            })
            ->when(!empty($month), function($query) use($month) {
                $query->where('month', $month);
            })
            ->get();

        $inventory_data = DB::table('monthly_inventories as mi')
            ->select(
                DB::raw('"'.$account->account_code.' '.$account->account_name.'" as account'),
                'l.code as location_code',
                'l.name as location_name',
                'p.stock_code',
                'p.description',
                'p.size',
                'year',
                'month',
                'mi.type',
                'uom',
                'total'
            )
            ->leftJoin(DB::connection('sms_db')->getDatabaseName().'.products as p', 'p.id', '=', 'mi.product_id')
            ->leftJoin('locations as l', 'l.id', '=', 'mi.location_id')
            ->when(!empty($year), function($query) use($year) {
                $query->where('year', $year);
            })
            ->when(!empty($month), function($query) use($month) {
                $query->where('month', $month);
            })
            ->get();

        $inventory_uploads = DB::table('inventory_uploads as iu')
            ->when(!empty($year), function($query) use($year) {
                $query->where(DB::raw('YEAR(date)'), $year);
            })
            ->when(!empty($month), function($query) use($month) {
                $query->where(DB::raw('MONTH(date)'), $month);
            })
            ->orderBy('date', 'ASC')
            ->get();

        $inventory_aging = [];
        foreach($inventory_uploads as $upload) {
            $inventories = DB::table('inventories as i')
                ->select(
                    'l.code as location_code',
                    'l.name as location_name',
                    'p.stock_code',
                    'p.description',
                    'p.size',
                    'i.uom',
                    'i.inventory',
                    'i.expiry_date',
                )
                ->leftJoin('locations as l', 'l.id', '=', 'i.location_id')
                ->leftJoin(DB::connection('sms_db')->getDatabaseName().'.products as p', 'p.id', '=', 'i.product_id')
                ->whereNotNull('expiry_date')
                ->where('i.inventory_upload_id', '=', $upload->id)
                ->get();

            foreach($inventories as $inventory) {
                $inventory_aging[$inventory->stock_code] = $inventory;   
            }
        }

        DB::setDefaultConnection('mysql');

        return [
            'sales_data' => $sales_data,
            'inventory_data' => $inventory_data,
            'inventory_aging' => $inventory_aging
        ];
    }
}
