<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\District;
use App\Models\Salesman;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Session;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SalesmanInfoTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // account_id 0 has no matching SMSAccount so getConnectionName() returns null → default connection
        $user = User::factory()->create(['type' => 1, 'username' => fake()->unique()->userName(), 'account_id' => null]);
        $this->actingAs($user);

        $superadmin = Role::firstOrCreate(['name' => 'superadmin']);
        $user->assignRole($superadmin);

        Session::put('account', (object) ['id' => 1]);
        Session::put('account_branch', (object) ['id' => 1]);
    }

    public function test_returns_district_and_areas_for_salesman(): void
    {

        $district = District::create([
            'account_branch_id' => 1,
            'district_code'     => 'D001',
        ]);

        $area = Area::create([
            'account_id'        => 1,
            'account_branch_id' => 1,
            'code'              => 'A001',
            'name'              => 'North',
        ]);

        $district->areas()->attach($area->id);

        $salesman = Salesman::create([
            'account_id'        => 1,
            'account_branch_id' => 1,
            'district_id'       => $district->id,
            'code'              => 'SM001',
            'name'              => 'Test Salesman',
            'type'              => 'VAN SALESMAN',
        ]);

        $response = $this->getJson(route('customer.salesman.info', $salesman->id));

        $response->assertOk()
            ->assertJson([
                'district_code' => 'D001',
                'areas'         => [
                    ['code' => 'A001', 'name' => 'North'],
                ],
            ]);
    }

    public function test_returns_null_district_when_salesman_has_no_district(): void
    {

        $salesman = Salesman::create([
            'account_id'        => 1,
            'account_branch_id' => 1,
            'district_id'       => null,
            'code'              => 'SM002',
            'name'              => 'No District Salesman',
            'type'              => 'PRE-BOOKING',
        ]);

        $response = $this->getJson(route('customer.salesman.info', $salesman->id));

        $response->assertOk()
            ->assertJson([
                'district_code' => null,
                'areas'         => [],
            ]);
    }

    public function test_returns_404_for_nonexistent_salesman(): void
    {

        $this->getJson(route('customer.salesman.info', 99999))
            ->assertNotFound();
    }

    public function test_requires_authentication(): void
    {
        auth()->logout();

        $this->getJson(route('customer.salesman.info', 1))
            ->assertUnauthorized();
    }
}
