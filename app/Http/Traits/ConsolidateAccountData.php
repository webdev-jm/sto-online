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
            $allConsolidatedData = $this->consolidateAccountData($account);

            Storage::disk('local')
                ->put(
                    'reports/consolidated_account_data-'.$account->account_code.'.json',
                    json_encode($allConsolidatedData, JSON_PRETTY_PRINT)
                );
        }
    }

    private function consolidateAccountData($account)
    {
        $account_db = $account->db_data;
        $sales_data = [];
        $inventory_data = [];

        DB::setDefaultConnection($account_db->connection_name);

        $sales_data = DB::table('sales_report as sr')
            ->select(
                DB::raw('"'.$account->account_code.' '.$account->account_name.'" as account'),
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
                'promo_quantity',
                'promo_sales',
                'credit_memo',
                'parked_quantity',
                'parked_amount',
            )
            ->leftJoin('customers as c', 'c.id', '=', 'sr.customer_id')
            ->leftJoin(DB::connection('mysql')->getDatabaseName().'.channels as ch', 'ch.id', '=', 'c.channel_id')
            ->leftJoin('salesmen as s', 's.id', '=', 'c.salesman_id')
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
            ->get();


        DB::setDefaultConnection('mysql');

        return [
            'sales_data' => $sales_data,
            'inventory_data' => $inventory_data,
        ];
    }
}
