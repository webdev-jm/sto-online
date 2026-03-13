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

    public function updatedYear() {
        $this->chartUpdated();
    }

    public function chartUpdated() {
        $raw = $this->getYearlySalesData($this->year);

        $this->chart_data = collect($raw)
            ->groupBy('area')
            ->map(function($items) {
                return $items->sum('sales');
            });
    }
};
?>

<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">SALES PER AREA</h3>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($areas as $area)
                    <div class="col-lg-4">
                        <label>{{ $area }}</label>
                        <input type="text" class="form-control border-bottom border-top-0 border-left-0 border-right-0" value="{{ number_format($chart_data[$area] ?? 0, 2) }}">
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
