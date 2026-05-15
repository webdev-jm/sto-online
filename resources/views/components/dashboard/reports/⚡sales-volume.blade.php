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
        $this->chart_data['categories'] = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
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
        $categories = $this->chart_data['categories'] ?? [];
        if (empty($currSeries)) {
            return "No sales volume data available for {$this->year}.";
        }
        $peak     = collect($currSeries)->sortByDesc('y')->first();
        $peakIdx  = array_search($peak, $currSeries);
        $peakMonth = $categories[$peakIdx] ?? 'N/A';
        $total    = collect($currSeries)->sum('y');
        return "Monthly sales volume (pcs) for {$this->year}. Total: " . number_format($total, 0)
            . " pcs. Peak month: {$peakMonth} with " . number_format($peak['y'], 0) . " pcs.";
    }

    public function chartUpdated(): void
    {
        $raw      = $this->getSalesData($this->year, $this->account_id)->all();
        $prev_raw = $this->getSalesData($this->year - 1, $this->account_id)->all();

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
                $currPoint = ['y' => round($monthlyQty[$m] ?? 0, 2)];
                $prevPoint = ['y' => round($prevMonthlyQty[$m] ?? 0, 2)];

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

                $dataSeries[]     = $currPoint;
                $prevDataSeries[] = $prevPoint;
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

<div wire:init="generateInsight">
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

    const buildConfig = (data) => ({
        credits: { enabled: false },
        chart: {
            type: 'line',
            events: {
                drillup: function () {
                    this.xAxis[0].update({ categories: data.categories }, false);
                    this.setTitle({ text: null });
                },
                drilldown: function (e) {
                    const accountNames = (e.seriesOptions.data ?? []).map(p => p.name);
                    this.xAxis[0].update({ categories: accountNames }, false);
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
