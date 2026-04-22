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

        $drilldown = [];

        $chart_data = $collection->groupBy('channel_code')
            ->map(function ($items) use (&$drilldown) {
                $first   = $items->first();
                $drillId = 'channel_' . md5($first['channel_code']);

                // Build per-account drilldown for this channel
                $drilldown[] = [
                    'id'   => $drillId,
                    'name' => $first['channel_code'],
                    'type' => 'column',
                    'data' => $items->groupBy('short_name')
                                ->map(fn($i, $account) => [
                                    'name' => $account ?: 'Unknown',
                                    'y'    => round($i->sum('sales'), 2),
                                ])
                                ->sortByDesc('y')
                                ->values()
                                ->toArray(),
                ];

                return [
                    'name'      => $first['channel_code'],
                    'y'         => (float) $items->sum('sales'),
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
            <h3 class="card-title">SALES BY CHANNEL {{ $year }}</h3>
        </div>
        <div class="chart-sk">
            <div class="chart-sk-shimmer"></div>
        </div>

        <div class="card-body" wire:ignore>
            <div id="container-channel"></div>
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
                },
                drilldown: function (e) {
                    this.setTitle({ text: `${e.point.name} — by Account` });
                }
            }
        },
        legend: { enabled: false },
        title: { text: null },
        accessibility: { announceNewData: { enabled: true } },
        xAxis: { type: 'category', crosshair: true },
        yAxis: {
            title: { text: 'Sales Amount' }
        },
        plotOptions: {
            series: {
                borderWidth: 0,
                dataLabels: {
                    enabled: true,
                    formatter: function () {
                        const val = this.y;
                        if (val >= 1_000_000_000) return '₱ ' + Highcharts.numberFormat(val / 1_000_000_000, 1) + 'B';
                        if (val >= 1_000_000)     return '₱ ' + Highcharts.numberFormat(val / 1_000_000, 1) + 'M';
                        if (val >= 1_000)         return '₱ ' + Highcharts.numberFormat(val / 1_000, 1) + 'K';
                        return '₱ ' + Highcharts.numberFormat(val, 2);
                    }
                }
            }
        },
        tooltip: {
            formatter: function () {
                const val = this.y;
                return `<span style="color:${this.point.color}">\u25cf</span>
                        <b>${this.point.name}</b><br>
                        ₱ <b>${Highcharts.numberFormat(val, 2)}</b>`;
            }
        },
        series: [{
            name: 'Sales by Channel',
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
        chart = Highcharts.chart('container-channel', buildConfig($wire.chart_data));
    };

    initChart();

    $wire.on('update-chart', (event) => {
        chart.destroy();
        chart = Highcharts.chart('container-channel', buildConfig(event.data));
    });

</script>
@endscript


