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
        // 1. Get Cached, Unified Data
        $raw = $this->getYearlySalesData($this->year);
        $prev_raw = $this->getYearlySalesData($this->year - 1);

        // 2. Group by Month and Sum Sales
        // Creates array [1 => 1200, 2 => 1500...]
        $monthlySums = collect($raw)->groupBy('month')->map(function($items) {
            return $items->sum('sales');
        });
        $prevMonthlySums = collect($prev_raw)->groupBy('month')->map(function($items) {
            return $items->sum('sales');
        });

        // 3. Format for Highcharts (Ensure all 12 months exist)
        $currData = [];
        $prevData = [];
        $categories = [];
        for ($m = 1; $m <= 12; $m++) {
            if(!empty($monthlySums[$m])) {
                $categories[] = \DateTime::createFromFormat('!m', $m)->format('M');
                $currData[] = round($monthlySums[$m] ?? 0, 2);
                $prevData[] = round($prevMonthlySums[$m] ?? 0, 2);
            }
        }

        $this->chart_data = [
            'categories' => $categories,
            'data' => [
                [
                    'name' => $this->year - 1,
                    'data' => $prevData
                ],
                [
                    'name' => $this->year,
                    'data' => $currData
                ]
            ]
        ];

        $this->dispatch('update-chart', data: $this->chart_data);
    }
};
?>

<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">MONTHLY SALES PERPORMANCE</h3>
        </div>
        <div class="card-body p-0" wire:ignore>
            <div id="container-performance"></div>
        </div>
    </div>
</div>

@script
<script>
    let chart;

    const initChart = () => {
        chart = Highcharts.chart('container-performance', {
            chart: {
                type: 'column'
            },
            title: {
                text: 'MONTHLY SALES PERPORMANCE ' + $wire.year
            },
            subtitle: {
                text: 'Monthly Sales Performance Report'
            },
            accessibility: {
                announceNewData: {
                    enabled: true
                }
            },
            xAxis: {
                categories: $wire.chart_data.categories,
                crosshair: true,
                accessibility: {
                    description: 'YEAR'
                }
            },
            yAxis: {
                title: {
                    text: 'Total percent market share'
                }

            },
            legend: {
                enabled: false
            },
            plotOptions: {
                series: {
                    borderWidth: 0,
                    dataLabels: {
                        enabled: true,
                        format: '₱ {point.y:,.2f}'
                    }
                }
            },

            tooltip: {
                headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
                pointFormat: '<b>{point.y:,.2f}</b> total<br/>'
            },

            series: $wire.chart_data.data
        });
    }

    initChart();

    $wire.on('update-chart', (event) => {
        chart.series[0].setData(event.data.data, true);

        chart.xAxis[0].setCategories(event.data.categories);

        chart.setTitle({text: 'MONTHLY SALES PERPORMANCE ' + $wire.year});
    });
</script>
@endscript
