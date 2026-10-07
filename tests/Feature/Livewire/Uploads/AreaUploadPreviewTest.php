<?php

namespace Tests\Feature\Livewire\Uploads;

use App\Http\Livewire\Uploads\Area;
use App\Models\Area as AreaModel;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

/**
 * An upload component built on WithCachedUploadData, exercised end to end:
 * the uploaded rows must stay out of the Livewire snapshot (paging a large
 * preview used to be rejected with a 413) while preview, paging and saving
 * keep working. Runs against in-memory tables only.
 */
class AreaUploadPreviewTest extends TestCase
{
    private const CONNECTION = 'area_upload_preview';

    /** @var array<int, string> */
    private array $temporaryFiles = [];

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('database.connections.' . self::CONNECTION, [
            'driver'                  => 'sqlite',
            'database'                => ':memory:',
            'prefix'                  => '',
            'foreign_key_constraints' => false,
        ]);
        Config::set('database.default', self::CONNECTION);
        DB::purge(self::CONNECTION);

        $schema = Schema::connection(self::CONNECTION);

        $schema->create('account_upload_templates', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->unsignedBigInteger('upload_template_id')->nullable();
            $table->string('type')->nullable();
            $table->integer('start_row')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        $schema->create('areas', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->unsignedBigInteger('account_branch_id');
            $table->string('code');
            $table->string('name')->nullable();
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

        Cache::flush();

        $user     = User::factory()->make(['type' => 1, 'account_id' => null]);
        $user->id = 987654;

        $this->actingAs($user);

        Session::put('account', (object) ['id' => 1, 'short_name' => 'Test Account', 'account_code' => 'TEST']);
        Session::put('account_branch', (object) ['id' => 1, 'code' => 'BR01', 'name' => 'Branch 1']);
    }

    protected function tearDown(): void
    {
        foreach ($this->temporaryFiles as $file) {
            @unlink($file);
        }

        array_map('unlink', glob(storage_path('app/area-uploads/*_area-preview-test-*.xlsx')) ?: []);

        DB::purge(self::CONNECTION);

        parent::tearDown();
    }

    /**
     * @param  array<int, array<int, mixed>>  $rows
     */
    private function excelFile(array $rows): UploadedFile
    {
        $name = 'area-preview-test-' . uniqid() . '.xlsx';
        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $name;

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->fromArray($rows, null, 'A1', true);
        (new Xlsx($spreadsheet))->save($path);

        $this->temporaryFiles[] = $path;

        return UploadedFile::fake()->createWithContent($name, file_get_contents($path));
    }

    /**
     * @return array<int, array<int, string>>
     */
    private function sheet(int $areas): array
    {
        $rows = [['AREA UPLOAD'], ['CODE', 'NAME']];

        for ($i = 1; $i <= $areas; $i++) {
            $rows[] = [sprintf('AREA-%03d', $i), "Area {$i}"];
        }

        return $rows;
    }

    public function test_fresh_component_shows_no_preview(): void
    {
        Livewire::test(Area::class)
            ->assertStatus(200)
            ->assertDontSee('PREVIEW')
            ->assertSet('uploadDataKey', null);
    }

    public function test_uploaded_rows_are_previewed_but_not_stored_in_the_snapshot(): void
    {
        $component = Livewire::test(Area::class)
            ->set('file', $this->excelFile($this->sheet(25)))
            ->assertHasNoErrors()
            ->assertSee('PREVIEW')
            ->assertSee('COUNT: 25')
            ->assertSee('AREA-001')
            ->assertSee('AREA-010')
            ->assertDontSee('AREA-011');

        $snapshot = $component->snapshot;

        $this->assertArrayNotHasKey('area_data', $snapshot['data']);
        $this->assertStringNotContainsString('AREA-001', json_encode($snapshot));
    }

    public function test_preview_can_be_paged_through(): void
    {
        Livewire::test(Area::class)
            ->set('file', $this->excelFile($this->sheet(25)))
            ->call('gotoPage', 2, 'page')
            ->assertSee('COUNT: 25')
            ->assertSee('AREA-011')
            ->assertSee('AREA-020')
            ->assertDontSee('AREA-010')
            ->call('nextPage', 'page')
            ->assertSee('AREA-025')
            ->call('previousPage', 'page')
            ->assertSee('AREA-011');
    }

    public function test_upload_saves_the_stored_rows(): void
    {
        Livewire::test(Area::class)
            ->set('file', $this->excelFile($this->sheet(12)))
            ->call('gotoPage', 2, 'page')
            ->call('uploadData')
            ->assertSet('upload_triggered', true)
            ->assertRedirect(route('area.index'));

        $this->assertSame(12, AreaModel::count());
        $this->assertSame('Area 12', AreaModel::where('code', 'AREA-012')->value('name'));
    }

    public function test_existing_areas_are_flagged_and_not_saved_twice(): void
    {
        AreaModel::create(['account_id' => 1, 'account_branch_id' => 1, 'code' => 'AREA-002', 'name' => 'Already here']);

        Livewire::test(Area::class)
            ->set('file', $this->excelFile($this->sheet(3)))
            ->assertSee('COUNT: 3')
            ->call('uploadData');

        $this->assertSame(3, AreaModel::count());
        $this->assertSame('Already here', AreaModel::where('code', 'AREA-002')->value('name'));
    }

    public function test_a_second_file_replaces_the_first_preview(): void
    {
        Livewire::test(Area::class)
            ->set('file', $this->excelFile($this->sheet(25)))
            ->assertSee('COUNT: 25')
            ->set('file', $this->excelFile($this->sheet(4)))
            ->assertSee('COUNT: 4')
            ->assertDontSee('AREA-005');
    }

    public function test_wrong_header_clears_the_preview_and_reports_the_format(): void
    {
        $wrongHeader    = $this->sheet(3);
        $wrongHeader[1] = ['ID', 'TITLE'];

        Livewire::test(Area::class)
            ->set('file', $this->excelFile($this->sheet(5)))
            ->assertSee('COUNT: 5')
            ->set('file', $this->excelFile($wrongHeader))
            ->assertSet('err_msg', 'Invalid format. Please provide an Excel file with the correct format (CODE, NAME).')
            ->assertDontSee('PREVIEW');
    }

    public function test_non_excel_file_is_rejected(): void
    {
        Livewire::test(Area::class)
            ->set('file', UploadedFile::fake()->create('test.pdf', 100, 'application/pdf'))
            ->assertHasErrors(['file'])
            ->assertDontSee('PREVIEW');
    }
}
