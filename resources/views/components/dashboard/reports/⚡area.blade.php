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
    public string $insight        = '';
    public bool   $loadingInsight = false;

    public $areas = [
        'SOUTH LUZON',
        'NORTH LUZON',
        'VISAYAS',
        'MINDANAO',
        'BICOL AREA',
        'METRO MANILA',
    ];

    public function mount($year) {
        $this->year = $year;
        $this->chartUpdated();
    }

    public function updatedYear(): void
    {
        $this->chartUpdated();
        $this->generateInsight();
    }

    public function chartUpdated(): void
    {
        $this->chart_data = collect($this->getYearlySalesData($this->year))
            ->groupBy('area')
            ->map(function($items) {
                return $items->sum('sales');
            })
            ->toArray();
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
        if (empty($this->chart_data)) {
            return "No area sales data available for {$this->year}.";
        }
        $sorted = collect($this->chart_data)->sortByDesc(fn($v) => $v);
        $top    = $sorted->keys()->first();
        $bottom = $sorted->keys()->last();
        return "Sales per area for {$this->year}: "
            . collect($this->chart_data)->map(fn($v, $k) => "{$k}: ₱" . number_format($v, 2))->implode(', ')
            . ". Highest: {$top}, Lowest: {$bottom}.";
    }
};
?>

<div wire:init="generateInsight">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">SALES PER AREA</h3>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($areas as $area)
                    <div class="col-lg-4">
                        <label>{{ $area }}</label>
                        <input type="text" class="form-control border-bottom border-top-0 border-left-0 border-right-0 bg-light text-right" value="{{ number_format($chart_data[$area] ?? 0, 2) }}" readonly>
                    </div>
                @endforeach
            </div>
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
