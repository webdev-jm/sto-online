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
        $this->chart_data['categories'] = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $this->chartUpdated();
    }

    public function updatedYear() {
        $this->chartUpdated();
    }

    public function chartUpdated() {
        $raw = $this->getYearlySalesData($this->year);

        // Sum Quantity (PCS) per month
        $monthlyQty = collect($raw)->groupBy('month')->map(function($items) {
            return $items->sum('qty_pcs');
        });

        // Fill 0s for missing months
        $dataSeries = [];
        for ($m = 1; $m <= 12; $m++) {
            $dataSeries[] = round($monthlyQty[$m] ?? 0, 2);
        }

        $this->chart_data['data'] = $dataSeries;
        $this->dispatch('update-chart', data: $this->chart_data);
    }
};
?>

<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">MONTHLY SALES VOLUME</h3>
            <div class="card-tools">
                <input type="number" class="form-control form-control-sm" wire:model.live="year">
            </div>
        </div>
        <div class="card-body" wire:ignore>
            <div id="container2"></div>
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
        chart = Highcharts.chart('container2', {
            chart: {
                type: 'column'
            },
            title: {
                text: 'MONTHLY SALES VOLUME ' + $wire.year
            },
            xAxis: {
                categories: $wire.chart_data['categories'],
                title: {
                    text: 'Months'
                }
            },
            yAxis: {
                min: 0,
                title: {
                    text: 'Total Quantity Sold (PCS)'
                }
            },
            series: [{
                name: 'Sales Volume',
                data: $wire.chart_data['data'] // Sample data
            }]
        });
    };

    initChart();

    $wire.on('update-chart', (event) => {
        chart.series[0].setData($wire.chart_data['data']);
        chart.xAxis[0].setCategories($wire.chart_data['categories']);
        chart.setTitle({text: 'MONTHLY SALES VOLUME ' + $wire.year});
    });

</script>
@endscript
