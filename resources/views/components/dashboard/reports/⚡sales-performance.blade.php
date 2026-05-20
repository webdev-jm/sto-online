<?php

use Livewire\Component;
use Livewire\Attributes\Reactive;
use App\Http\Traits\SalesDataAggregator;

new class extends Component
{
    use SalesDataAggregator;

    #[Reactive]
    public $year;
    #[Reactive]
    public ?int $account_id = null;
    public $chart_data    = [];
    public string $insight        = '';
    public bool   $loadingInsight = false;

    public function mount($year, $account_id = null): void {
        $this->year = $year;
        $this->account_id = $account_id;
        $this->chartUpdated();
    }

    public function updatedYear(): void
    {
        $this->chartUpdated();
        $this->generateInsight();
    }

    public function generateInsight(): void
    {
        $this->loadingInsight = true;
        try {
            $this->insight = app(\App\Services\OllamaService::class)->chat([
                ['role' => 'system', 'content' => 'You are a business data analyst for a Philippine FMCG distributor. Given chart data, respond with exactly one concise insight sentence. No markdown, no bullet points, no labels.'],
                ['role' => 'user',   'content' => $this->buildInsightSummary()],
            ]);
        } catch (\App\Exceptions\AiUnavailableException) {
        }
        $this->loadingInsight = false;
    }

    private function buildInsightSummary(): string
    {
        $currSeries = collect($this->chart_data['data'] ?? [])->firstWhere('name', $this->year)['data'] ?? [];
        $prevSeries = collect($this->chart_data['data'] ?? [])->firstWhere('name', $this->year - 1)['data'] ?? [];
        $currTotal  = collect($currSeries)->sum('y');
        $prevTotal  = collect($prevSeries)->sum('y');
        $bestMonth  = collect($currSeries)->sortByDesc('y')->first();
        return "Monthly sales performance {$this->year} vs " . ($this->year - 1) . ". "
            . "Current year total: ₱" . number_format($currTotal, 2) . ", "
            . "Previous year total: ₱" . number_format($prevTotal, 2) . ". "
            . ($bestMonth ? "Best month this year: {$bestMonth['name']} at ₱" . number_format($bestMonth['y'], 2) . "." : '');
    }

    public function chartUpdated(): void
    {
        $raw      = $this->getSalesData($this->year, $this->account_id)->all();
        $prev_raw = $this->getSalesData($this->year - 1, $this->account_id)->all();

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

                $currPoint = [
                    'name' => $monthLabel,
                    'y'    => round($monthlySums[$m] ?? 0, 2),
                ];
                $prevPoint = [
                    'name' => $monthLabel,
                    'y'    => round($prevMonthlySums[$m] ?? 0, 2),
                ];

                if (!$this->account_id) {
                    $currPoint['drilldown'] = $currDrillId;
                    $prevPoint['drilldown'] = $prevDrillId;

                    $drilldown[] = [
                        'id'   => $currDrillId,
                        'name' => "{$monthLabel} {$this->year}",
                        'type' => 'column',
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
                        'type' => 'column',
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

                $currData[] = $currPoint;
                $prevData[] = $prevPoint;
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

<div wire:init="generateInsight">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">MONTHLY SALES PERPORMANCE {{ $this->year }}</h3>
        </div>
        <div class="chart-sk">
            <div class="chart-sk-shimmer"></div>
        </div>

        <div class="card-body" wire:ignore>
            <div id="container-performance"></div>
        </div>
        <div class="card-footer text-xs text-muted">
            @if($loadingInsight)
                <i class="fa fa-spinner fa-spin fa-sm mr-1"></i> Generating insight...
            @else
                {{ $insight }}
            @endif
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
                title: { text: 'Sales' }
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

                        this.yAxis[0].setTitle({ text: 'Sales' }, false);
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
                title: { text: 'Sales' }
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
</script>
@endscript
