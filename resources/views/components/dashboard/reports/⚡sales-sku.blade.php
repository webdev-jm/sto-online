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

        // Group by SKU -> Sum Sales -> Sort -> Top 10
        $top10 = collect($raw)
            ->groupBy('sku')
            ->map(function($items) {
                return [
                    'name' => $items->first()['full_name'], // Grab name from first item
                    'y' => $items->sum('sales')
                ];
            })
            ->sortByDesc('y')
            ->take(10)
            ->values(); // Reset keys

        $this->chart_data = [
            'categories' => $top10->pluck('name')->toArray(),
            'data'       => $top10->pluck('y')->map(fn($val) => round($val, 2))->toArray()
        ];

        $this->dispatch('update-chart', data: $this->chart_data);
    }
};
?>

<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">TOP SKU BASED ON SALES ({{ $year }})</h3>
            <div class="card-tools">
                <input type="number" class="form-control form-control-sm" wire:model.live="year">
            </div>
        </div>
        <div class="card-body" wire:ignore>
            <div id="container3"></div>
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
        chart = Highcharts.chart('container3', {
            chart: {
                type: 'bar'
            },
            title: {
                text: 'TOP SKU BASED ON SALES ' + $wire.year
            },
            xAxis: {
                // Bind Categories dynamically
                categories: $wire.chart_data['categories'],
                title: {
                    text: 'Product SKU'
                }
            },
            yAxis: {
                min: 0,
                title: {
                    text: 'Total Sales ({{ $year }})',
                    align: 'high'
                }
            },
            tooltip: {
                valuePrefix: '₱ '
            },
            series: [{
                name: 'Sales Amount',
                // Bind Data dynamically
                data: $wire.chart_data['data']
            }]
        });
    };

    initChart();

    $wire.on('update-chart', (event) => {
        chart.series[0].setData($wire.chart_data['data']);
        chart.xAxis[0].setCategories($wire.chart_data['categories']);
        chart.setTitle({text: 'TOP SKU BASED ON SALES ' + $wire.year});
    });

</script>
@endscript
