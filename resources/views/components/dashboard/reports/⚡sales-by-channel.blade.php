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
        $this->insight = app(\App\Services\OllamaService::class)->chat([
            ['role' => 'system', 'content' => 'You are a business data analyst for a Philippine FMCG distributor. Given chart data, respond with exactly one concise insight sentence. No markdown, no bullet points, no labels.'],
            ['role' => 'user',   'content' => $this->buildInsightSummary()],
        ]);
        $this->loadingInsight = false;
    }

    private function buildInsightSummary(): string
    {
        if (empty($this->chart_data['data'])) {
            return "No channel sales data available for {$this->year}.";
        }
        $channels = collect($this->chart_data['data'])->sortByDesc('y')
            ->map(fn($d) => "{$d['name']}: ₱" . number_format($d['y'], 2))->implode(', ');
        return "Sales by channel for {$this->year}: {$channels}.";
    }

    public function chartUpdated(): void
    {
        $collection = $this->getSalesData($this->year, $this->account_id);

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

                $point = [
                    'name' => $first['channel_code'],
                    'y'    => (float) $items->sum('sales'),
                ];

                if (!$this->account_id) {
                    $point['drilldown'] = $drillId;

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
                }

                return $point;
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

<div wire:init="generateInsight">
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


