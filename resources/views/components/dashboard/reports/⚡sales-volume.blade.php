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
        $this->chart_data['categories'] = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $this->chartUpdated();
    }

    public function updatedYear() {
        $this->chartUpdated();
    }

    public function chartUpdated() {
        $raw = $this->getYearlySalesData($this->year);
        $prev_raw = $this->getYearlySalesData($this->year - 1);

        $monthlyQty = collect($raw)
            ->groupBy('month')->map(function($items) {
                return $items->sum('qty_pcs');
            });

        $prevMonthlyQty = collect($prev_raw)
            ->groupBy('month')
            ->map(function($items) {
                return $items->sum('qty_pcs');
            });

        $dataSeries = [];
        $prevDataSeries = [];
        for ($m = 1; $m <= 12; $m++) {
            if(!empty($monthlyQty[$m])) {
                $dataSeries[] = round($monthlyQty[$m] ?? 0, 2);
                $prevDataSeries[] = round($prevMonthlyQty[$m] ?? 0, 2);
            }
        }

        $this->chart_data['data'] = [
            [
                'name' => $this->year - 1,
                'data' => $prevDataSeries
            ],
            [
                'name' => $this->year,
                'data' => $dataSeries
            ]
        ];
        $this->dispatch('update-chart', data: $this->chart_data);
    }
};
?>

<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">MONTHLY SALES VOLUME</h3>
        </div>
        <div class="card-body" wire:ignore>
            <div id="container-volume"></div>
        </div>
    </div>
</div>


@script
<script>
    let chart;

    const initChart = () => {
        chart = Highcharts.chart('container-volume', {
            chart: {
                type: 'line'
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
            plotOptions: {
                line: {
                    dataLabels: {
                        enabled: true
                    },
                    enableMouseTracking: false
                }
            },
            series: $wire.chart_data['data']
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
