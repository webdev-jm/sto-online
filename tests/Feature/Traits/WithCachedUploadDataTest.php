<?php

namespace Tests\Feature\Traits;

use App\Http\Traits\WithCachedUploadData;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Tests\TestCase;

class WithCachedUploadDataTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    public function test_fresh_component_has_no_key_and_writes_nothing_to_the_cache(): void
    {
        Cache::spy();

        Livewire::test(CachedUploadDataProbe::class)
            ->assertSet('uploadDataKey', null)
            ->assertSee('EMPTY')
            ->call('nextPage')
            ->assertSet('uploadDataKey', null);

        Cache::shouldNotHaveReceived('put');
    }

    public function test_rows_are_kept_out_of_the_snapshot(): void
    {
        $component = Livewire::test(CachedUploadDataProbe::class)
            ->call('load', 2000)
            ->assertSee('COUNT: 2000');

        $snapshot = $component->snapshot;

        $this->assertArrayNotHasKey('rows', $snapshot['data']);
        $this->assertStringNotContainsString('ROW-0001', json_encode($snapshot));
        $this->assertLessThan(1000, strlen(json_encode($snapshot)));
        $this->assertNotNull($component->get('uploadDataKey'));
    }

    public function test_rows_survive_later_requests_and_reach_the_view(): void
    {
        Livewire::test(CachedUploadDataProbe::class)
            ->call('load', 25)
            ->assertSee('ROW-0001')
            ->assertSee('ROW-0010')
            ->assertDontSee('ROW-0011')
            ->call('nextPage')
            ->assertSee('COUNT: 25')
            ->assertSee('ROW-0011')
            ->assertDontSee('ROW-0010')
            ->call('nextPage')
            ->assertSee('ROW-0025');
    }

    public function test_changed_rows_replace_the_stored_ones_under_the_same_key(): void
    {
        $component = Livewire::test(CachedUploadDataProbe::class)->call('load', 5);

        $key = $component->get('uploadDataKey');

        $component->call('load', 3)
            ->assertSet('uploadDataKey', $key)
            ->call('nextPage')
            ->call('previousPage')
            ->assertSee('COUNT: 3')
            ->assertDontSee('ROW-0004');
    }

    public function test_reset_clears_the_stored_rows(): void
    {
        Livewire::test(CachedUploadDataProbe::class)
            ->call('load', 5)
            ->assertSee('COUNT: 5')
            ->call('clear')
            ->assertSee('EMPTY')
            ->call('nextPage')
            ->assertSee('EMPTY');
    }

    public function test_expired_cache_falls_back_to_an_empty_preview(): void
    {
        $component = Livewire::test(CachedUploadDataProbe::class)
            ->call('load', 5)
            ->assertSee('COUNT: 5');

        Cache::flush();

        $component->call('nextPage')->assertSee('EMPTY');
    }

    public function test_two_uploads_do_not_share_rows(): void
    {
        $first  = Livewire::test(CachedUploadDataProbe::class)->call('load', 4);
        $second = Livewire::test(CachedUploadDataProbe::class)->call('load', 7);

        $this->assertNotSame($first->get('uploadDataKey'), $second->get('uploadDataKey'));

        $first->call('nextPage')->assertSee('COUNT: 4');
        $second->call('nextPage')->assertSee('COUNT: 7');
    }

    public function test_key_cannot_be_changed_from_the_browser(): void
    {
        $this->expectException(CannotUpdateLockedPropertyException::class);

        Livewire::test(CachedUploadDataProbe::class)
            ->call('load', 2)
            ->set('uploadDataKey', 'someone-elses-upload');
    }

    public function test_rows_cannot_be_set_from_the_browser(): void
    {
        $this->expectException(\Throwable::class);

        Livewire::test(CachedUploadDataProbe::class)->set('rows', [['code' => 'FORGED']]);
    }
}

class CachedUploadDataProbe extends Component
{
    use WithCachedUploadData;

    protected $rows;

    public int $page = 1;

    /**
     * @return array<int, string>
     */
    protected function cachedUploadData(): array
    {
        return ['rows'];
    }

    public function load(int $count): void
    {
        $this->rows = array_map(fn(int $i): array => ['code' => sprintf('ROW-%04d', $i)], range(1, $count));
        $this->page = 1;
    }

    public function nextPage(): void
    {
        $this->page++;
    }

    public function previousPage(): void
    {
        $this->page--;
    }

    public function clear(): void
    {
        $this->reset('rows');
    }

    public function render(): string
    {
        return <<<'BLADE'
            <div>
                @if(!empty($rows))
                    COUNT: {{ count($rows) }}
                    @foreach(array_slice($rows, ($page - 1) * 10, 10) as $row)
                        <span>{{ $row['code'] }}</span>
                    @endforeach
                @else
                    EMPTY
                @endif
            </div>
        BLADE;
    }
}
