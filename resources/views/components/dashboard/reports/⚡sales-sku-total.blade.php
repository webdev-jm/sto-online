<?php

use Livewire\Component;
use Livewire\Attributes\Reactive;
use App\Http\Traits\SalesDataAggregator;

new class extends Component
{
    use SalesDataAggregator;

    #[Reactive]
    public $year;
    public $chart_data = [];
    public $table_data = [];

    public function mount($year) {
        $this->year = $year;
        $this->loadChartData();
    }

    public function updatedYear() {
        $this->loadChartData();
    }

    public function loadChartData() {
        $raw = $this->getYearlySalesData($this->year);

        // Group, Sum, Sort
        $ranked = collect($raw)
            ->groupBy('sku')
            ->map(function($items) {
                return [
                    'stock_code' => $items->first()['sku'],
                    'name' => $items->first()['full_name'],
                    'y' => $items->sum('sales')
                ];
            })
            ->sortByDesc('y')
            ->values();

        $this->table_data = $ranked;
    }
};
?>

<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">TOP SKU BASED ON SALES ({{ $year }})</h3>
            <div class="card-tools">
                {{-- <input type="number" class="form-control form-control-sm" wire:model.live.debounce.500ms="year"> --}}
            </div>
        </div>
        <div class="card-body table-responsive p-0" style="max-height: 416px; overflow-y: auto;">
            <table class="table table-bordered table-sm table-hover m-0 text-xs">
                <thead class="bg-secondary" style="position: sticky; top: 0; z-index: 10;">
                    <tr>
                        <th class="text-center" style="width: 50px;">Rank</th>
                        <th>StockCode</th>
                        <th>Description</th>
                        <th class="text-right">Total Sales
                    </tr>
                </thead>
                <tbody>
                    @foreach ($table_data as $index => $item)
                        <tr>
                            <td class="text-center px-0">{{ $index + 1 }}</td>
                            <td>{{ $item['stock_code'] }}</td>
                            <td>{{ $item['name'] }}</td>
                            <td class="text-right">{{ number_format($item['y'], 2, '.', ',') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer"></div>
    </div>
</div>

@script
    <script>
    </script>
@endscript
