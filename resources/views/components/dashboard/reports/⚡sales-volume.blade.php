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
        $raw      = $this->getYearlySalesData($this->year);
        $prev_raw = $this->getYearlySalesData($this->year - 1);

        $monthlyQty     = collect($raw)->groupBy('month')->map(fn($i) => $i->sum('qty_pcs'));
        $prevMonthlyQty = collect($prev_raw)->groupBy('month')->map(fn($i) => $i->sum('qty_pcs'));

        $dataSeries     = [];
        $prevDataSeries = [];
        $drilldown      = [];

        for ($m = 1; $m <= 12; $m++) {
            $monthLabel   = \DateTime::createFromFormat('!m', $m)->format('M');
            $currDrillId  = "curr_{$m}";
            $prevDrillId  = "prev_{$m}";

            if (!empty($monthlyQty[$m])) {
                $dataSeries[] = [
                    'y'        => round($monthlyQty[$m] ?? 0, 2),
                    'drilldown' => $currDrillId,
                ];

                $prevDataSeries[] = [
                    'y'        => round($prevMonthlyQty[$m] ?? 0, 2),
                    'drilldown' => $prevDrillId,
                ];

                $drilldown[] = [
                    'id'   => $currDrillId,
                    'name' => "{$monthLabel} {$this->year}",
                    'type' => 'column',
                    'data' => collect($raw)
                                ->where('month', $m)
                                ->groupBy('account_name')
                                ->map(fn($i, $account) => [
                                    'name' => $account ?: 'Unknown',
                                    'y'    => round($i->sum('qty_pcs'), 2),
                                ])
                                ->sortByDesc('y')
                                ->values()
                                ->toArray(),
                ];

                $drilldown[] = [
                    'id'   => $prevDrillId,
                    'name' => "{$monthLabel} " . ($this->year - 1),
                    'type' => 'column',
                    'data' => collect($prev_raw)
                                ->where('month', $m)
                                ->groupBy('account_name')
                                ->map(fn($i, $account) => [
                                    'name' => $account ?: 'Unknown',
                                    'y'    => round($i->sum('qty_pcs'), 2),
                                ])
                                ->sortByDesc('y')
                                ->values()
                                ->toArray(),
                ];
            }
        }

        $this->chart_data['data'] = [
            ['name' => $this->year - 1, 'data' => $prevDataSeries],
            ['name' => $this->year,     'data' => $dataSeries],
        ];
        $this->chart_data['drilldown'] = $drilldown;

        $this->dispatch('update-chart', data: $this->chart_data);
    }
};
?>

<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">MONTHLY SALES VOLUME {{ $this->year }}</h3>
        </div>
        <div class="chart-sk">
            <div class="chart-sk-shimmer"></div>
        </div>

        <div class="card-body" wire:ignore>
            <div id="container-volume"></div>
        </div>
    </div>
</div>

@script
<script>
    let chart;

    const buildConfig = (data) => ({
        credits: { enabled: false },
        chart: {
            type: 'line',
            events: {
                drillup: function () {
                    this.setTitle({ text: null });
                },
                drilldown: function (e) {
                    this.setTitle({ text: `${e.point.series.name} — ${e.point.category} by Account` });
                }
            }
        },
        title: { text: null },
        xAxis: {
            categories: data.categories,
            crosshair: true,
            title: { text: 'Months' }
        },
        yAxis: {
            min: 0,
            title: { text: 'Total Quantity Sold (PCS)' }
        },
        plotOptions: {
            line: {
                dataLabels: {
                    enabled: true,
                    formatter: function () {
                        const val = this.y;
                        if (val >= 1_000_000) return Highcharts.numberFormat(val / 1_000_000, 1) + 'M';
                        if (val >= 1_000)     return Highcharts.numberFormat(val / 1_000, 1) + 'K';
                        return Highcharts.numberFormat(val, 0);
                    }
                },
                marker: { enabled: true }
            },
            column: {
                borderWidth: 0,
                dataLabels: {
                    enabled: true,
                    formatter: function () {
                        const val = this.y;
                        if (val >= 1_000_000) return Highcharts.numberFormat(val / 1_000_000, 1) + 'M';
                        if (val >= 1_000)     return Highcharts.numberFormat(val / 1_000, 1) + 'K';
                        return Highcharts.numberFormat(val, 0);
                    }
                }
            }
        },
        tooltip: {
            formatter: function () {
                const val = this.y;
                return `<span style="font-size:11px">${this.series.name}</span><br>
                        <b>${this.point.name ?? this.point.category}</b><br>
                        <b>${Highcharts.numberFormat(val, 0)} pcs</b>`;
            }
        },
        series: data.data,
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
        chart = Highcharts.chart('container-volume', buildConfig($wire.chart_data));
    };

    initChart();

    $wire.on('update-chart', (event) => {
        chart.destroy();
        chart = Highcharts.chart('container-volume', buildConfig(event.data));
    });

</script>
@endscript
