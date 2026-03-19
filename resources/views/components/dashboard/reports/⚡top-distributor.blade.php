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

    public function mount($year) {
        $this->year = $year;
        $this->chartUpdated();
    }

    public function updatedYear() {
        $this->chartUpdated();
    }

    public function chartUpdated() {
        $raw = $this->getYearlySalesData($this->year);

        $this->chart_data = collect($raw)
            ->groupBy('account_name')
            ->map(function($items) {
                return $items->sum('sales');
            })
            ->sortByDesc(fn ($sum) => $sum)
            ->take(5);
    }

};
?>

<div>
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
    </div>
</div>
