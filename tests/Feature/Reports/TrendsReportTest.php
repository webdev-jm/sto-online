<?php

namespace Tests\Feature\Reports;

use App\Http\Livewire\Reports\Trends;
use App\Models\Account;
use App\Models\AccountBranch;
use App\Models\AccountDatabase;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class TrendsReportTest extends TestCase
{
    private ?Account $testAccount = null;
    private ?AccountBranch $testBranch = null;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('database.connections.sqlite_reports', [
            'driver'                  => 'sqlite',
            'database'                => ':memory:',
            'prefix'                  => '',
            'foreign_key_constraints' => false,
        ]);

        DB::purge('sqlite_reports');

        DB::connection('sqlite_reports')->statement('
            CREATE TABLE IF NOT EXISTS consolidated_sales_reports (
                id           INTEGER PRIMARY KEY AUTOINCREMENT,
                account_code TEXT NOT NULL,
                account_name TEXT,
                year         INTEGER NOT NULL,
                month        INTEGER NOT NULL,
                stock_code   TEXT NOT NULL,
                description  TEXT,
                sales        REAL DEFAULT 0,
                quantity     REAL DEFAULT 0,
                created_at   TEXT,
                updated_at   TEXT
            )
        ');

        DB::connection('sqlite_reports')->statement('
            CREATE TABLE IF NOT EXISTS inventory_data (
                id           INTEGER PRIMARY KEY AUTOINCREMENT,
                account_code TEXT NOT NULL,
                stock_code   TEXT NOT NULL,
                description  TEXT,
                size         TEXT,
                uom          TEXT,
                year         INTEGER NOT NULL,
                month        INTEGER NOT NULL,
                total        REAL DEFAULT 0
            )
        ');

        DB::connection('sqlite_reports')->statement('
            CREATE TABLE IF NOT EXISTS sales_data (
                id              INTEGER PRIMARY KEY AUTOINCREMENT,
                account_code    TEXT NOT NULL,
                account_name    TEXT,
                area            TEXT,
                customer_code   TEXT,
                customer_name   TEXT,
                city            TEXT,
                province        TEXT,
                salesman_code   TEXT,
                salesman_name   TEXT,
                salesman_type   TEXT,
                location_code   TEXT,
                location_name   TEXT,
                channel_code    TEXT,
                channel_name    TEXT,
                customer_status INTEGER DEFAULT 0,
                stock_code      TEXT NOT NULL,
                uom             TEXT,
                year            INTEGER NOT NULL,
                month           INTEGER NOT NULL,
                quantity        REAL DEFAULT 0
            )
        ');

        Cache::flush();
    }

    protected function tearDown(): void
    {
        DB::purge('sqlite_reports');

        if ($this->testAccount) {
            AccountDatabase::where('account_id', $this->testAccount->id)->forceDelete();
            AccountBranch::where('account_id', $this->testAccount->id)->forceDelete();
            User::where('account_id', $this->testAccount->id)->forceDelete();
            $this->testAccount->forceDelete();
        }

        Account::where('account_code', 'like', 'TRD-TAB-%')->forceDelete();

        parent::tearDown();
    }

    private function setupAccountContext(): array
    {
        $uniq = uniqid();

        $account = Account::create([
            'sms_account_id'   => null,
            'account_code'     => 'TRD-' . $uniq,
            'account_name'     => 'Test Trends Account ' . $uniq,
            'short_name'       => 'TRD',
            'account_password' => bcrypt('password'),
        ]);

        $this->testAccount = $account;

        $connectionName = "account_{$account->id}_db";

        Config::set("database.connections.{$connectionName}", [
            ...config('database.connections.mysql'),
            'database' => config('database.connections.mysql.database'),
        ]);

        AccountDatabase::create([
            'account_id'      => $account->id,
            'database_name'   => config('database.connections.mysql.database'),
            'connection_name' => $connectionName,
        ]);

        $branch = AccountBranch::create([
            'account_id' => $account->id,
            'code'       => 'TRD-BR01',
            'name'       => 'Trends Branch 1',
        ]);

        $this->testBranch = $branch;

        $user = User::factory()->create([
            'type'       => 0,
            'account_id' => $account->id,
            'username'   => 'trd_test_' . $uniq,
            'email'      => 'trd_' . $uniq . '@test.com',
        ]);

        $this->actingAs($user);

        return ['account' => $account, 'branch' => $branch, 'user' => $user];
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('report.trends'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_without_branch_in_session_is_redirected(): void
    {
        $user = User::factory()->create(['type' => 0, 'username' => 'trd_guest_' . uniqid()]);
        $this->actingAs($user);

        $response = $this->withoutMiddleware(\Spatie\Permission\Middlewares\PermissionMiddleware::class)
            ->get(route('report.trends'));

        $response->assertRedirect();
    }

    public function test_authenticated_user_with_valid_branch_sees_trends_page(): void
    {
        ['account' => $account, 'branch' => $branch] = $this->setupAccountContext();

        $response = $this->withoutMiddleware(\Spatie\Permission\Middlewares\PermissionMiddleware::class)
            ->withSession(['account' => $account, 'account_branch' => $branch])
            ->get(route('report.trends'));

        $response->assertStatus(200);
    }

    public function test_trends_component_mounts_successfully(): void
    {
        ['branch' => $branch] = $this->setupAccountContext();

        Livewire::test(Trends::class, ['account_branch' => $branch])
            ->assertStatus(200);
    }

    public function test_trends_component_growth_stats_is_array_after_mount(): void
    {
        ['branch' => $branch] = $this->setupAccountContext();

        $component = Livewire::test(Trends::class, ['account_branch' => $branch]);

        $growthStats = $component->get('growth_stats');

        $this->assertIsArray($growthStats);
        $this->assertArrayHasKey('six_mo', $growthStats);
        $this->assertArrayHasKey('mom', $growthStats);
        $this->assertArrayHasKey('recent_total', $growthStats);
        $this->assertArrayHasKey('prior_total', $growthStats);
    }

    public function test_trends_component_sku_table_has_growers_and_decliners_keys(): void
    {
        ['branch' => $branch] = $this->setupAccountContext();

        $skuTable = Livewire::test(Trends::class, ['account_branch' => $branch])
            ->get('sku_table');

        $this->assertIsArray($skuTable);
        $this->assertArrayHasKey('growers', $skuTable);
        $this->assertArrayHasKey('decliners', $skuTable);
    }

    public function test_trends_component_sku_table_labels_are_set(): void
    {
        ['branch' => $branch] = $this->setupAccountContext();

        $component = Livewire::test(Trends::class, ['account_branch' => $branch]);

        $this->assertNotEmpty($component->get('recent_label'));
        $this->assertNotEmpty($component->get('prior_label'));
    }

    public function test_trends_tab_initializes_on_header_dashboard(): void
    {
        $user = User::factory()->create(['type' => 0, 'username' => 'trd_hdr_' . uniqid()]);
        $this->actingAs($user);

        Livewire::test('dashboard.header')
            ->assertSet('selected_tab', 'trends')
            ->call('selectTab', 'trends')
            ->assertSet('selected_tab', 'trends')
            ->assertSee('TRENDS');
    }

    private function getRollingPlan(): array
    {
        return (new class {
            use \App\Http\Traits\SalesDataAggregator;
            public function get(int $n): array { return $this->getRollingMonthPlan($n); }
        })->get(18);
    }

    private function seedSkuRows(string $accountCode, string $stockCode, string $description, array $months, float $totalSales): void
    {
        $salesPerMonth = $totalSales / count($months);

        foreach ($months as $m) {
            DB::connection('sqlite_reports')->table('consolidated_sales_reports')->insert([
                'account_code' => $accountCode,
                'account_name' => 'Growth Test Account',
                'year'         => $m['year'],
                'month'        => $m['month'],
                'stock_code'   => $stockCode,
                'description'  => $description,
                'sales'        => $salesPerMonth,
                'quantity'     => 1.0,
            ]);
        }
    }

    public function test_sku_growth_computation_correctly_identifies_growers_decliners_and_dropped_skus(): void
    {
        ['account' => $account, 'branch' => $branch] = $this->setupAccountContext();

        $plan         = $this->getRollingPlan();
        $recentMonths = array_slice($plan, 12, 6);
        $priorMonths  = array_slice($plan, 6, 6);
        $code         = $account->account_code;

        // SKU-A: prior=1000, recent=2000 → +100% (grower)
        $this->seedSkuRows($code, 'SKU-A', 'Product Alpha', $priorMonths, 1000);
        $this->seedSkuRows($code, 'SKU-A', 'Product Alpha', $recentMonths, 2000);

        // SKU-B: prior=2000, recent=1000 → -50% (decliner)
        $this->seedSkuRows($code, 'SKU-B', 'Product Beta', $priorMonths, 2000);
        $this->seedSkuRows($code, 'SKU-B', 'Product Beta', $recentMonths, 1000);

        // SKU-C: prior=500, no recent records → -100% (Bug 1 regression)
        $this->seedSkuRows($code, 'SKU-C', 'Product Charlie', $priorMonths, 500);

        // SKU-D: no prior, recent=800 → excluded (no baseline)
        $this->seedSkuRows($code, 'SKU-D', 'Product Delta', $recentMonths, 800);

        // SKU-E: prior=300, recent=300 → 0%, must not appear in either list (Bug 2/3)
        $this->seedSkuRows($code, 'SKU-E', 'Product Echo', $priorMonths, 300);
        $this->seedSkuRows($code, 'SKU-E', 'Product Echo', $recentMonths, 300);

        $skuTable = Livewire::test(Trends::class, ['account_branch' => $branch])
            ->get('sku_table');

        $growers   = collect($skuTable['growers']);
        $decliners = collect($skuTable['decliners']);

        // SKU-A must be in growers at +100%
        $this->assertTrue($growers->pluck('sku')->contains('SKU-A'));
        $this->assertEquals(100.0, $growers->firstWhere('sku', 'SKU-A')['change_pct']);

        // SKU-B must be in decliners at -50%
        $this->assertTrue($decliners->pluck('sku')->contains('SKU-B'));
        $this->assertEquals(-50.0, $decliners->firstWhere('sku', 'SKU-B')['change_pct']);

        // SKU-C must be in decliners at -100% (key Bug 1 assertion)
        $this->assertTrue($decliners->pluck('sku')->contains('SKU-C'), 'SKU-C (dropped to zero) must appear in decliners');
        $this->assertEquals(-100.0, $decliners->firstWhere('sku', 'SKU-C')['change_pct']);
        $this->assertEquals(0.0, $decliners->firstWhere('sku', 'SKU-C')['curr']);

        // SKU-D must not appear (no prior baseline)
        $this->assertFalse($growers->pluck('sku')->contains('SKU-D'));
        $this->assertFalse($decliners->pluck('sku')->contains('SKU-D'));

        // SKU-E must not appear (0% change, Bugs 2/3)
        $this->assertFalse($growers->pluck('sku')->contains('SKU-E'), 'SKU-E (0%) must not appear in growers');
        $this->assertFalse($decliners->pluck('sku')->contains('SKU-E'), 'SKU-E (0%) must not appear in decliners');

        // Invariants: growers have change_pct > 0, decliners have change_pct < 0
        $growers->each(fn($r) => $this->assertGreaterThan(0, $r['change_pct']));
        $decliners->each(fn($r) => $this->assertLessThan(0, $r['change_pct']));
    }

    public function test_trends_tab_initializes_on_account_dashboard(): void
    {
        $user = User::factory()->create(['type' => 0, 'username' => 'trd_acct_' . uniqid()]);
        $this->actingAs($user);

        $account = Account::create([
            'sms_account_id'   => null,
            'account_code'     => 'TRD-TAB-' . uniqid(),
            'account_name'     => 'Tab Test Account',
            'short_name'       => 'TAB',
            'account_password' => bcrypt('password'),
        ]);

        $tabs = Livewire::test('dashboard.account-report', ['account' => $account])
            ->assertSet('selected_tab', 'sales')
            ->call('selectTab', 'trends')
            ->assertSet('selected_tab', 'trends')
            ->get('initializedTabs');

        $this->assertContains('trends', $tabs);
    }
}
