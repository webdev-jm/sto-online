<?php

namespace Tests\Feature\Reports;

use App\Http\Livewire\Reports\Trends;
use App\Models\Account;
use App\Models\AccountBranch;
use App\Models\AccountDatabase;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Livewire\Livewire;
use Tests\TestCase;

class TrendsReportTest extends TestCase
{
    private ?Account $testAccount = null;
    private ?AccountBranch $testBranch = null;

    protected function tearDown(): void
    {
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
            ->assertSet('selected_tab', 'sales')
            ->call('selectTab', 'trends')
            ->assertSet('selected_tab', 'trends')
            ->assertSee('TRENDS');
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
