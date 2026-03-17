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
        $collection = collect($raw);

        $total_sales = $collection->sum('sales');

        if ($total_sales <= 0) {
            $this->chart_data = [];
            return;
        }

        $this->chart_data = $collection->groupBy('channel_code')
            ->map(function ($items) use($total_sales) {
                $first = $items->first();
                $sales = $items->sum('sales');

                $percent = round(($sales / $total_sales) * 100, 2);

                return [
                    'name' => '['.$first['channel_code'].'] '.$first['channel_name'],
                    'y' => (float) $sales
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
            <h3 class="card-title">SALES BY CHANNEL {{ $year }}</h3>
        </div>
        <div class="card-body p-0" wire:ignore>
            <div id="container-channel"></div>
        </div>
    </div>
</div>

@script
<script>
    let chart;

    const initChart = () => {
        chart = Highcharts.chart('container-channel', {
            chart: {
                type: 'column'
            },
            title: {
                text: 'SALES BY CHANNEL'
            },
            tooltip: {
                headerFormat: '',
                pointFormat:
                    '<span style="color:{point.color}">\u25cf</span> ' +
                    '{point.name}: <b>{point.y:,.2f}</b>'
            },
            accessibility: {
                point: {
                    valueSuffix: '%'
                }
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    borderWidth: 2,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        format: '<b>{point.name}</b><br>{point.percentage:.1f}%',
                        distance: 20
                    }
                }
            },
            xAxis: {
                type: 'category'
            },
            yAxis: {
                title: {
                    text: 'Total percent market share'
                }

            },
            series: [{
                enableMouseTracking: true,
                colorByPoint: true,
                data: $wire.chart_data
            }]
        });
    }

    initChart();

    $wire.on('update-chart', (event) => {
        chart.series[0].setData(event.data);
        chart.setTitle({text: 'SALES BY CHANNEL ' + $wire.year});
        chart.redraw();
    });

</script>
@endscript


