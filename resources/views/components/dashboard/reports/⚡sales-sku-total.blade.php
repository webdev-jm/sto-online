<?php

use Livewire\Component;
use Livewire\Attributes\Reactive;
use App\Http\Traits\SalesDataAggregator;

new class extends Component
{
    use SalesDataAggregator;

    #[Reactive]
    public $year;
    public $chart_data    = [];
    public $table_data    = [];
    public string $insight        = '';
    public bool   $loadingInsight = false;

    public function mount($year) {
        $this->year = $year;
        $this->loadChartData();
    }

    public function updatedYear(): void
    {
        $this->loadChartData();
        $this->generateInsight();
    }

    public function generateInsight(): void
    {
        $this->loadingInsight = true;
        $this->insight = app(\App\Services\OllamaService::class)->chat([
            ['role' => 'system', 'content' => 'You are a business data analyst for a Philippine FMCG distributor. Given chart data, respond with exactly one concise insight sentence. No markdown, no bullet points, no labels.'],
            ['role' => 'user',   'content' => $this->buildInsightSummary()],
        ]);
        $this->loadingInsight = false;
    }

    private function buildInsightSummary(): string
    {
        if ($this->table_data->isEmpty()) {
            return "No SKU sales data available for {$this->year}.";
        }
        $top3  = $this->table_data->take(3)
            ->map(fn($d) => "{$d['stock_code']} {$d['name']}: ₱" . number_format($d['y'], 2))->implode(', ');
        $total = $this->table_data->count();
        return "Top SKU rankings by sales for {$this->year}. {$total} SKUs total. Top 3: {$top3}.";
    }

    public function loadChartData(): void
    {
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

<div wire:init="generateInsight">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">TOP SKU BASED ON SALES ({{ $year }})</h3>
        </div>
        <div class="card-body table-responsive p-0" style="max-height: 700px; overflow-y: auto;">
            <table class="table table-bordered table-sm table-hover m-0 text-xs">
                <thead class="bg-secondary" style="position: sticky; top: 0; z-index: 10;">
                    <tr>
                        <th class="text-center p-0 align-middle" style="width: 50px;">Rank</th>
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
        <div class="card-footer text-xs text-muted">
            @if($loadingInsight)
                <i class="fa fa-spinner fa-spin fa-sm mr-1"></i> Generating insight...
            @else
                {{ $insight }}
            @endif
        </div>
    </div>
</div>

@script
    <script>
    </script>
@endscript
