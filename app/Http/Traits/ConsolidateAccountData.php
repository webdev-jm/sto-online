<?php

namespace App\Http\Traits;

use App\Models\Account;
use App\Models\AccountBranch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Traits\GenerateMonthlyInventory;

trait ConsolidateAccountData
{
    use GenerateMonthlyInventory;

    public function setConsolidatedAccountData($year = NULL)
    {
        $years = empty($year) ? [2025, 2026] : [$year];

        $this->initSqliteSchema();

        Account::where('id', '>=', '10')->chunk(100, function ($accounts) use ($years) {
            foreach ($accounts as $account) {
                $this->consolidateSingleAccount($account, $years);
            }
        });
    }

    public function consolidateSingleAccount(Account $account, array $years = [2025, 2026]): void
    {
        $this->initSqliteSchema();

        foreach ($years as $y) {
            foreach (range(1, 12) as $m) {
                foreach (AccountBranch::where('account_id', $account->id)->get() as $branch) {
                    $this->setMonthlyInventory($account->id, $branch->id, $y, $m);
                }

                $allConsolidatedData = $this->consolidateAccountData($account, $y, $m);

                $filename = sprintf(
                    'reports/consolidated_account_data-%s-%s-%s.json',
                    $account->account_code, $y, $m
                );
                Storage::disk('local')->put(
                    $filename,
                    json_encode($allConsolidatedData, JSON_PRETTY_PRINT)
                );

                $this->importToSqlite($account, $y, $m, $allConsolidatedData);
            }
        }
    }

    // ---------------------------------------------------------------
    // SQLite Helpers
    // ---------------------------------------------------------------

    private function initSqliteSchema(): void
    {
        $sqlite = DB::connection('sqlite_reports');

        $sqlite->statement('CREATE TABLE IF NOT EXISTS sales_data (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            account_code TEXT,
            account_name TEXT,
            area TEXT,
            customer_code TEXT,
            customer_name TEXT,
            city TEXT,
            province TEXT,
            salesman_code TEXT,
            salesman_name TEXT,
            salesman_type TEXT,
            location_code TEXT,
            location_name TEXT,
            channel_code TEXT,
            channel_name TEXT,
            customer_status INTEGER,
            stock_code TEXT,
            description TEXT,
            size TEXT,
            brand TEXT,
            uom TEXT,
            year INTEGER,
            month INTEGER,
            quantity REAL DEFAULT 0,
            sales REAL DEFAULT 0
        )');

        $sqlite->statement('CREATE TABLE IF NOT EXISTS inventory_data (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            account_code TEXT,
            location_code TEXT,
            location_name TEXT,
            stock_code TEXT,
            description TEXT,
            size TEXT,
            uom TEXT,
            year INTEGER,
            month INTEGER,
            type TEXT,
            total REAL DEFAULT 0
        )');

        $sqlite->statement('CREATE TABLE IF NOT EXISTS inventory_aging (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            account_code TEXT,
            location_code TEXT,
            location_name TEXT,
            stock_code TEXT,
            description TEXT,
            size TEXT,
            uom TEXT,
            inventory REAL DEFAULT 0,
            expiry_date TEXT,
            year INTEGER,
            month INTEGER
        )');

        // Indexes for common query patterns
        $sqlite->statement('CREATE INDEX IF NOT EXISTS idx_sales_year_month ON sales_data (year, month)');
        $sqlite->statement('CREATE INDEX IF NOT EXISTS idx_sales_customer ON sales_data (customer_code, customer_status)');
        $sqlite->statement('CREATE INDEX IF NOT EXISTS idx_inventory_year_month ON inventory_data (year, month)');
        $sqlite->statement('CREATE INDEX IF NOT EXISTS idx_aging_stock ON inventory_aging (stock_code, expiry_date)');
    }

    private function importToSqlite(Account $account, int $year, int $month, array $data): void
    {
        $sqlite = DB::connection('sqlite_reports');

        // SQLite max variables = 999, chunk = floor(999 / column_count)
        $salesChunk     = floor(999 / 24); // 41
        $inventoryChunk = floor(999 / 10); // 99
        $agingChunk     = floor(999 / 11); // 90

        $sqlite->table('sales_data')
            ->where('account_code', $account->account_code)
            ->where('year', $year)->where('month', $month)->delete();

        $sqlite->table('inventory_data')
            ->where('account_code', $account->account_code)
            ->where('year', $year)->where('month', $month)->delete();

        $sqlite->table('inventory_aging')
            ->where('account_code', $account->account_code)
            ->where('year', $year)->where('month', $month)->delete();

        // sales_data — 24 columns
        collect($data['sales_data'] ?? [])
            ->chunk($salesChunk)
            ->each(fn($chunk) => $sqlite->table('sales_data')->insert(
                $chunk->map(fn($row) => [
                    'account_code'    => $account->account_code,
                    'account_name'    => $account->account_name,
                    'area'            => $account->area,
                    'customer_code'   => $row->customer_code   ?? null,
                    'customer_name'   => $row->customer_name   ?? null,
                    'city'            => $row->city            ?? null,
                    'province'        => $row->province        ?? null,
                    'salesman_code'   => $row->salesman_code   ?? null,
                    'salesman_name'   => $row->salesman_name   ?? null,
                    'salesman_type'   => $row->salesman_type   ?? null,
                    'location_code'   => $row->location_code   ?? null,
                    'location_name'   => $row->location_name   ?? null,
                    'channel_code'    => $row->channel_code    ?? null,
                    'channel_name'    => $row->channel_name    ?? null,
                    'customer_status' => $row->customer_status ?? 0,
                    'stock_code'      => $row->stock_code      ?? null,
                    'description'     => $row->description     ?? null,
                    'size'            => $row->size            ?? null,
                    'brand'           => $row->brand           ?? null,
                    'uom'             => $row->uom             ?? null,
                    'year'            => $year,
                    'month'           => $month,
                    'quantity'        => (float) ($row->quantity ?? 0),
                    'sales'           => (float) ($row->sales   ?? 0),
                ])->all()
            ));

        // inventory_data — 10 columns
        collect($data['inventory_data'] ?? [])
            ->chunk($inventoryChunk)
            ->each(fn($chunk) => $sqlite->table('inventory_data')->insert(
                $chunk->map(fn($row) => [
                    'account_code'  => $account->account_code,
                    'location_code' => $row->location_code ?? null,
                    'location_name' => $row->location_name ?? null,
                    'stock_code'    => $row->stock_code    ?? null,
                    'description'   => $row->description   ?? null,
                    'size'          => $row->size          ?? null,
                    'uom'           => $row->uom           ?? null,
                    'year'          => $year,
                    'month'         => $month,
                    'total'         => (float) ($row->total ?? 0),
                ])->all()
            ));

        // inventory_aging — 11 columns
        collect($data['inventory_aging'] ?? [])
            ->chunk($agingChunk)
            ->each(fn($chunk) => $sqlite->table('inventory_aging')->insert(
                $chunk->map(fn($row) => [
                    'account_code'  => $account->account_code,
                    'location_code' => $row->location_code ?? null,
                    'location_name' => $row->location_name ?? null,
                    'stock_code'    => $row->stock_code    ?? null,
                    'description'   => $row->description   ?? null,
                    'size'          => $row->size          ?? null,
                    'uom'           => $row->uom           ?? null,
                    'inventory'     => (float) ($row->inventory ?? 0),
                    'expiry_date'   => $row->expiry_date   ?? null,
                    'year'          => $year,
                    'month'         => $month,
                ])->all()
            ));
    }

    // ---------------------------------------------------------------
    // Existing method unchanged
    // ---------------------------------------------------------------

    public function consolidateAccountData($account, $year = null, $month = null) {
        $account_db = $account->db_data;

        $originalConnection = DB::getDefaultConnection();
        DB::setDefaultConnection($account_db->connection_name);

        $smsDb   = DB::connection('sms_db')->getDatabaseName();
        $mysqlDb = DB::connection('mysql')->getDatabaseName();

        $sales_data = DB::table('sales_report as sr')
            ->select([
                DB::raw("'" . $account->account_code . "' as account_code"),
                DB::raw("'" . $account->account_name . "' as account_name"),
                DB::raw("'" . $account->area . "' as area"),
                'c.code as customer_code',
                'c.name as customer_name',
                'c.city as city',
                'c.province as province',
                DB::raw("COALESCE(s.code, cs.code) as salesman_code"),
                DB::raw("COALESCE(s.name, cs.name) as salesman_name"),
                DB::raw("COALESCE(s.type, cs.type) as salesman_type"),
                'l.code as location_code',
                'l.name as location_name',
                'ch.code as channel_code',
                'ch.name as channel_name',
                'c.status as customer_status',
                'sr.year', 'sr.month',
                'sr.stock_code', 'sr.description', 'sr.size',
                'sr.brand', 'sr.uom', 'sr.quantity', 'sr.sales',
            ])
            ->leftJoin('customers as c', 'c.id', '=', 'sr.customer_id')
            ->leftJoin($mysqlDb . '.channels as ch', 'ch.id', '=', 'c.channel_id')
            ->leftJoin('salesmen as s', 's.id', '=', 'sr.salesman_id')               // sales_report's salesman
            ->leftJoin('salesmen as cs', 'cs.id', '=', 'c.salesman_id')              // customer's salesman (fallback)
            ->leftJoin('locations as l', 'l.id', '=', 'sr.location_id')
            ->when(!empty($year), fn($q) => $q->where('sr.year', $year))
            ->when(!empty($month), fn($q) => $q->where('sr.month', $month))
            ->get();

        $inventory_data = DB::table('monthly_inventories as mi')
            ->select([
                'l.code as location_code',
                'l.name as location_name',
                'p.stock_code', 'p.description', 'p.size',
                'mi.year', 'mi.month', 'mi.type', 'mi.uom', 'mi.total',
            ])
            ->leftJoin($smsDb . '.products as p', 'p.id', '=', 'mi.product_id')
            ->leftJoin('locations as l', 'l.id', '=', 'mi.location_id')
            ->when(!empty($year), fn($q) => $q->where('mi.year', $year))
            ->when(!empty($month), fn($q) => $q->where('mi.month', $month))
            ->get();

        $inventories = DB::table('inventories as i')
            ->select([
                'l.code as location_code',
                'l.name as location_name',
                'p.stock_code', 'p.description', 'p.size',
                'i.uom', 'i.inventory', 'i.expiry_date',
            ])
            ->join('inventory_uploads as iu', 'iu.id', '=', 'i.inventory_upload_id')
            ->leftJoin('locations as l', 'l.id', '=', 'i.location_id')
            ->leftJoin($smsDb . '.products as p', 'p.id', '=', 'i.product_id')
            ->whereNotNull('i.expiry_date')
            ->when(!empty($year), fn($q) => $q->whereYear('iu.date', $year))
            ->when(!empty($month), fn($q) => $q->whereMonth('iu.date', $month))
            ->orderBy('iu.date', 'ASC')
            ->get();

        $inventory_aging = [];
        foreach ($inventories as $inventory) {
            $inventory_aging[$inventory->stock_code] = $inventory;
        }

        DB::setDefaultConnection($originalConnection);

        return [
            'sales_data'      => $sales_data,
            'inventory_data'  => $inventory_data,
            'inventory_aging' => $inventory_aging,
        ];
    }
}
