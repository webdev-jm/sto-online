<?php

namespace Tests\Feature\Reports;

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class AccountDashboardTest extends TestCase
{
    use DatabaseTransactions;

    private function actingAsUser(): User
    {
        $user = User::factory()->create(['type' => 0]);
        $this->actingAs($user);
        return $user;
    }

    private function makeAccount(): Account
    {
        return Account::create([
            'sms_account_id' => null,
            'account_code'   => 'TEST-001',
            'account_name'   => 'Test Account',
            'short_name'     => 'TEST',
            'account_password' => bcrypt('password'),
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('report.account-dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_without_account_in_session_is_redirected_home(): void
    {
        $this->actingAsUser();

        $response = $this->withoutMiddleware(\Spatie\Permission\Middlewares\PermissionMiddleware::class)
            ->get(route('report.account-dashboard'));

        $response->assertRedirect(route('home'));
    }

    public function test_authenticated_user_with_account_in_session_sees_dashboard(): void
    {
        $this->actingAsUser();
        $account = $this->makeAccount();

        $response = $this->withoutMiddleware(\Spatie\Permission\Middlewares\PermissionMiddleware::class)
            ->withSession(['account' => $account])
            ->get(route('report.account-dashboard'));

        $response->assertStatus(200);
        $response->assertSee($account->account_name);
    }

    public function test_livewire_account_report_component_mounts_with_account(): void
    {
        $this->actingAsUser();
        $account = $this->makeAccount();

        Livewire::test('dashboard.account-report', ['account' => $account])
            ->assertSet('account_id', $account->id)
            ->assertSet('account_name', $account->account_name)
            ->assertSet('selected_tab', 'sales')
            ->assertStatus(200);
    }

    public function test_livewire_account_report_tab_switching(): void
    {
        $this->actingAsUser();
        $account = $this->makeAccount();

        Livewire::test('dashboard.account-report', ['account' => $account])
            ->assertSet('selected_tab', 'sales')
            ->call('selectTab', 'inventories')
            ->assertSet('selected_tab', 'inventories')
            ->assertSet('initializedTabs', ['sales', 'inventories']);
    }

    public function test_livewire_account_report_shows_accounts_tab(): void
    {
        $this->actingAsUser();
        $account = $this->makeAccount();

        Livewire::test('dashboard.account-report', ['account' => $account])
            ->assertSee('ACCOUNTS');
    }

    public function test_livewire_account_report_year_navigation(): void
    {
        $this->actingAsUser();
        $account = $this->makeAccount();

        $currentYear = (int) date('Y');

        Livewire::test('dashboard.account-report', ['account' => $account])
            ->assertSet('globalYear', $currentYear)
            ->call('previousYear')
            ->assertSet('globalYear', $currentYear - 1)
            ->call('nextYear')
            ->assertSet('globalYear', $currentYear);
    }
}
