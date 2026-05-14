<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class SalesMonitoringTest extends TestCase
{
    use DatabaseTransactions;

    private function actingAsAdmin(): User
    {
        $user = User::factory()->create(['type' => 1]);
        $this->actingAs($user);
        return $user;
    }

    public function test_component_mounts_with_todays_date(): void
    {
        $this->actingAsAdmin();

        Livewire::test('dashboard.monitoring.sales')
            ->assertSet('date', date('Y-m-d'));
    }

    public function test_year_badge_shows_year_from_date(): void
    {
        $this->actingAsAdmin();

        Livewire::test('dashboard.monitoring.sales')
            ->set('date', '2025-06-15')
            ->assertSee('2025');
    }

    public function test_updating_date_updates_year_badge(): void
    {
        $this->actingAsAdmin();

        Livewire::test('dashboard.monitoring.sales')
            ->set('date', '2024-01-01')
            ->assertSee('2024')
            ->set('date', '2026-12-31')
            ->assertSee('2026');
    }

    public function test_table_renders_twelve_month_headers_for_sales(): void
    {
        $this->actingAsAdmin();

        $component = Livewire::test('dashboard.monitoring.sales');

        foreach (['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'] as $month) {
            $component->assertSee($month);
        }
    }

    public function test_table_renders_sales_and_inventory_group_headers(): void
    {
        $this->actingAsAdmin();

        Livewire::test('dashboard.monitoring.sales')
            ->assertSee('SALES')
            ->assertSee('INVENTORY');
    }
}
