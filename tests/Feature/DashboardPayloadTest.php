<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\User;
use App\Services\OllamaService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

/**
 * The dashboard components must not carry their datasets in the Livewire
 * snapshot: every interaction posts the snapshot back, and the combined
 * request body was rejected by the web server (413) once the data grew.
 */
class DashboardPayloadTest extends TestCase
{
    use DatabaseTransactions;

    private const HEAVY_PROPERTIES = ['chart_data', 'table_data', 'raw_table_data', 'products', 'brands'];

    /** Each test reads its own year, since the yearly datasets are memoized per process. */
    private static int $nextYear = 2090;

    private int $year;

    private Account $account;

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

        $sqlite = DB::connection('sqlite_reports');

        $sqlite->statement('CREATE TABLE inventory_data (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            account_code TEXT, location_code TEXT, location_name TEXT,
            stock_code TEXT, description TEXT, size TEXT, uom TEXT,
            year INTEGER, month INTEGER, type TEXT, total REAL DEFAULT 0
        )');

        $sqlite->statement('CREATE TABLE inventory_aging (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            account_code TEXT, location_code TEXT, location_name TEXT,
            stock_code TEXT, description TEXT, size TEXT, uom TEXT,
            inventory REAL DEFAULT 0, expiry_date TEXT, year INTEGER, month INTEGER
        )');

        $sqlite->statement('CREATE TABLE sales_data (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            account_code TEXT NOT NULL, account_name TEXT, area TEXT,
            customer_code TEXT, customer_name TEXT, city TEXT, province TEXT,
            salesman_code TEXT, salesman_name TEXT, salesman_type TEXT,
            location_code TEXT, location_name TEXT, channel_code TEXT, channel_name TEXT,
            customer_status INTEGER DEFAULT 0, year INTEGER NOT NULL, month INTEGER NOT NULL,
            stock_code TEXT, description TEXT, size TEXT, brand TEXT, uom TEXT,
            quantity REAL DEFAULT 0, sales REAL DEFAULT 0
        )');

        Cache::flush();
        Http::fake(['*' => Http::response([], 200)]);

        $this->year = self::$nextYear++;

        $uniq = uniqid();

        $this->account = Account::create([
            'sms_account_id'   => null,
            'account_code'     => 'PAY-' . $uniq,
            'account_name'     => 'Payload Test Account ' . $uniq,
            'short_name'       => 'PAYLOAD DIST',
            'account_password' => bcrypt('password'),
        ]);

        $this->actingAs(User::factory()->create(['type' => 1]));
    }

    protected function tearDown(): void
    {
        DB::purge('sqlite_reports');

        parent::tearDown();
    }

    private function seedInventory(string $stockCode, float $total = 120): void
    {
        DB::connection('sqlite_reports')->table('inventory_data')->insert([
            'account_code' => $this->account->account_code,
            'stock_code'   => $stockCode,
            'description'  => 'Payload Soap',
            'size'         => '135g',
            'uom'          => 'PCS',
            'year'         => $this->year,
            'month'        => 3,
            'total'        => $total,
        ]);
    }

    private function seedAging(string $stockCode, float $inventory = 80): void
    {
        DB::connection('sqlite_reports')->table('inventory_aging')->insert([
            'account_code' => $this->account->account_code,
            'stock_code'   => $stockCode,
            'description'  => 'Payload Soap',
            'size'         => '135g',
            'uom'          => 'PCS',
            'inventory'    => $inventory,
            'expiry_date'  => now()->addDays(60)->toDateString(),
            'year'         => $this->year,
            'month'        => 3,
        ]);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public function heavyComponentProvider(): array
    {
        return [
            'ubo matrix'         => ['dashboard.reports.ubo-matrix'],
            'sales sku'          => ['dashboard.reports.sales-sku'],
            'sales brands'       => ['dashboard.reports.sales-brands'],
            'sales per address'  => ['dashboard.accounts.sales-per-address'],
            'inventory aging'    => ['dashboard.reports.inventory-aging'],
            'inventory ending'   => ['dashboard.reports.inventory-ending'],
            'inventory inactive' => ['dashboard.reports.inventory-inactive'],
        ];
    }

    /**
     * @dataProvider heavyComponentProvider
     */
    public function test_component_snapshot_does_not_carry_its_dataset(string $component): void
    {
        $this->seedInventory('PAY-SKU-1');
        $this->seedAging('PAY-SKU-1');

        $snapshot = Livewire::test($component, ['year' => $this->year, 'account_id' => null])
            ->assertStatus(200)
            ->snapshot;

        foreach (self::HEAVY_PROPERTIES as $property) {
            $this->assertArrayNotHasKey($property, $snapshot['data'], "{$component} still stores {$property} in its snapshot.");
        }

        $this->assertLessThan(2000, strlen(json_encode($snapshot)), "{$component} snapshot is larger than expected.");
    }

    public function test_inventory_aging_sends_chart_data_to_the_browser_on_mount(): void
    {
        $this->seedAging('PAY-SKU-1', 80);

        $component = Livewire::test('dashboard.reports.inventory-aging', ['year' => $this->year])
            ->assertDispatched('update-chart', function (string $event, array $params): bool {
                $bucket = collect($params['data']['data'])->firstWhere('name', '1–3 Months');

                return $params['year'] === $this->year && (float) $bucket['y'] === 80.0;
            });

        $initScript = implode('', $component->effects['scripts'] ?? []);

        $this->assertStringContainsString('"name":"PAY-SKU-1","y":80', $initScript);
    }

    public function test_inventory_aging_insight_is_built_without_stored_chart_data(): void
    {
        $this->seedAging('PAY-SKU-1', 80);

        $mock = Mockery::mock(OllamaService::class);
        $mock->shouldReceive('chat')
            ->once()
            ->with(Mockery::on(fn(array $messages): bool => str_contains($messages[1]['content'], '1–3 Months: 80 pcs')))
            ->andReturn('Most stock expires within three months.');

        $this->app->instance(OllamaService::class, $mock);

        Livewire::test('dashboard.reports.inventory-aging', ['year' => $this->year])
            ->call('generateInsight')
            ->assertSet('insight', 'Most stock expires within three months.');
    }

    public function test_inventory_aging_reports_no_data_for_an_empty_year(): void
    {
        $mock = Mockery::mock(OllamaService::class);
        $mock->shouldReceive('chat')
            ->once()
            ->with(Mockery::on(fn(array $messages): bool => str_contains($messages[1]['content'], '1–3 Months: 0 pcs')))
            ->andReturn('No stock on hand.');

        $this->app->instance(OllamaService::class, $mock);

        Livewire::test('dashboard.reports.inventory-aging', ['year' => $this->year])
            ->call('generateInsight')
            ->assertSet('insight', 'No stock on hand.');
    }

    public function test_inventory_ending_lists_rows_and_filters_by_search(): void
    {
        $this->seedInventory('PAY-SKU-1', 120);
        $this->seedInventory('PAY-SKU-2', 45);

        Livewire::test('dashboard.reports.inventory-ending', ['year' => $this->year])
            ->assertSee('PAYLOAD DIST')
            ->assertSee('PAY-SKU-1')
            ->assertSee('PAY-SKU-2')
            ->set('search', 'sku-2')
            ->assertDontSee('PAY-SKU-1')
            ->assertSee('PAY-SKU-2')
            ->set('search', 'no such product')
            ->assertDontSee('PAY-SKU-1')
            ->assertDontSee('PAY-SKU-2')
            ->set('search', '')
            ->assertSee('PAY-SKU-1')
            ->assertSee('PAY-SKU-2');
    }

    public function test_inventory_ending_search_does_not_repeat_the_sell_in_requests(): void
    {
        $this->seedInventory('PAY-SKU-1');

        $component = Livewire::test('dashboard.reports.inventory-ending', ['year' => $this->year]);

        Http::assertSentCount(1);

        $component->set('search', 'pay')->set('search', 'sku')->set('selectedBrand', '');

        Http::assertSentCount(1);
    }

    public function test_inventory_ending_rows_are_scoped_to_the_selected_account(): void
    {
        $this->seedInventory('PAY-SKU-1');

        Livewire::test('dashboard.reports.inventory-ending', ['year' => $this->year, 'account_id' => $this->account->id])
            ->assertSee('PAY-SKU-1');

        Livewire::test('dashboard.reports.inventory-ending', ['year' => $this->year, 'account_id' => $this->account->id + 999999])
            ->assertDontSee('PAY-SKU-1');
    }

    public function test_inventory_ending_insight_counts_rows_without_stored_table_data(): void
    {
        $this->seedInventory('PAY-SKU-1');
        $this->seedInventory('PAY-SKU-2');

        $mock = Mockery::mock(OllamaService::class);
        $mock->shouldReceive('chat')
            ->once()
            ->with(Mockery::on(fn(array $messages): bool => str_contains($messages[1]['content'], '2 SKUs tracked across 1 distributors')))
            ->andReturn('Two SKUs on hand.');

        $this->app->instance(OllamaService::class, $mock);

        Livewire::test('dashboard.reports.inventory-ending', ['year' => $this->year])
            ->call('generateInsight')
            ->assertSet('insight', 'Two SKUs on hand.');
    }

    public function test_inventory_inactive_renders_and_filters_with_no_rows(): void
    {
        Livewire::test('dashboard.reports.inventory-inactive', ['year' => $this->year])
            ->assertStatus(200)
            ->set('search', 'anything')
            ->set('selectedBrand', 'ANY')
            ->assertStatus(200);
    }
}
