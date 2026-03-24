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
        $raw      = $this->getYearlySalesData($this->year);
        $prev_raw = $this->getYearlySalesData($this->year - 1);

        $monthlySums     = collect($raw)->groupBy('month')->map(fn($i) => $i->sum('sales'));
        $prevMonthlySums = collect($prev_raw)->groupBy('month')->map(fn($i) => $i->sum('sales'));

        $currData   = [];
        $prevData   = [];
        $categories = [];
        $drilldown  = [];

        for ($m = 1; $m <= 12; $m++) {
            if (!empty($monthlySums[$m])) {
                $monthLabel = \DateTime::createFromFormat('!m', $m)->format('M');
                $categories[] = $monthLabel;

                $currDrillId = "curr_{$m}";
                $prevDrillId = "prev_{$m}";

                $currData[] = [
                    'name'      => $monthLabel,
                    'y'         => round($monthlySums[$m] ?? 0, 2),
                    'drilldown' => $currDrillId,
                ];
                $prevData[] = [
                    'name'      => $monthLabel,
                    'y'         => round($prevMonthlySums[$m] ?? 0, 2),
                    'drilldown' => $prevDrillId,
                ];

                $drilldown[] = [
                    'id'   => $currDrillId,
                    'name' => "{$monthLabel} {$this->year}",
                    'type' => 'pie',          // ← change from 'column'
                    'data' => collect($raw)
                                ->where('month', $m)
                                ->groupBy('account_name')
                                ->map(fn($i, $k) => [
                                    'name' => $k,
                                    'y'    => round($i->sum('sales'), 2),
                                ])
                                ->values()
                                ->toArray(),
                ];

                $drilldown[] = [
                    'id'   => $prevDrillId,
                    'name' => "{$monthLabel} " . ($this->year - 1),
                    'type' => 'pie',          // ← change from 'column'
                    'data' => collect($prev_raw)
                                ->where('month', $m)
                                ->groupBy('account_name')
                                ->map(fn($i, $k) => [
                                    'name' => $k,
                                    'y'    => round($i->sum('sales'), 2),
                                ])
                                ->values()
                                ->toArray(),
                ];
            }
        }

        $this->chart_data = [
            'categories' => $categories,
            'data'       => [
                ['name' => $this->year - 1, 'data' => $prevData],
                ['name' => $this->year,     'data' => $currData],
            ],
            'drilldown'  => $drilldown,
        ];

        $this->dispatch('update-chart', data: $this->chart_data);
    }
};
?>

<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">MONTHLY SALES PERPORMANCE {{ $this->year }}</h3>
        </div>
        <div class="card-body" wire:ignore>
            <div id="container-performance"></div>
        </div>
    </div>
</div>

@script
<script>
    let chart;

    const initChart = () => {
        chart = Highcharts.chart('container-performance', {
            credits: { enabled: false },
            chart: {
                type: 'column',
                events: {
                    drillup: function () {
                        const categories = this.userOptions.xAxis.categories;
                        this.xAxis[0].setCategories(categories, false);
                        this.redraw();
                    }
                }
            },
            title: { text: null },
            accessibility: { announceNewData: { enabled: true } },
            xAxis: {
                categories: $wire.chart_data.categories,
                crosshair: true,
            },
            yAxis: {
                title: { text: 'Total percent market share' }
            },
            legend: { enabled: false },
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
                },
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        formatter: function () {
                            const val = this.y;
                            let abbreviated;
                            if (val >= 1_000_000_000) abbreviated = Highcharts.numberFormat(val / 1_000_000_000, 1) + 'B';
                            else if (val >= 1_000_000) abbreviated = Highcharts.numberFormat(val / 1_000_000, 1) + 'M';
                            else if (val >= 1_000)     abbreviated = Highcharts.numberFormat(val / 1_000, 1) + 'K';
                            else                       abbreviated = Highcharts.numberFormat(val, 2);
                            return `<b>${this.point.name}</b>: ₱ ${abbreviated} (${Highcharts.numberFormat(this.percentage, 1)}%)`;
                        }
                    },
                    showInLegend: true
                }
            },
            tooltip: {
                formatter: function () {
                    if (this.series.type === 'pie') {
                        // Drilldown tooltip
                        return `<span style="font-size:11px">${this.series.name}</span><br>
                                <b>${this.point.name}</b><br>
                                ₱ <b>${Highcharts.numberFormat(this.y, 2)}</b>
                                (${Highcharts.numberFormat(this.percentage, 1)}%)`;
                    }
                    // Top level column tooltip
                    return `<span style="font-size:11px">${this.series.name}</span><br>
                            <b>${this.point.name}</b><br>
                            ₱ <b>${Highcharts.numberFormat(this.y, 2)}</b>`;
                }
            },
            series: $wire.chart_data.data,
            drilldown: {
                series: $wire.chart_data.drilldown,
                activeDataLabelStyle: {
                    textDecoration: 'none',
                    color: 'inherit'
                }
            }
        });
    }

    initChart();

    $wire.on('update-chart', (event) => {
        chart.destroy();
        chart = Highcharts.chart('container-performance', {
            credits: { enabled: false },
            chart: {
                type: 'column',
                events: {
                    drillup: function () {
                        const categories = event.data.categories;
                        this.xAxis[0].setCategories(categories, false);
                        this.redraw();
                    }
                }
            },
            title: { text: null },
            accessibility: { announceNewData: { enabled: true } },
            xAxis: {
                categories: event.data.categories,
                crosshair: true,
            },
            yAxis: {
                title: { text: 'Total percent market share' }
            },
            legend: { enabled: false },
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
                },
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        formatter: function () {
                            const val = this.y;
                            let abbreviated;
                            if (val >= 1_000_000_000) abbreviated = Highcharts.numberFormat(val / 1_000_000_000, 1) + 'B';
                            else if (val >= 1_000_000) abbreviated = Highcharts.numberFormat(val / 1_000_000, 1) + 'M';
                            else if (val >= 1_000)     abbreviated = Highcharts.numberFormat(val / 1_000, 1) + 'K';
                            else                       abbreviated = Highcharts.numberFormat(val, 2);
                            return `<b>${this.point.name}</b>: ₱ ${abbreviated} (${Highcharts.numberFormat(this.percentage, 1)}%)`;
                        }
                    },
                    showInLegend: true
                }
            },
            tooltip: {
                formatter: function () {
                    if (this.series.type === 'pie') {
                        // Drilldown tooltip
                        return `<span style="font-size:11px">${this.series.name}</span><br>
                                <b>${this.point.name}</b><br>
                                ₱ <b>${Highcharts.numberFormat(this.y, 2)}</b>
                                (${Highcharts.numberFormat(this.percentage, 1)}%)`;
                    }
                    // Top level column tooltip
                    return `<span style="font-size:11px">${this.series.name}</span><br>
                            <b>${this.point.name}</b><br>
                            ₱ <b>${Highcharts.numberFormat(this.y, 2)}</b>`;
                }
            },
            series: event.data.data,
            drilldown: {
                series: event.data.drilldown,
                activeDataLabelStyle: {
                    textDecoration: 'none',
                    color: 'inherit'
                }
            }
        });
    });

    console.log(typeof Highcharts.Chart.prototype.addSeriesAsDrilldown);
    console.log(Highcharts.version);
</script>
@endscript
