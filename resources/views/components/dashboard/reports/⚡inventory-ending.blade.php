<?php

use Livewire\Component;
use Livewire\Attributes\Reactive;
use Livewire\WithPagination;
use App\Http\Traits\SalesDataAggregator;

new class extends Component
{
    use SalesDataAggregator, WithPagination;
    protected $paginationTheme = 'bootstrap';

    #[Reactive]
    public $year;
    public $table_data = [];
    public $per_page = 10;

    public function mount($year) {
        $this->year = $year;
        $this->chartUpdated();
    }

    public function updatedYear() {
        $this->resetPage('page');
        $this->chartUpdated();
    }

    public function chartUpdated() {
        $raw = $this->getYearlyInventoryData($this->year);
        $inventories = collect($raw);

        // $latest_month = $inventories->max('month');
        $latest_month = 2;

        $this->table_data = $inventories->groupBy(function($item) {
            return $item['sku'].'_'.$item['short_name'];
        })
        ->filter(function($items) use($latest_month) {
            return $items->where('month', $latest_month)->isNotEmpty();
        })
        ->map(function($items) {
            $first = $items->first();
            $total = $items->sum('total');

            return [
                'account' => $first['short_name'],
                'sku'     => $first['sku'],
                'total'   => $total
            ];
        })
        ->values();
    }

    public function getPaginatedDataProperty() {
        $page     = $this->getPage();
        $sliced   = $this->table_data->slice(($page - 1) * $this->per_page, $this->per_page);
        $total    = $this->table_data->count();

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $sliced,
            $total,
            $this->per_page,
            $page,
            ['pageName' => 'page']
        );
    }
};
?>

<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">ENDING INVENTORY {{ $year }} <i class="fa fa-spinner fa-spin" wire:loading></i></h3>
        </div>
        <div class="card-body table-responsive p-0" style="max-height: 320px; overflow-y: auto;">
            <table class="table table-bordered table-sm table-hover m-0 text-xs">
                <thead class="bg-secondary" style="position: sticky; top: 0; z-index: 10;">
                    <tr>
                        <th>DISTRIBUTOR</th>
                        <th>STOCK CODE</th>
                        <th>OPENING BALANCE</th>
                        <th>SELL IN</th>
                        <th>SELL OUT</th>
                        <th>SHOULD BE</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($this->paginatedData as $data)
                        <tr>
                            <td>{{ $data['account'] }}</td>
                            <td>{{ $data['sku'] }}</td>
                            <td>{{ number_format($data['total']) }}</td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $this->paginatedData->links(data: ['scrollTo' => false]) }}
        </div>
    </div>
</div>

