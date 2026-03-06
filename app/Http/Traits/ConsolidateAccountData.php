<?php

namespace App\Http\Traits;

use App\Models\Account;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

trait ConsolidateAccountData
{

    public function setConsolidatedAccountData()
    {

        $years = [2025, 2026];
        $months = range(1, 12);

        Account::where('id', '>=', '10')->chunk(100, function($accounts) use($years, $months) {
            foreach ($accounts as $account) {
                foreach ($years as $y) {
                    foreach ($months as $m) {
                        $allConsolidatedData = $this->consolidateAccountData($account, $y, $m);

                        $filename = sprintf('reports/consolidated_account_data-%s-%s-%s.json',
                            $account->account_code, $y, $m);

                        Storage::disk('local')->put(
                            $filename,
                            json_encode($allConsolidatedData, JSON_PRETTY_PRINT)
                        );
                    }
                }
            }
        });

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

        $originalConnection = DB::getDefaultConnection();
        DB::setDefaultConnection($account_db->connection_name);

        $smsDb = DB::connection('sms_db')->getDatabaseName();
        $mysqlDb = DB::connection('mysql')->getDatabaseName();

        $sales_data = DB::table('sales_report as sr')
            ->select([
                DB::raw("'" . $account->account_code . "' as account_code"),
                DB::raw("'" . $account->account_name . "' as account_name"),
                DB::raw("'" . $account->account_code . " " . $account->account_name . "' as account_description"),
                'c.code as customer_code',
                'c.name as customer_name',
                'ch.code as channel_code',
                'ch.name as channel_name',
                'c.status as customer_status',
                'c.province',
                'c.city',
                'c.brgy',
                'sr.year',
                'sr.month',
                'sr.stock_code',
                'sr.description',
                'sr.size',
                'sr.brand_classification',
                'sr.brand',
                'sr.category',
                'sr.uom',
                'sr.quantity',
                'sr.sales',
                'sr.fg_quantity',
                'sr.fg_sales',
                'sr.promo_quantity',
                'sr.promo_sales',
                'sr.credit_memo',
                'sr.parked_quantity',
                'sr.parked_amount',
            ])
            ->leftJoin('customers as c', 'c.id', '=', 'sr.customer_id')
            ->leftJoin($mysqlDb . '.channels as ch', 'ch.id', '=', 'c.channel_id')
            ->leftJoin('salesmen as s', 's.id', '=', 'c.salesman_id')
            ->when(!empty($year), fn($query) => $query->where('sr.year', $year))
            ->when(!empty($month), fn($query) => $query->where('sr.month', $month))
            ->get();

        $inventory_data = DB::table('monthly_inventories as mi')
            ->select([
                DB::raw("'" . $account->account_code . " " . $account->account_name . "' as account"),
                'l.code as location_code',
                'l.name as location_name',
                'p.stock_code',
                'p.description',
                'p.size',
                'mi.year',
                'mi.month',
                'mi.type',
                'mi.uom',
                'mi.total'
            ])
            ->leftJoin($smsDb . '.products as p', 'p.id', '=', 'mi.product_id')
            ->leftJoin('locations as l', 'l.id', '=', 'mi.location_id')
            ->when(!empty($year), fn($query) => $query->where('mi.year', $year))
            ->when(!empty($month), fn($query) => $query->where('mi.month', $month))
            ->get();

        // OPTIMIZATION: Eliminated N+1 Query. Fetches all required inventory aging data in ONE query instead of looping
        $inventories = DB::table('inventories as i')
            ->select([
                'l.code as location_code',
                'l.name as location_name',
                'p.stock_code',
                'p.description',
                'p.size',
                'i.uom',
                'i.inventory',
                'i.expiry_date',
            ])
            ->join('inventory_uploads as iu', 'iu.id', '=', 'i.inventory_upload_id')
            ->leftJoin('locations as l', 'l.id', '=', 'i.location_id')
            ->leftJoin($smsDb . '.products as p', 'p.id', '=', 'i.product_id')
            ->whereNotNull('i.expiry_date')
            // OPTIMIZATION: Used Laravel's native whereYear/whereMonth instead of DB::raw('YEAR()')
            ->when(!empty($year), fn($query) => $query->whereYear('iu.date', $year))
            ->when(!empty($month), fn($query) => $query->whereMonth('iu.date', $month))
            ->orderBy('iu.date', 'ASC')
            ->get();

        $inventory_aging = [];
        // The last upload processed overwrites the previous ones for the same stock_code, maintaining original logic
        foreach($inventories as $inventory) {
            $inventory_aging[$inventory->stock_code] = $inventory;
        }

        DB::setDefaultConnection($originalConnection);

        return [
            'sales_data' => $sales_data,
            'inventory_data' => $inventory_data,
            'inventory_aging' => $inventory_aging
        ];
    }
}
