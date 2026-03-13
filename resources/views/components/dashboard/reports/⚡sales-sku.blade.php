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

        // Group by SKU -> Sum Sales -> Sort -> Top 10
        $top10 = collect($raw)
            ->groupBy('sku')
            ->map(function($items) {
                return [
                    'name' => $items->first()['full_name'],
                    'sku' => $items->first()['sku'],
                    'y' => $items->sum('sales')
                ];
            })
            ->sortByDesc('y')
            ->take(10)
            ->values(); // Reset keys

        $this->chart_data = [
            'categories' => $top10->pluck('sku')->toArray(),
            'data'       => $top10->pluck('y')->map(fn($val) => round($val, 2))->toArray()
        ];

        $this->dispatch('update-chart', data: $this->chart_data);
    }
};
?>

<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">TOP 10 SKU SALES ({{ $year }})</h3>
        </div>
        <div class="card-body" wire:ignore>
            <div id="container-sku"></div>
        </div>
    </div>
</div>


@script
<script>
    let chart;

    const initChart = () => {
        chart = Highcharts.chart('container-sku', {
            chart: {
                type: 'bar'
            },
            title: {
                text: 'TOP SKU BASED ON SALES ' + $wire.year
            },
            xAxis: {
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
