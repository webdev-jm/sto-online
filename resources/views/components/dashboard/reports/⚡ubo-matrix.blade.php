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
            ->groupBy('customer_code')
            ->filter(function($items) {
                return $items->get('customer_status') == 0;
            })
            ->map(function($items) {
                $first = $items->first();

                return [
                    'x' => $items->sum('sales'),
                    'y' => $items->sum('qty_pcs'),
                    'z' => 0.1,
                    'name' => $first['customer_name'],
                    'account' => $first['account_name'],
                    'channel_code' => $first['channel_code'],
                    'channel_name' => $first['channel_name'],
                ];
            })->values();
    }
};
?>

<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">UBO MATRIX ({{ $year }})</h3>
        </div>
        <div class="card-body" wire:ignore>
            <div id="container-ubo-matrix"></div>
        </div>
    </div>
</div>

@script
<script>
    let chart;

    const initChart = () => {
        chart = Highcharts.chart('container-ubo-matrix', {

            chart: {
                type: 'scatter',
                zooming: {
                    type: 'xy'
                }
            },

            legend: {
                enabled: false
            },

            title: {
                text: 'UBO MATRIX' + $wire.year
            },

            xAxis: {
                gridLineWidth: 1,
                title: {
                    text: 'Sales'
                },
                labels: {
                    format: '{value} php'
                },
            },

            yAxis: {
                startOnTick: false,
                endOnTick: false,
                title: {
                    text: 'Units Sold'
                },
                labels: {
                    format: '{value} pcs'
                },
                maxPadding: 0.2,
            },

            tooltip: {
                useHTML: true,
                headerFormat: '<table>',
                pointFormat: '<tr><th colspan="2"><h3>{point.account}</h3></th></tr>' +
                    '<tr><th>Sales (php):</th><td>{point.x:,.2f}</td></tr>' +
                    '<tr><th>Units Sold (pcs):</th><td>{point.y:,.2f}</td></tr>' +
                    '<tr><th>Store :</th><td>{point.name}</td></tr>' +
                    '<tr><th>Channel :</th><td>{point.channel_name}</td></tr>',
                footerFormat: '</table>',
                followPointer: true
            },

            series: [{
                data: $wire.chart_data,
            }]

        });
    }

    initChart();

    $wire.on('update-chart', (event) => {
        chart.series[0].setData($wire.chart_data);

    });
</script>
@endscript
