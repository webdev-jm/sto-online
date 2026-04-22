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

        $drilldown = [];

        $top10 = $collection
            ->groupBy('sku')
            ->map(function ($items) use (&$drilldown) {
                $sku       = $items->first()['sku'];
                $fullName  = $items->first()['full_name'];
                $drillId   = 'sku_' . md5($sku);

                $drilldown[] = [
                    'id'   => $drillId,
                    'name' => $fullName ?: $sku,
                    'type' => 'bar',
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
                    'name'      => $sku,
                    'full_name' => $fullName,
                    'y'         => round($items->sum('sales'), 2),
                    'drilldown' => $drillId,
                ];
            })
            ->sortByDesc('y')
            ->take(10)
            ->values();

        $this->chart_data = [
            'data'      => $top10->toArray(),
            'drilldown' => $drilldown,
        ];

        $this->dispatch('update-chart', data: $this->chart_data);
    }
};
?>

<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">TOP 10 SKU SALES ({{ $year }})</h3>
        </div>
        <div class="chart-sk">
            <div class="chart-sk-shimmer"></div>
        </div>

        <div class="card-body" wire:ignore>
            <div id="container-sku"></div>
        </div>
    </div>
</div>


@script
<script>
    let chart;

    const buildConfig = (data) => ({
        credits: { enabled: false },
        chart: {
            type: 'bar',
            events: {
                drillup: function () {
                    this.setTitle({ text: null });
                    this.xAxis[0].update({ title: { text: 'Product SKU' } }, false);
                    this.yAxis[0].update({ title: { text: 'Total Sales' } }, false);
                    this.redraw();
                },
                drilldown: function (e) {
                    this.setTitle({ text: `${e.point.full_name || e.point.name} — by Account` });
                    this.xAxis[0].update({ title: { text: 'Account' } }, false);
                    this.yAxis[0].update({ title: { text: 'Sales Amount' } }, false);
                    this.redraw();
                }
            }
        },
        legend: { enabled: false },
        title: { text: null },
        xAxis: {
            type: 'category',           // ← key fix: reads point.name at every level
            title: { text: 'Product SKU' }
        },
        yAxis: {
            min: 0,
            title: { text: 'Total Sales', align: 'high' }
        },
        plotOptions: {
            bar: {
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
                const label = this.point.full_name || this.point.name;
                return `<b>${label}</b><br>₱ <b>${Highcharts.numberFormat(val, 2)}</b>`;
            }
        },
        series: [{
            name: 'Sales Amount',
            colorByPoint: true,
            data: data.data       // no categories needed — names come from point.name
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
        chart = Highcharts.chart('container-sku', buildConfig($wire.chart_data));
    };

    initChart();

    $wire.on('update-chart', (event) => {
        chart.destroy();
        chart = Highcharts.chart('container-sku', buildConfig(event.data));
    });

</script>
@endscript
