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
            ->groupBy('month')
            ->filter(function($items) {
                return $items->get('customer_status') == 0;
            })
            ->map(function($items) {
                $first = $items->first();

                $month = \DateTime::createFromFormat('!m', $first['month'])->format('M');
                $count = collect($items)->groupBy('customer_code')->count();

                return [
                    'name' => $month,
                    'y' => (int) $count,
                ];
            })
            ->values()
            ->toArray();

        $this->dispatch('update-chart', data: $this->chart_data);
    }
};
?>

<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">UNIQUE BUYING OUTLET (UBO) {{ $year }}</h3>
        </div>
        <div class="card-body" wire:ignore>
            <div id="container-ubo"></div>
        </div>
    </div>
</div>


@script
<script>
    let chart;

    const initChart = () => {
        chart = Highcharts.chart('container-ubo', {
            chart: {
                type: 'column'
            },
            title: {
                text: '>UNIQUE BUYING OUTLET (UBO) ' + $wire.year
            },
            accessibility: {
                announceNewData: {
                    enabled: true
                }
            },
            xAxis: {
                type: 'category'
            },
            yAxis: {
                title: {
                    text: 'Total UBO'
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
                        format: '{point.y}'
                    }
                }
            },

            tooltip: {
                headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
                pointFormat: '<span style="color:{point.color}">{point.name}</span>: ' +
                    '<b>{point.y}</b><br/>'
            },

            series: [
                {
                    name: 'UBO',
                    colorByPoint: true,
                    data: $wire.chart_data
                }
            ],
        });
    };


    initChart();

    $wire.on('update-chart', (event) => {
        chart.series[0].setData($wire.chart_data);
        chart.setTitle({text: 'UNIQUE BUYING OUTLET (UBO) ' + $wire.year});
    });

</script>
@endscript
