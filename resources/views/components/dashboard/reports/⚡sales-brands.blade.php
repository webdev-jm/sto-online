<?php

use Livewire\Component;
use App\Http\Traits\SalesDataAggregator;

new class extends Component
{
    use SalesDataAggregator;
    public $year;
    public $chart_data = [];

    public function mount() {
        $this->year = date('Y');
        $this->chartUpdated();
    }

    public function updatedYear() {
        $this->chartUpdated();
    }

    public function chartUpdated() {
        $raw = $this->getYearlySalesData($this->year);

        $this->chart_data = collect($raw)
            ->groupBy('brand')
            ->map(function ($items, $brandName) {
                return [
                    'name' => $brandName ?: 'Other', // Handle empty/null brands
                    'y'    => round($items->sum('sales'), 2)
                ];
            })
            ->sortByDesc('y') // Sort highest sales first
            ->values()        // Reset array keys (0, 1, 2...)
            ->toArray();      // Convert to pure Array for JS

        $this->dispatch('update-chart', data: $this->chart_data);
    }
};
?>

<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">SALES BY BRAND</h3>
            <div class="card-tools">
                <input type="number" class="form-control form-control-sm" wire:model.live="year">
            </div>
        </div>
        <div class="card-body" wire:ignore>
            <div id="container5"></div>
        </div>
    </div>
</div>

@assets
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/data.js"></script>
    <script src="https://code.highcharts.com/modules/drilldown.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/export-data.js"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>
@endassets

@script
    <script>
        let chart;

        const initChart = () => {
            chart = Highcharts.chart('container5', {
                chart: {
                    type: 'pie'
                },
                title: {
                    text: 'Sales by Brands'
                },
                series: [{
                    name: 'Sales',
                    colorByPoint: true,
                    data: $wire.chart_data['data']
                }]
            });
        };

        initChart();

        $wire.on('update-chart', (event) => {
            chart.series[0].setData($wire.chart_data);
            chart.setTitle({text: 'SALES BY BRAND ' + $wire.year});
        });
    </script>
@endscript
