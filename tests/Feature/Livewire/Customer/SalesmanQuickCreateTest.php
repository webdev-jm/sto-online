<?php

namespace Tests\Feature\Livewire\Customer;

use App\Http\Livewire\Customer\SalesmanQuickCreate;
use App\Models\District;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;
use Tests\TestCase;

class SalesmanQuickCreateTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // account_id 0 has no matching SMSAccount so getConnectionName() returns null → default connection
        $user = User::factory()->create(['type' => 1, 'username' => fake()->unique()->userName(), 'account_id' => null]);
        $this->actingAs($user);

        Session::put('account', (object) ['id' => 1, 'short_name' => 'Test', 'account_code' => 'TEST']);
        Session::put('account_branch', (object) ['id' => 1, 'code' => 'BR01', 'name' => 'Branch 1']);
    }

    public function test_modal_opens_and_closes(): void
    {
        Livewire::test(SalesmanQuickCreate::class)
            ->assertSet('showModal', false)
            ->call('openModal')
            ->assertSet('showModal', true)
            ->call('closeModal')
            ->assertSet('showModal', false);
    }

    public function test_salesman_validation_fails_with_missing_required_fields(): void
    {
        Livewire::test(SalesmanQuickCreate::class)
            ->call('save')
            ->assertHasErrors(['code', 'name', 'type']);
    }

    public function test_salesman_is_created_with_valid_data_and_event_dispatched(): void
    {
        Livewire::test(SalesmanQuickCreate::class)
            ->set('code', 'SM-TEST')
            ->set('name', 'Test Salesman')
            ->set('type', 'VAN SALESMAN')
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('salesmanCreated')
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('salesmen', [
            'code'              => 'SM-TEST',
            'name'              => 'Test Salesman',
            'type'              => 'VAN SALESMAN',
            'account_branch_id' => 1,
        ]);
    }

    public function test_district_is_created_inline(): void
    {
        Livewire::test(SalesmanQuickCreate::class)
            ->call('toggleDistrictForm')
            ->assertSet('showDistrictForm', true)
            ->set('districtCode', 'D-TEST')
            ->call('saveDistrict')
            ->assertHasNoErrors()
            ->assertSet('showDistrictForm', false);

        $this->assertDatabaseHas('districts', [
            'district_code'     => 'D-TEST',
            'account_branch_id' => 1,
        ]);
    }

    public function test_district_validation_fails_with_empty_code(): void
    {
        Livewire::test(SalesmanQuickCreate::class)
            ->call('saveDistrict')
            ->assertHasErrors(['districtCode']);
    }

    public function test_area_is_created_and_assigned_to_selected_district(): void
    {
        $district = District::create(['account_branch_id' => 1, 'district_code' => 'D-ASSIGN']);

        Livewire::test(SalesmanQuickCreate::class)
            ->set('district_id', $district->id)
            ->call('toggleAreaForm')
            ->assertSet('showAreaForm', true)
            ->set('areaCode', 'AR-TEST')
            ->set('areaName', 'Test Area')
            ->call('saveArea')
            ->assertHasNoErrors()
            ->assertSet('showAreaForm', false);

        $this->assertDatabaseHas('areas', [
            'code'              => 'AR-TEST',
            'name'              => 'Test Area',
            'account_id'        => 1,
            'account_branch_id' => 1,
        ]);

        $area = \App\Models\Area::where('code', 'AR-TEST')->first();
        $this->assertTrue($district->areas()->where('areas.id', $area->id)->exists());
    }

    public function test_area_validation_fails_with_missing_fields(): void
    {
        Livewire::test(SalesmanQuickCreate::class)
            ->call('saveArea')
            ->assertHasErrors(['areaCode', 'areaName']);
    }

    public function test_close_modal_resets_form(): void
    {
        Livewire::test(SalesmanQuickCreate::class)
            ->set('code', 'SM001')
            ->set('name', 'Some Name')
            ->call('openModal')
            ->call('closeModal')
            ->assertSet('code', '')
            ->assertSet('name', '')
            ->assertSet('showModal', false);
    }
}
