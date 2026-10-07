<?php

namespace Tests\Feature\Livewire\Sales;

use App\Http\Livewire\Sales\SalesUpload;
use App\Jobs\SalesImportJob;
use App\Models\Account;
use App\Models\AccountBranch;
use App\Models\AccountDatabase;
use App\Models\SMSAccount;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\Livewire;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

/**
 * The uploaded rows must stay out of the Livewire snapshot: paging through a
 * large preview posted every row back and the web server answered 413.
 */
class SalesUploadPreviewTest extends TestCase
{
    use DatabaseTransactions;

    private const HEADER = [
        'Invoice Date',
        'Customer Code',
        'Salesman Code',
        'Invoice Number',
        'Warehouse Code',
        'BEVI Sku Code',
        'Quantity',
        'Unit of Measure Code',
        'Unit Price Incl VAT',
        'Amount',
        'Amount Including VAT',
        'Line Discount',
    ];

    /** Only the real account tables are written to; everything else lives in memory. */
    protected $connectionsToTransact = ['mysql'];

    private const TENANT = 'sales_upload_preview';

    protected function setUp(): void
    {
        parent::setUp();

        $smsAccount = SMSAccount::has('company')->first();

        if ($smsAccount === null) {
            $this->markTestSkipped('No SMS account with a company is available in the local sms_db.');
        }

        Config::set('database.connections.' . self::TENANT, [
            'driver'                  => 'sqlite',
            'database'                => ':memory:',
            'prefix'                  => '',
            'foreign_key_constraints' => false,
        ]);
        Config::set('database.default', self::TENANT);
        DB::purge(self::TENANT);

        $this->createTenantTables();

        Cache::flush();

        Livewire::component('sales.customer-maintenance', MaintenanceModalStub::class);
        Livewire::component('sales.location-maintenance', MaintenanceModalStub::class);

        $uniq = uniqid();

        $account = Account::create([
            'sms_account_id'   => $smsAccount->id,
            'account_code'     => 'SUP-' . $uniq,
            'account_name'     => 'Sales Upload Preview ' . $uniq,
            'short_name'       => 'SUP',
            'account_password' => bcrypt('password'),
        ]);

        AccountDatabase::on('mysql')->create([
            'account_id'      => $account->id,
            'database_name'   => ':memory:',
            'connection_name' => self::TENANT,
        ]);

        $branch = AccountBranch::create([
            'account_id' => $account->id,
            'code'       => 'SUP-BR01',
            'name'       => 'Preview Branch',
        ]);

        $user = User::factory()->make(['type' => 0, 'account_id' => $account->id]);
        $user->id = 987654;

        $this->actingAs($user);

        session(['account' => $account, 'account_branch' => $branch]);
    }

    protected function tearDown(): void
    {
        DB::purge(self::TENANT);

        parent::tearDown();
    }

    private function createTenantTables(): void
    {
        $schema = Schema::connection(self::TENANT);

        $schema->create('account_branches', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->unsignedBigInteger('bevi_area_id')->nullable();
            $table->string('code');
            $table->string('name');
            $table->string('branch_token')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        $schema->create('product_mappings', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('external_stock_code')->nullable();
            $table->integer('type')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        $schema->create('account_upload_templates', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->unsignedBigInteger('upload_template_id')->nullable();
            $table->string('type')->nullable();
            $table->integer('start_row')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        foreach (['customers', 'locations'] as $name) {
            $schema->create($name, function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('account_id');
                $table->unsignedBigInteger('account_branch_id');
                $table->string('code');
                $table->unsignedBigInteger('channel_id')->nullable();
                $table->unsignedBigInteger('salesman_id')->nullable();
                $table->integer('status')->default(0);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        $schema->create('sales_uploads', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->unsignedBigInteger('account_branch_id');
            $table->unsignedBigInteger('user_id');
            $table->integer('sku_count')->default(0);
            $table->decimal('total_quantity', 15, 2)->default(0);
            $table->decimal('total_price_vat', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('total_amount_vat', 15, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        $schema->create('activity_log', function (Blueprint $table): void {
            $table->id();
            $table->string('log_name')->nullable();
            $table->text('description');
            $table->nullableMorphs('subject');
            $table->string('event')->nullable();
            $table->nullableMorphs('causer');
            $table->json('properties')->nullable();
            $table->uuid('batch_uuid')->nullable();
            $table->timestamps();
        });
    }

    /**
     * A sheet shaped like the upload template: a title row, the header row and
     * the given number of lines, none of which match a maintained customer.
     *
     * @return array<int, array<int, mixed>>
     */
    private function sheet(int $lines): array
    {
        $rows = [['SALES UPLOAD'], self::HEADER];

        for ($line = 1; $line <= $lines; $line++) {
            $rows[] = [
                sprintf('2026-01-%02d', min($line, 28)) . sprintf(' 00:%02d:00', $line % 60),
                'NO-SUCH-CUSTOMER',
                'SM01',
                sprintf('INV-%04d', $line),
                'NO-SUCH-WAREHOUSE',
                'NO-SUCH-SKU',
                '10',
                'PCS',
                '12.50',
                '111.61',
                '125.00',
                '0',
            ];
        }

        return $rows;
    }

    public function test_snapshot_does_not_carry_the_uploaded_rows(): void
    {
        $component = Livewire::test(SalesUpload::class)
            ->call('checkData', $this->sheet(45))
            ->assertSee('COUNT: 45');

        $snapshot = $component->snapshot;

        $this->assertArrayNotHasKey('sales_data', $snapshot['data']);
        $this->assertArrayNotHasKey('data', $snapshot['data']);
        $this->assertStringNotContainsString('INV-0001', json_encode($snapshot));
        $this->assertNotNull($component->get('uploadKey'));
    }

    public function test_preview_can_be_paged_through(): void
    {
        Livewire::test(SalesUpload::class)
            ->call('checkData', $this->sheet(45))
            ->assertSee('INV-0001')
            ->assertSee('INV-0020')
            ->assertDontSee('INV-0021')
            ->call('gotoPage', 2, 'page')
            ->assertDontSee('INV-0020')
            ->assertSee('INV-0021')
            ->assertSee('INV-0040')
            ->call('nextPage', 'page')
            ->assertSee('INV-0041')
            ->assertSee('INV-0045')
            ->assertDontSee('INV-0040')
            ->call('previousPage', 'page')
            ->assertSee('INV-0021');
    }

    public function test_uploaded_file_is_previewed_paged_and_rechecked(): void
    {
        $name = 'preview-test-' . uniqid() . '.xlsx';
        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $name;

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->fromArray($this->sheet(25), null, 'A1', true);
        (new Xlsx($spreadsheet))->save($path);

        try {
            $component = Livewire::test(SalesUpload::class)
                ->set('file', UploadedFile::fake()->createWithContent($name, file_get_contents($path)))
                ->assertHasNoErrors()
                ->assertSee('COUNT: 25')
                ->assertSee('INV-0020')
                ->assertDontSee('INV-0021');

            $this->assertStringNotContainsString('INV-0001', json_encode($component->snapshot));

            $component
                ->call('gotoPage', 2, 'page')
                ->assertSee('INV-0021')
                ->assertSee('INV-0025')
                ->call('updateData')
                ->assertSee('COUNT: 25')
                ->assertSee('INV-0025');
        } finally {
            @unlink($path);
            array_map('unlink', glob(storage_path('app/sales-uploads/*-' . $name)) ?: []);
        }
    }

    public function test_recheck_without_an_upload_reports_an_empty_file(): void
    {
        Livewire::test(SalesUpload::class)
            ->call('updateData')
            ->assertSet('err_msg', 'The file is empty or has no data rows.')
            ->assertDontSee('COUNT:');
    }

    public function test_save_queues_the_stored_rows_and_clears_the_preview(): void
    {
        Bus::fake([SalesImportJob::class]);

        $component = Livewire::test(SalesUpload::class)
            ->call('checkData', $this->sheet(3));

        $key = $component->get('uploadKey');

        $component->call('saveUpload')
            ->assertSet('upload_triggered', true)
            ->assertRedirect(route('sales.index'));

        Bus::assertDispatched(SalesImportJob::class, 1);

        $this->assertNull(Cache::get("sales-upload-preview:{$key}:sales"));
        $this->assertNull(Cache::get("sales-upload-preview:{$key}:data"));
    }

    public function test_save_without_an_upload_reports_no_data(): void
    {
        Bus::fake([SalesImportJob::class]);

        Livewire::test(SalesUpload::class)
            ->call('saveUpload')
            ->assertSet('err_msg', 'No data has been saved!')
            ->assertNoRedirect();

        Bus::assertNotDispatched(SalesImportJob::class);
    }

    public function test_empty_sheet_is_rejected(): void
    {
        Livewire::test(SalesUpload::class)
            ->call('checkData', [['SALES UPLOAD'], self::HEADER])
            ->assertSet('err_msg', 'The file is empty or has no data rows.')
            ->assertDontSee('COUNT:');
    }

    public function test_wrong_header_is_rejected(): void
    {
        $sheet       = $this->sheet(2);
        $sheet[1][0] = 'Posting Date';

        Livewire::test(SalesUpload::class)
            ->call('checkData', $sheet)
            ->assertSet('err_msg', 'Invalid header format. Please provide an excel file with the correct column structure.')
            ->assertDontSee('COUNT:');
    }

    public function test_a_rejected_sheet_replaces_the_previous_preview(): void
    {
        Livewire::test(SalesUpload::class)
            ->call('checkData', $this->sheet(4))
            ->assertSee('COUNT: 4')
            ->call('checkData', [['SALES UPLOAD'], self::HEADER])
            ->assertDontSee('COUNT:')
            ->assertDontSee('INV-0001');
    }

    public function test_upload_key_cannot_be_changed_from_the_browser(): void
    {
        $this->expectException(\Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException::class);

        Livewire::test(SalesUpload::class)
            ->call('checkData', $this->sheet(2))
            ->set('uploadKey', 'someone-elses-upload');
    }
}

/**
 * Stands in for the customer and location maintenance modals, which read
 * reference tables that are not part of this test.
 */
class MaintenanceModalStub extends Component
{
    public function render(): string
    {
        return '<div></div>';
    }
}
