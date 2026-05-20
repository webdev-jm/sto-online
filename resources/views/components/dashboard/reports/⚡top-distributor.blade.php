<?php

use Livewire\Component;
use Livewire\Attributes\Reactive;
use App\Http\Traits\SalesDataAggregator;

new class extends Component
{
    use SalesDataAggregator;

    #[Reactive]
    public $year;
    #[Reactive]
    public ?int $account_id = null;
    public $chart_data    = [];
    public string $insight        = '';
    public bool   $loadingInsight = false;

    public function mount($year, $account_id = null): void {
        $this->year = $year;
        $this->account_id = $account_id;
        $this->chartUpdated();
    }

    public function updatedYear(): void
    {
        $this->chartUpdated();
        $this->generateInsight();
    }

    public function generateInsight(): void
    {
        $this->loadingInsight = true;
        try {
            $this->insight = app(\App\Services\OllamaService::class)->chat([
                ['role' => 'system', 'content' => 'You are a business data analyst for a Philippine FMCG distributor. Given chart data, respond with exactly one concise insight sentence. No markdown, no bullet points, no labels.'],
                ['role' => 'user',   'content' => $this->buildInsightSummary()],
            ]);
        } catch (\App\Exceptions\AiUnavailableException) {
        }
        $this->loadingInsight = false;
    }

    private function buildInsightSummary(): string
    {
        $data = collect($this->chart_data);
        if ($data->isEmpty()) {
            return "No distributor sales data available for {$this->year}.";
        }
        $list = $data->map(fn($v, $k) => "{$k}: ₱" . number_format($v, 2))->implode(', ');
        return "Top 5 distributors by sales for {$this->year}: {$list}.";
    }

    public function chartUpdated(): void
    {
        $this->chart_data = $this->getSalesData($this->year, $this->account_id)
            ->groupBy('account_name')
            ->map(function($items) {
                return $items->sum('sales');
            })
            ->sortByDesc(fn ($sum) => $sum)
            ->take(5);
    }

};
?>

<div wire:init="generateInsight">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">TOP 5 DISTRIBUTOR</h3>
        </div>
        <div class="card-body p-1">
            <ul class="list-group">
                @php
                    $num = 1;
                @endphp
                @foreach($chart_data as $account => $data)
                    <li class="list-group-item py-1 px-1">
                        <strong>{{ $num++ }}.</strong> {{ $account }}<b class="float-right">{{ number_format($data, 2) }}</b>
                    </li>
                @endforeach
            </ul>
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
