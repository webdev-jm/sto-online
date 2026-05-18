<?php

namespace Tests\Feature;

use App\Models\Barangay;
use App\Models\Customer;
use App\Models\Municipality;
use App\Models\Province;
use App\Models\Region;
use App\Models\Salesman;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Session;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CustomerAddressTest extends TestCase
{
    use DatabaseTransactions;

    protected Region $region;
    protected Province $province;
    protected Municipality $municipality;
    protected Barangay $barangay;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create(['type' => 1, 'username' => fake()->unique()->userName(), 'account_id' => null]);
        $this->actingAs($user);

        $superadmin = Role::firstOrCreate(['name' => 'superadmin']);
        $user->assignRole($superadmin);

        Session::put('account', (object) ['id' => 1, 'short_name' => 'Test', 'account_code' => 'TEST']);
        Session::put('account_branch', (object) ['id' => 1, 'code' => 'BR01', 'name' => 'Branch 1']);

        $this->region       = Region::first() ?? Region::create(['region_name' => 'Region Test', 'description' => 'Test']);
        $this->province     = Province::where('region_id', $this->region->id)->first()
                              ?? Province::create(['region_id' => $this->region->id, 'province_name' => 'Test Province']);
        $this->municipality = Municipality::where('province_id', $this->province->id)->first()
                              ?? Municipality::create(['province_id' => $this->province->id, 'municipality_name' => 'Test City']);
        $this->barangay     = Barangay::where('municipality_id', $this->municipality->id)->first()
                              ?? Barangay::create(['municipality_id' => $this->municipality->id, 'barangay_name' => 'Test Barangay']);
    }

    public function test_location_provinces_returns_json_for_valid_region(): void
    {
        $response = $this->getJson(route('customer.location.provinces', $this->region->id));

        $response->assertOk()
            ->assertJsonFragment(['id' => $this->province->id, 'province_name' => $this->province->province_name]);
    }

    public function test_location_municipalities_returns_json_for_valid_province(): void
    {
        $response = $this->getJson(route('customer.location.municipalities', $this->province->id));

        $response->assertOk()
            ->assertJsonFragment(['id' => $this->municipality->id, 'municipality_name' => $this->municipality->municipality_name]);
    }

    public function test_location_barangays_returns_json_for_valid_municipality(): void
    {
        $response = $this->getJson(route('customer.location.barangays', $this->municipality->id));

        $response->assertOk()
            ->assertJsonFragment(['id' => $this->barangay->id, 'barangay_name' => $this->barangay->barangay_name]);
    }

    public function test_customer_store_saves_location_ids_and_text_fields(): void
    {
        $salesman = Salesman::create([
            'account_id'        => 1,
            'account_branch_id' => 1,
            'code'              => 'SM-ADDR',
            'name'              => 'Address Salesman',
            'type'              => 'VAN SALESMAN',
        ]);

        $response = $this->post(route('customer.store'), [
            'salesman_id'     => $salesman->id,
            'code'            => 'CUST-ADDR',
            'name'            => 'Address Customer',
            'street'          => '123 Main St',
            'province_id'     => $this->province->id,
            'municipality_id' => $this->municipality->id,
            'barangay_id'     => $this->barangay->id,
        ]);

        $response->assertRedirect(route('customer.index'));

        $this->assertDatabaseHas('customers', [
            'code'            => 'CUST-ADDR',
            'province_id'     => $this->province->id,
            'municipality_id' => $this->municipality->id,
            'barangay_id'     => $this->barangay->id,
            'province'        => $this->province->province_name,
            'city'            => $this->municipality->municipality_name,
            'brgy'            => $this->barangay->barangay_name,
        ]);
    }

    public function test_customer_store_fails_validation_with_missing_location_ids(): void
    {
        $salesman = Salesman::create([
            'account_id'        => 1,
            'account_branch_id' => 1,
            'code'              => 'SM-ADDR2',
            'name'              => 'Address Salesman 2',
            'type'              => 'VAN SALESMAN',
        ]);

        $response = $this->post(route('customer.store'), [
            'salesman_id' => $salesman->id,
            'code'        => 'CUST-NOADDR',
            'name'        => 'No Address Customer',
            'street'      => '123 Main St',
            // province_id, municipality_id, barangay_id intentionally omitted
        ]);

        $response->assertSessionHasErrors(['province_id', 'municipality_id', 'barangay_id']);
    }

    public function test_customer_update_saves_location_ids_and_text_fields(): void
    {
        $salesman = Salesman::create([
            'account_id'        => 1,
            'account_branch_id' => 1,
            'code'              => 'SM-UPD',
            'name'              => 'Update Salesman',
            'type'              => 'VAN SALESMAN',
        ]);

        $customer = Customer::create([
            'account_id'        => 1,
            'account_branch_id' => 1,
            'salesman_id'       => $salesman->id,
            'code'              => 'CUST-UPD',
            'name'              => 'Update Customer',
            'street'            => 'Old Street',
            'brgy'              => 'Old Brgy',
            'city'              => 'Old City',
            'province'          => 'Old Province',
        ]);

        $response = $this->post(route('customer.update', encrypt($customer->id)), [
            'salesman_id'     => $salesman->id,
            'code'            => 'CUST-UPD',
            'name'            => 'Update Customer',
            'street'          => 'New Street',
            'province_id'     => $this->province->id,
            'municipality_id' => $this->municipality->id,
            'barangay_id'     => $this->barangay->id,
        ]);

        $response->assertRedirect(route('customer.show', encrypt($customer->id)));

        $this->assertDatabaseHas('customers', [
            'id'              => $customer->id,
            'province_id'     => $this->province->id,
            'municipality_id' => $this->municipality->id,
            'barangay_id'     => $this->barangay->id,
            'province'        => $this->province->province_name,
            'city'            => $this->municipality->municipality_name,
            'brgy'            => $this->barangay->barangay_name,
        ]);
    }
}
