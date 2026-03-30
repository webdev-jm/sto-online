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

    public function updatedYear() {
        $this->chartUpdated();
    }

    public function chartUpdated() {
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
                                        'name' => $sku ?: 'Unknown SKU',
                                        'y'    => round(collect($skuItems)->sum('total_inventory'), 2),
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

<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">INVENTORY AGING</h3>
        </div>
        <div class="card-body" wire:ignore>
            <div id="container-aging"></div>
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
                return `<b>${this.point.name}</b><br>Quantity: <b>${val} PCS</b>`;
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
