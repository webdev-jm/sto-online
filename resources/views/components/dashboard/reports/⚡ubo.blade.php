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
        $collection = collect($raw)->where('customer_status', 0);

        $drilldown = [];

        $chart_data = $collection
            ->groupBy('month')
            ->map(function ($items, $month) use (&$drilldown) {
                $monthLabel = \DateTime::createFromFormat('!m', $month)->format('M');
                $drillId    = "month_{$month}";

                // Per-account UBO count for this month
                $drilldown[] = [
                    'id'   => $drillId,
                    'name' => $monthLabel,
                    'type' => 'column',
                    'data' => collect($items)
                                ->groupBy('account_name')
                                ->map(fn($accountItems, $accountName) => [
                                    'name' => $accountName ?: 'Unknown',
                                    'y'    => collect($accountItems)->groupBy('customer_code')->count(),
                                ])
                                ->sortByDesc('y')
                                ->values()
                                ->toArray(),
                ];

                return [
                    'name'      => $monthLabel,
                    'y'         => collect($items)->groupBy('customer_code')->count(),
                    'drilldown' => $drillId,
                ];
            })
            ->values()
            ->toArray();

        $this->chart_data = [
            'data'      => $chart_data,
            'drilldown' => $drilldown,
        ];

        $this->dispatch('update-chart', data: $this->chart_data);
    }
};
?>

<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">UNIQUE BUYING OUTLET (UBO) {{ $year }}</h3>
        </div>
        <div class="chart-sk">
            <div class="chart-sk-shimmer"></div>
        </div>

        <div class="card-body" wire:ignore>
            <div id="container-ubo"></div>
        </div>
    </div>
</div>

@script
<script>
    let chart;

    const buildConfig = (data) => ({
        credits: { enabled: false },
        chart: {
            type: 'column',
            events: {
                drillup: function () {
                    this.setTitle({ text: null });
                    this.yAxis[0].setTitle({ text: 'Total UBO' }, false);
                    this.redraw();
                },
                drilldown: function (e) {
                    this.setTitle({ text: `${e.point.name} — UBO per Account` });
                    this.yAxis[0].setTitle({ text: 'UBO Count' }, false);
                    this.redraw();
                }
            }
        },
        title: { text: null },
        accessibility: { announceNewData: { enabled: true } },
        xAxis: { type: 'category' },
        yAxis: {
            title: { text: 'Total UBO' }
        },
        legend: { enabled: false },
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
            pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y} UBO</b><br/>'
        },
        series: [{
            name: 'UBO',
            colorByPoint: true,
            data: data.data
        }],
        drilldown: {
            breadcrumbs: {
                position: { align: 'right' }
            },
            activeDataLabelStyle: {
                textDecoration: 'none',
                color: 'inherit'
            },
            series: data.drilldown
        }
    });

    const initChart = () => {
        chart = Highcharts.chart('container-ubo', buildConfig($wire.chart_data));
    };

    initChart();

    $wire.on('update-chart', (event) => {
        chart.destroy();
        chart = Highcharts.chart('container-ubo', buildConfig(event.data));
    });

</script>
@endscript
