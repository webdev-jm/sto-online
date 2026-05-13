<?php

use Livewire\Component;
use Livewire\Attributes\Reactive;
use App\Http\Traits\SalesDataAggregator;

new class extends Component
{
    use SalesDataAggregator;

    #[Reactive]
    public $year;
    public $chart_data    = [];
    public string $insight        = '';
    public bool   $loadingInsight = false;

    const EXPIRY_BUCKETS = [
        '1–3 Months'  => [1,  3],
        '4–6 Months'  => [4,  6],
        '7–12 Months' => [7,  12],
        '18+ Months'  => [13, null],
    ];

    public function mount($year) {
        $this->year = $year;
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
            return "No inventory aging data available for {$this->year}.";
        }
        $buckets = collect($this->chart_data['data'])
            ->map(fn($d) => "{$d['name']}: " . number_format($d['y'], 0) . " pcs")->implode(', ');
        $highest = collect($this->chart_data['data'])->sortByDesc('y')->first();
        return "Inventory aging distribution for {$this->year}: {$buckets}. "
            . "Largest bucket: {$highest['name']}.";
    }

    public function chartUpdated(): void
    {
        $raw = $this->getYearlyInventoryAgingData($this->year);

        $bucketGroups = array_map(fn() => collect(), array_flip(array_keys(self::EXPIRY_BUCKETS)));

        foreach ($raw as $item) {
            $remainingDays   = (int) $item['remaining_days'];
            $remainingMonths = $remainingDays / 30.44;
            $bucket = $this->resolveBucket($remainingMonths);
            if ($bucket === null) continue;
            $bucketGroups[$bucket]->push($item);
        }

        $drilldown  = [];
        $chart_data = [];

        foreach ($bucketGroups as $bucketLabel => $items) {
            $bucketDrillId = 'bucket_' . md5($bucketLabel);

            $chart_data[] = [
                'name'      => $bucketLabel,
                'y'         => round($items->sum('total_inventory'), 2),
                'drilldown' => $bucketDrillId,
            ];

            // Level 1 drilldown: per account
            $accountData = $items->groupBy('short_name')
                ->map(function ($accountItems, $accountName) use ($bucketLabel, &$drilldown) {
                    $accountDrillId = 'account_' . md5($bucketLabel . $accountName);

                    // Level 2 drilldown: per SKU under this account in this bucket
                    $drilldown[] = [
                        'id'   => $accountDrillId,
                        'name' => $accountName ?: 'Unknown',
                        'type' => 'column',
                        'data' => collect($accountItems)
                                    ->groupBy('stock_code')
                                    ->map(fn($skuItems, $sku) => [
                                        'name'        => $sku ?: 'Unknown SKU',
                                        'y'           => round(collect($skuItems)->sum('total_inventory'), 2),
                                        'description' => trim((collect($skuItems)->first()['name'] ?? '') . ' ' . (collect($skuItems)->first()['size'] ?? '')),
                                    ])
                                    ->sortByDesc('y')
                                    ->values()
                                    ->toArray(),
                    ];

                    return [
                        'name'      => $accountName ?: 'Unknown',
                        'y'         => round(collect($accountItems)->sum('total_inventory'), 2),
                        'drilldown' => $accountDrillId,
                    ];
                })
                ->sortByDesc('y')
                ->values()
                ->toArray();

            $drilldown[] = [
                'id'   => $bucketDrillId,
                'name' => $bucketLabel,
                'type' => 'column',
                'data' => $accountData,
            ];
        }

        $this->chart_data = [
            'data'      => $chart_data,
            'drilldown' => $drilldown,
        ];

        $this->dispatch('update-chart', data: $this->chart_data, year: $this->year);
    }

    private function resolveBucket(float $months): ?string
    {
        foreach (self::EXPIRY_BUCKETS as $label => [$min, $max]) {
            if ($months >= $min && ($max === null || $months <= $max)) {
                return $label;
            }
        }
        return null;
    }
};
?>

<div wire:init="generateInsight">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">INVENTORY AGING</h3>
        </div>
        <div class="chart-sk">
            <div class="chart-sk-shimmer"></div>
        </div>

        <div class="card-body" wire:ignore>
            <div id="container-aging"></div>
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

    const BUCKET_LABELS = ['1–3 Months', '3–6 Months', '6–12 Months', '18+ Months'];

    let chart;

    const buildConfig = (data) => ({
        credits: { enabled: false },
        chart: {
            type: 'column',
            events: {
                drillup: function (e) {
                    const depth = this.drilldownLevels?.length ?? 0;
                    if (depth === 1) {
                        // Coming back to top level
                        this.yAxis[0].setTitle({ text: 'Total Quantity on Hand' }, false);
                        this.xAxis[0].setTitle({ text: 'Months Before Expiration' }, false);
                    } else {
                        // Coming back to account level
                        this.yAxis[0].setTitle({ text: 'Total Quantity on Hand' }, false);
                        this.xAxis[0].setTitle({ text: 'Account' }, false);
                    }
                    this.redraw();
                },
                drilldown: function (e) {
                    const depth = this.drilldownLevels?.length ?? 0;
                    if (depth === 0) {
                        // Drilled into account level
                        this.xAxis[0].setTitle({ text: 'Account' }, false);
                    } else {
                        // Drilled into SKU level
                        this.xAxis[0].setTitle({ text: 'SKU' }, false);
                    }
                    this.yAxis[0].setTitle({ text: 'Quantity on Hand' }, false);
                    this.redraw();
                }
            }
        },
        title: { text: null },
        xAxis: {
            type: 'category',
            title: { text: 'Months Before Expiration' },
            crosshair: true
        },
        yAxis: {
            min: 0,
            title: { text: 'Total Quantity on Hand' }
        },
        legend: { enabled: false },
        tooltip: {
            formatter: function () {
                const val = Highcharts.numberFormat(this.y, 2);
                let tip = `<b>${this.point.name}</b>`;
                if (this.point.description) {
                    tip += `<br><span style="font-size:0.9em;color:#666">${this.point.description}</span>`;
                }
                tip += `<br>Quantity: <b>${val} PCS</b>`;
                return tip;
            }
        },
        plotOptions: {
            column: {
                colorByPoint: true,
                borderWidth: 0,
                dataLabels: {
                    enabled: true,
                    formatter: function () {
                        const val = this.y;
                        if (val >= 1_000_000) return Highcharts.numberFormat(val / 1_000_000, 1) + 'M';
                        if (val >= 1_000)     return Highcharts.numberFormat(val / 1_000, 1) + 'K';
                        return Highcharts.numberFormat(val, 2);
                    }
                }
            }
        },
        series: [{
            name: 'Quantity',
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
        chart = Highcharts.chart('container-aging', buildConfig($wire.chart_data));
    };

    initChart();

    $wire.on('update-chart', (event) => {
        chart.destroy();
        chart = Highcharts.chart('container-aging', buildConfig(event.data));
    });
</script>
@endscript
