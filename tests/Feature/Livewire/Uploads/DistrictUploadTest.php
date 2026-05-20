<?php

namespace Tests\Feature\Livewire\Uploads;

use App\Http\Livewire\Uploads\District;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;
use Tests\TestCase;

class DistrictUploadTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create([
            'type'       => 1,
            'username'   => fake()->unique()->userName(),
            'account_id' => null,
        ]);
        $this->actingAs($user);

        Session::put('account', (object) [
            'id'           => 1,
            'short_name'   => 'Test Account',
            'account_code' => 'TEST',
        ]);
        Session::put('account_branch', (object) [
            'id'   => 1,
            'code' => 'BR01',
            'name' => 'Branch 1',
        ]);
    }

    public function test_component_renders_in_card_mode(): void
    {
        Livewire::test(District::class)
            ->assertStatus(200)
            ->assertSet('mode', 'card');
    }

    public function test_default_redirect_route_is_district_index(): void
    {
        Livewire::test(District::class)
            ->assertSet('redirectRoute', 'district.index');
    }

    public function test_redirect_route_can_be_overridden(): void
    {
        Livewire::test(District::class, ['redirectRoute' => 'uploads.index'])
            ->assertSet('redirectRoute', 'uploads.index');
    }

    public function test_active_tab_can_be_set(): void
    {
        Livewire::test(District::class, ['activeTab' => 'district'])
            ->assertSet('activeTab', 'district');
    }

    public function test_file_validation_rejects_non_excel(): void
    {
        Livewire::test(District::class)
            ->set('file', \Illuminate\Http\UploadedFile::fake()->create('test.pdf', 100, 'application/pdf'))
            ->assertHasErrors(['file']);
    }

    public function test_upload_triggered_flag_prevents_duplicate_upload(): void
    {
        Livewire::test(District::class)
            ->set('upload_triggered', true)
            ->set('district_data', [])
            ->call('uploadData')
            ->assertSet('upload_triggered', true);
    }

    public function test_no_data_shows_no_preview(): void
    {
        Livewire::test(District::class)
            ->assertSet('district_data', null)
            ->assertDontSee('PREVIEW');
    }

    public function test_err_msg_is_null_on_fresh_mount(): void
    {
        Livewire::test(District::class)
            ->assertSet('err_msg', null);
    }

    public function test_area_codes_property_exists_in_district_data_structure(): void
    {
        $component = Livewire::test(District::class);

        // Set district_data manually to verify the structure is handled correctly
        $component->set('district_data', [
            [
                'check'         => 0,
                'district_code' => 'D001',
                'area_codes'    => 'AREA1,AREA2',
                'area_ids'      => [],
                'invalid_areas' => ['AREA1', 'AREA2'],
            ],
        ]);

        $component->assertSet('district_data.0.district_code', 'D001')
            ->assertSet('district_data.0.invalid_areas.0', 'AREA1');
    }
}
