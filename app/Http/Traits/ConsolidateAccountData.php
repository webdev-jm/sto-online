<?php

namespace App\Http\Traits;

use App\Models\Account;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

trait ConsolidateAccountData
{

    public function setConsolidatedAccountData()
    {
        $accounts = Account::all();
        $allConsolidatedData = [];

        foreach($accounts as $account) {
            $allConsolidatedData[$account->id] = $this->consolidateAccountData($account);
        }

        Storage::disk('local')
        ->put(
            'reports/consolidated_account_data.json',
            json_encode($allConsolidatedData, JSON_PRETTY_PRINT)
        );

        return $allConsolidatedData;
    }

    private function consolidateAccountData($account)
    {
        $account_db = $account->db_data;
        $sales_data = [];
        $inventory_data = [];

        DB::setDefaultConnection($account_db->connection_name);

        $sales_data = DB::table('sales')
            ->select(
                DB::raw('"'.$account->account_code.' '.$account->account_name.'" as account'),
                'c.code as customer_code',
                'c.name as customer_name',
                'p.stock_code',
                'p.description',
                'p.size',
                'ch.code as channel_code',
                'ch.name as channel_name',
                's.code as salesman_code',
                's.name as salesman_name',
                'l.code as location_code',
                'l.name as location_name',
                'u.name as user_name',
                'sales.type',
                'date',
                'document_number',
                'sales.category',
                'uom',
                'quantity',
                'price_inc_vat',
                'amount',
                'amount_inc_vat',
                'sales.status',
            )
            ->leftJoin('customers as c', 'c.id', '=', 'sales.customer_id')
            ->leftJoin(DB::connection('sms_db')->getDatabaseName().'.products as p', 'p.id', '=', 'sales.product_id')
            ->leftJoin(DB::connection('mysql')->getDatabaseName().'.channels as ch', 'ch.id', '=', 'sales.channel_id')
            ->leftJoin('salesmen as s', 's.id', '=', 'sales.salesman_id')
            ->leftJoin('locations as l', 'l.id', '=', 'sales.location_id')
            ->leftJoin(DB::connection('mysql')->getDatabaseName().'.users as u', 'u.id', '=', 'sales.user_id')
            ->get();

        $inventory_data = DB::table('inventories as i')
                ->select(
                    'l.code as location_code',
                    'l.name as location_name',
                    'p.stock_code',
                    'p.description',
                    'p.size',
                    'type',
                    'uom',
                    'inventory',
                    'expiry_date'
                )
                ->leftJoin(DB::connection('sms_db')->getDatabaseName().'.products as p', 'p.id', '=', 'i.product_id')
                ->leftJoin('locations as l', 'l.id', '=', 'i.location_id')
                ->get();

        DB::setDefaultConnection('mysql');

        return [
            'sales_data' => $sales_data,
            'inventory_data' => $inventory_data,
        ];
    }
}
