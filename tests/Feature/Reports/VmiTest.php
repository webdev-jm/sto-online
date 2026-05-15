<?php

namespace Tests\Feature\Reports;

use App\Exceptions\AiUnavailableException;
use App\Http\Livewire\Reports\Vmi;
use App\Models\Account;
use App\Models\AccountBranch;
use App\Models\AccountDatabase;
use App\Models\User;
use App\Services\OllamaService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class VmiTest extends TestCase
{
    private ?Account $testAccount = null;
    private ?string $accountConnectionName = null;

    protected function tearDown(): void
    {
        if ($this->accountConnectionName) {
            Schema::connection($this->accountConnectionName)->dropIfExists('sales');
            Schema::connection($this->accountConnectionName)->dropIfExists('monthly_inventories');
        }

        if ($this->testAccount) {
            AccountDatabase::where('account_id', $this->testAccount->id)->forceDelete();
            AccountBranch::where('account_id', $this->testAccount->id)->forceDelete();
            User::where('account_id', $this->testAccount->id)->forceDelete();
            $this->testAccount->forceDelete();
        }

        parent::tearDown();
    }

    private function setupAccountContext(): array
    {
        $uniq = uniqid();

        $account = Account::create([
            'sms_account_id'   => null,
            'account_code'     => 'VMI-' . $uniq,
            'account_name'     => 'Test VMI Account ' . $uniq,
            'short_name'       => 'VMI',
            'account_password' => bcrypt('password'),
        ]);

        $this->testAccount = $account;

        $connectionName = "account_{$account->id}_db";
        $this->accountConnectionName = $connectionName;

        Config::set("database.connections.{$connectionName}", [
            ...config('database.connections.mysql'),
            'database' => config('database.connections.mysql.database'),
        ]);

        Schema::connection($connectionName)->create('monthly_inventories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id')->nullable();
            $table->unsignedBigInteger('account_branch_id')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->integer('year');
            $table->integer('month');
            $table->integer('type')->default(1);
            $table->string('uom');
            $table->integer('total');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::connection($connectionName)->create('sales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id')->nullable();
            $table->unsignedBigInteger('account_branch_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->date('date');
            $table->string('uom')->nullable();
            $table->integer('quantity')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        AccountDatabase::create([
            'account_id'      => $account->id,
            'database_name'   => config('database.connections.mysql.database'),
            'connection_name' => $connectionName,
        ]);

        $branch = AccountBranch::create([
            'account_id' => $account->id,
            'code'       => 'VMI-BR01',
            'name'       => 'VMI Branch 1',
        ]);

        $user = User::factory()->create([
            'type'       => 0,
            'account_id' => $account->id,
            'username'   => 'vmi_test_' . $uniq,
            'email'      => 'vmi_' . $uniq . '@test.com',
        ]);

        $this->actingAs($user);

        return ['account' => $account, 'branch' => $branch, 'user' => $user];
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('report.vmi'));

        $response->assertRedirect(route('login'));
    }

    public function test_vmi_component_mounts_with_correct_defaults(): void
    {
        ['branch' => $branch] = $this->setupAccountContext();

        Livewire::test(Vmi::class, ['account_branch' => $branch])
            ->assertSet('year', date('Y'))
            ->assertSet('month', 4)
            ->assertSet('parameter', 4)
            ->assertSet('ai_recommendations', [])
            ->assertStatus(200);
    }

    public function test_months_arr_contains_1_2_3_and_6(): void
    {
        ['branch' => $branch] = $this->setupAccountContext();

        $months_arr = Livewire::test(Vmi::class, ['account_branch' => $branch])
            ->get('months_arr');

        $this->assertArrayHasKey(1, $months_arr);
        $this->assertArrayHasKey(2, $months_arr);
        $this->assertArrayHasKey(3, $months_arr);
        $this->assertArrayHasKey(6, $months_arr);
        $this->assertArrayNotHasKey(4, $months_arr);
        $this->assertArrayNotHasKey(5, $months_arr);
        $this->assertCount(4, $months_arr);
    }

    public function test_month_is_clamped_to_maximum_12(): void
    {
        ['branch' => $branch] = $this->setupAccountContext();

        Livewire::test(Vmi::class, ['account_branch' => $branch])
            ->set('month', 15)
            ->assertSet('month', 12);
    }

    public function test_month_is_clamped_to_minimum_1(): void
    {
        ['branch' => $branch] = $this->setupAccountContext();

        Livewire::test(Vmi::class, ['account_branch' => $branch])
            ->set('month', 0)
            ->assertSet('month', 1);
    }

    public function test_ai_recommendations_are_populated_on_success(): void
    {
        ['branch' => $branch] = $this->setupAccountContext();

        // AI returns analysis per product; with no seeded inventory the page is empty so recommendations will be [].
        $fakeResponse = json_encode([
            ['product_id' => 1, 'analysis' => 'Understocked — coverage below target'],
        ]);

        $mock = $this->mock(OllamaService::class);
        $mock->shouldReceive('chat')->once()->andReturn($fakeResponse);

        Livewire::test(Vmi::class, ['account_branch' => $branch])
            ->call('getAiRecommendations')
            ->assertSet('ai_loading', false)
            ->assertSet('ai_error', null)
            ->assertSet('ai_recommendations.1.analysis', 'Understocked — coverage below target');
    }

    public function test_ai_recommendations_are_empty_when_service_unavailable(): void
    {
        ['branch' => $branch] = $this->setupAccountContext();

        $mock = $this->mock(OllamaService::class);
        $mock->shouldReceive('chat')->once()->andThrow(new AiUnavailableException('Service down'));

        Livewire::test(Vmi::class, ['account_branch' => $branch])
            ->call('getAiRecommendations')
            ->assertSet('ai_loading', false)
            ->assertSet('ai_recommendations', [])
            ->assertSet('ai_error', 'AI service is unavailable. Please try again later.');
    }
}
