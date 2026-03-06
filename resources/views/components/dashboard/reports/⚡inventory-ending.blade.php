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
    public $chart_categories = [];

    public function mount($year) {
        $this->year = $year;

        $this->chartUpdated();
    }

    public function updatedYear() {
        $this->chartUpdated();
    }

    public function chartUpdated() {
        $raw = $this->getYearlyInventoryData($this->year);
        $collection = collect($raw);

        $available_months = $collection->pluck('month')->unique()->sort()->values();
        $month_names = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $this->chart_categories = $available_months->map(fn($m) => $month_names[$m - 1] ?? $m)->toArray();

        $this->chart_data = $collection
            ->groupBy('full_name')
            ->filter(function ($items) {
                return $items->sum('total') > 0;
            })
            ->map(function ($items, $name) use ($available_months) {
                $uom = $items->first()['uom'] ?? 'Units';

                $data = $available_months->map(function ($month) use ($items) {
                    $match = $items->where('month', $month);
                    return $match->isNotEmpty() ? (float) $match->sum('total') : 0;
                })->all();

                return [
                    'name' => $name,
                    'data' => $data,
                    'custom' => ['uom' => $uom]
                ];
            })
            ->values()
            ->toArray();

        $this->dispatch('update-chart',
            series: $this->chart_data,
            categories: $this->chart_categories,
            year: $this->year
        );
    }

};
?>

<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">ENDING INVENTORY {{ $year }}</h3>
        </div>
        <div class="card-body" wire:ignore>
            <div id="container-ending"></div>
            <div class="mb-2">
                <button onclick="toggleAll(true)" class="btn btn-sm btn-outline-primary">Show All</button>
                <button onclick="toggleAll(false)" class="btn btn-sm btn-outline-secondary">Hide All</button>
            </div>
        </div>
    </div>
</div>

<style>
    .highcharts-tooltip span {
        max-height: 500px;
        overflow-y: auto !important;
        display: block;
        padding-right: 2px;
    }
</style>

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
        window.toggleAll = (show) => {
            chart.series.forEach(s => s.setVisible(show, false));
            chart.redraw();
        };

        let chart;

        const initChart = () => {
            chart = Highcharts.chart('container-ending', {
                chart: {
                    type: 'line',
                    height: 700,
                },
                title: {
                    text: 'Ending Inventory by Product',
                    align: 'left'
                },

                yAxis: {
                    title: {
                        text: 'Units in Stock'
                    }
                },

                xAxis: {
                    categories: $wire.chart_categories,
                    labels: {
                        overflow: 'justify'
                    }
                },
                tooltip: {
                    shared: true,
                    useHTML: true,
                    headerFormat: '<span style="font-size: 10px"><b>{point.key}</b></span><br/>',
                    pointFormat: '<span style="color:{point.color}">\u25CF</span> {series.name}: <b>{point.y}</b> {series.options.custom.uom}<br/>',
                    style: {
                        pointerEvents: 'auto'
                    }
                },

                legend: {
                    layout: 'horizontal',
                    align: 'center',
                    verticalAlign: 'bottom',
                    maxHeight: 100,
                    navigation: {
                        activeColor: '#3E576F',
                        animation: true,
                        arrowSize: 12,
                        inactiveColor: '#CCC',
                        style: { fontWeight: 'bold', color: '#333', fontSize: '12px' }
                    }
                },

                plotOptions: {
                    series: {
                        label: {
                            connectorAllowed: false
                        },
                        legendItemClick: function () {
                            return true;
                        }
                    }
                },

                series: $wire.chart_data,

            });
        };

        initChart();



        $wire.on('update-chart', (event) => {
            chart.xAxis[0].setCategories(event.categories);
            while(chart.series.length > 0) {
                chart.series[0].remove(false);
            }

            event.series.forEach(series => {
                chart.addSeries(series, false);
            });

            chart.setTitle({ text: 'ENDING INVENTORY ' + event.year });
            chart.redraw();
        });
    </script>
@endscript
