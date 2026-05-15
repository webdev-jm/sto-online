<?php

namespace Tests\Feature\Reports;

use App\Http\Traits\ConsolidateAccountData;
use App\Models\Account;
use App\Models\AccountDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ConsolidateAccountDataTest extends TestCase
{
    private ?Account $testAccount = null;

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('consolidated_sales_reports')
            ->where('account_code', 'like', 'CSR-TEST-%')
            ->delete();
    }

    protected function tearDown(): void
    {
        if ($this->testAccount) {
            DB::table('consolidated_sales_reports')
                ->where('account_code', $this->testAccount->account_code)
                ->delete();

            AccountDatabase::where('account_id', $this->testAccount->id)->forceDelete();
            $this->testAccount->forceDelete();
        }

        parent::tearDown();
    }

    private function makeTestAccount(): Account
    {
        $uniq = uniqid();

        $account = Account::create([
            'sms_account_id'   => null,
            'account_code'     => 'CSR-TEST-' . $uniq,
            'account_name'     => 'CSR Test Account ' . $uniq,
            'short_name'       => 'CSR',
            'account_password' => bcrypt('password'),
        ]);

        $this->testAccount = $account;

        return $account;
    }

    private function makePipeline(array $salesData = [], array $inventoryData = [], array $inventoryAging = []): object
    {
        return new class($salesData, $inventoryData, $inventoryAging) {
            use ConsolidateAccountData;

            public function __construct(
                private array $fakeSales,
                private array $fakeInventory,
                private array $fakeAging,
            ) {}

            protected function setMonthlyInventory(): void {}

            protected function ensureTenantConnection($db): void {}

            public function consolidateAccountData($account, $year = null, $month = null): array
            {
                return [
                    'sales_data'      => collect($this->fakeSales),
                    'inventory_data'  => collect($this->fakeInventory),
                    'inventory_aging' => $this->fakeAging,
                ];
            }
        };
    }

    public function test_sales_data_is_written_to_mysql_consolidated_sales_reports(): void
    {
        $account = $this->makeTestAccount();

        $salesRow = (object) [
            'customer_code'   => 'C001',
            'customer_name'   => 'Test Customer',
            'city'            => 'Cebu City',
            'province'        => 'Cebu',
            'salesman_code'   => 'S001',
            'salesman_name'   => 'Test Salesman',
            'salesman_type'   => 'regular',
            'location_code'   => 'L001',
            'location_name'   => 'Main Depot',
            'channel_code'    => 'CH01',
            'channel_name'    => 'Grocery',
            'customer_status' => 1,
            'stock_code'      => 'SKU-001',
            'description'     => 'Product A',
            'size'            => '500g',
            'brand'           => 'BrandX',
            'uom'             => 'CS',
            'quantity'        => 10.0,
            'sales'           => 1500.0,
            'fg_quantity'     => 2.0,
            'fg_sales'        => 300.0,
            'promo_quantity'  => 1.0,
            'promo_sales'     => 150.0,
            'credit_memo'     => 0.0,
        ];

        $pipeline = $this->makePipeline([$salesRow]);
        $pipeline->consolidateSingleAccount($account, [2026]);

        $row = DB::table('consolidated_sales_reports')
            ->where('account_code', $account->account_code)
            ->where('year', 2026)
            ->where('month', 1)
            ->where('stock_code', 'SKU-001')
            ->first();

        $this->assertNotNull($row, 'consolidated_sales_reports should have a row for this account/year/month/sku');
        $this->assertEquals('C001', $row->customer_code);
        $this->assertEquals(10.0, $row->quantity);
        $this->assertEquals(1500.0, $row->sales);
        $this->assertEquals(2.0, $row->fg_quantity);
        $this->assertEquals(300.0, $row->fg_sales);
        $this->assertEquals(1.0, $row->promo_quantity);
        $this->assertEquals(150.0, $row->promo_sales);
        $this->assertEquals('S001', $row->salesman_code);
        $this->assertEquals('CH01', $row->channel_code);
        $this->assertEquals('L001', $row->location_code);
    }

    public function test_upsert_updates_existing_row_rather_than_inserting_duplicate(): void
    {
        $account = $this->makeTestAccount();

        $salesRow = (object) [
            'customer_code'   => 'C001',
            'customer_name'   => 'Test Customer',
            'city'            => null, 'province' => null,
            'salesman_code'   => null, 'salesman_name' => null, 'salesman_type' => null,
            'location_code'   => null, 'location_name' => null,
            'channel_code'    => null, 'channel_name' => null,
            'customer_status' => 1,
            'stock_code'      => 'SKU-001',
            'description'     => 'Product A',
            'size'            => null, 'brand' => 'BrandX', 'uom' => 'CS',
            'quantity'        => 5.0, 'sales' => 750.0,
            'fg_quantity'     => 0.0, 'fg_sales' => 0.0,
            'promo_quantity'  => 0.0, 'promo_sales' => 0.0,
            'credit_memo'     => 0.0,
        ];

        $pipeline = $this->makePipeline([$salesRow]);
        $pipeline->consolidateSingleAccount($account, [2026]);

        // Run again with updated quantity — should update, not insert a second row
        $salesRow->quantity = 20.0;
        $salesRow->sales    = 3000.0;
        $pipeline2 = $this->makePipeline([$salesRow]);
        $pipeline2->consolidateSingleAccount($account, [2026]);

        $count = DB::table('consolidated_sales_reports')
            ->where('account_code', $account->account_code)
            ->where('year', 2026)
            ->where('stock_code', 'SKU-001')
            ->count();

        $this->assertEquals(12, $count, 'There should be exactly 12 rows (one per month for year 2026), upsert must not create duplicates');

        $row = DB::table('consolidated_sales_reports')
            ->where('account_code', $account->account_code)
            ->where('year', 2026)
            ->where('month', 1)
            ->where('stock_code', 'SKU-001')
            ->first();

        $this->assertEquals(20.0, $row->quantity, 'Quantity should be updated to the latest value');
    }

    public function test_no_json_files_are_written_during_consolidation(): void
    {
        $account = $this->makeTestAccount();

        Storage::fake('local');

        $pipeline = $this->makePipeline();
        $pipeline->consolidateSingleAccount($account, [2026]);

        Storage::disk('local')->assertMissing(
            'reports/consolidated_account_data-' . $account->account_code . '-2026-1.json'
        );
    }

    public function test_inventory_data_is_written_to_sqlite(): void
    {
        $account = $this->makeTestAccount();

        $inventoryRow = (object) [
            'location_code' => 'L001',
            'location_name' => 'Main Depot',
            'stock_code'    => 'SKU-001',
            'description'   => 'Product A',
            'size'          => '500g',
            'uom'           => 'CS',
            'total'         => 50.0,
        ];

        $pipeline = $this->makePipeline([], [$inventoryRow]);
        $pipeline->consolidateSingleAccount($account, [2026]);

        $count = DB::connection('sqlite_reports')
            ->table('inventory_data')
            ->where('account_code', $account->account_code)
            ->where('year', 2026)
            ->where('month', 1)
            ->count();

        $this->assertGreaterThan(0, $count, 'inventory_data in SQLite should have rows');

        // Cleanup
        DB::connection('sqlite_reports')
            ->table('inventory_data')
            ->where('account_code', $account->account_code)
            ->delete();
    }

    public function test_null_customer_code_is_stored_as_empty_string(): void
    {
        $account = $this->makeTestAccount();

        $salesRow = (object) [
            'customer_code'   => null,
            'customer_name'   => 'Walk-in Customer',
            'city'            => null, 'province' => null,
            'salesman_code'   => null, 'salesman_name' => null, 'salesman_type' => null,
            'location_code'   => null, 'location_name' => null,
            'channel_code'    => null, 'channel_name' => null,
            'customer_status' => 0,
            'stock_code'      => 'SKU-002',
            'description'     => 'Product B',
            'size'            => null, 'brand' => 'BrandY', 'uom' => 'PC',
            'quantity'        => 1.0, 'sales' => 100.0,
            'fg_quantity'     => 0.0, 'fg_sales' => 0.0,
            'promo_quantity'  => 0.0, 'promo_sales' => 0.0,
            'credit_memo'     => 0.0,
        ];

        $pipeline = $this->makePipeline([$salesRow]);
        $pipeline->consolidateSingleAccount($account, [2026]);

        $row = DB::table('consolidated_sales_reports')
            ->where('account_code', $account->account_code)
            ->where('year', 2026)
            ->where('month', 1)
            ->where('stock_code', 'SKU-002')
            ->first();

        $this->assertNotNull($row);
        $this->assertEquals('', $row->customer_code, 'Null customer_code must be stored as empty string for unique index correctness');
    }
}
