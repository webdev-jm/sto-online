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
        $raw = $this->getYearlyInventoryAgingData($this->year);

        $this->chart_data = collect($raw)
            ->groupBy(function ($item) {
                return $item['stock_code'] . '|' . $item['expiry_date'] . '|' . $item['uom'];
            })
            ->map(function ($group) {
                $first = $group->first();

                $totalQty = $group->sum('total_inventory');

                return [
                    'x'            => (int) $this->computeRemainingDays($first['expiry_date']),
                    'y'            => (float) $totalQty,
                    'z'            => (float) $totalQty, // Usually for bubble size in charts
                    'name'         => $first['stock_code'],
                    'product_name' => $first['name'] . ' ' . $first['size'],
                    'uom'          => $first['uom'],
                    'expiry'       => $first['expiry_date']
                ];
            })
            ->values();

        $this->dispatch('update-chart', data: $this->chart_data);
    }
};
?>

<div>
    <div class="card data-loading:bg-dark">
        <div class="card-header">
            <h3 class="card-title">INVENTORY AGING</h3>
            <div class="card-tools">
                {{-- <input type="number" class="form-control form-control-sm" wire:model.live="year"> --}}
            </div>
        </div>
        <div class="card-body" wire:ignore>
            <div id="container-aging"></div>
        </div>
    </div>
</div>

@assets
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/maps/modules/map.js"></script>
    <script src="https://code.highcharts.com/modules/data.js"></script>
    <script src="https://code.highcharts.com/modules/drilldown.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/export-data.js"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>
@endassets

@script
    <script>
        let chart

        const initChart = () => {
            chart = Highcharts.chart('container-aging', {
                chart: { type: 'bubble', plotBorderWidth: 1, zooming: { type: 'xy' } },
                title: { text: 'Inventory Aging Risk Analysis' },
                xAxis: {
                    gridLineWidth: 1,
                    title: { text: 'Days Until Expiry' },
                    labels: { format: '{value} d' },
                    // Add a plotline to show the "Today" threshold
                    plotLines: [{
                        color: 'red',
                        dashStyle: 'solid',
                        width: 2,
                        value: 0,
                        label: { text: 'EXPIRED', style: { color: 'red' } },
                        zIndex: 5
                    }]
                },
                yAxis: {
                    title: { text: 'Quantity on Hand' },
                    labels: { format: '{value}' }
                },
                tooltip: {
                    useHTML: true,
                    headerFormat: '<table>',
                    pointFormat: '<tr><th colspan="2"><h3>{point.name}</h3></th></tr>' +
                        '<tr><th>Product:</th><td>{point.product_name}</td></tr>' +
                        '<tr><th>Expiry:</th><td>{point.expiry}</td></tr>' +
                        '<tr><th>Days Left:</th><td>{point.x}</td></tr>' +
                        '<tr><th>Stock:</th><td>{point.y} {point.uom}</td></tr>' +
                        '<tr><th>Value Risk:</th><td>{point.z}</td></tr>',
                    footerFormat: '</table>',
                    followPointer: true
                },
                plotOptions: {
                    series: {
                        dataLabels: { enabled: true, format: '{point.name}' }
                    }
                },
                series: [{ data: $wire.chart_data, colorByPoint: true }]
            });

        };

        initChart();

        $wire.on('update-chart', (event) => {
            chart.series[0].setData($wire.chart_data);
            chart.setTitle({text: 'INVENTORY AGING ' + $wire.year});
        });
    </script>
@endscript
