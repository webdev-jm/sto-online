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

        $drilldown = [];

        $chart_data = collect($raw)
            ->groupBy('brand')
            ->map(function ($items, $brandName) use (&$drilldown) {
                $drillId = 'brand_' . md5($brandName);

                // Build SKU drilldown for this brand
                $drilldown[] = [
                    'id'   => $drillId,
                    'name' => $brandName ?: 'Other',
                    'type' => 'pie',
                    'data' => $items->groupBy('sku')
                                ->map(fn($i, $sku) => [
                                    'full_name' => $i->first()['full_name'] ?? '',
                                    'name' => $sku ?: 'Unknown SKU',
                                    'y'    => round($i->sum('sales'), 2),
                                ])
                                ->sortByDesc('y')
                                ->values()
                                ->toArray(),
                ];

                return [
                    'name'      => $brandName ?: 'Other',
                    'full_name' => '',
                    'y'         => round($items->sum('sales'), 2),
                    'drilldown' => $drillId,
                ];
            })
            ->sortByDesc('y')
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
            <h3 class="card-title">SALES BY BRAND</h3>
        </div>
        <div class="card-body" wire:ignore>
            <div id="container-brands"></div>
        </div>
    </div>
</div>

@script
    <script>
        let chart;

        const buildConfig = (data) => ({
            credits: { enabled: false },
            chart: {
                type: 'pie',
                events: {
                    drillup: function () {
                        this.setTitle({ text: null });
                    },
                    drilldown: function (e) {
                        this.setTitle({ text: `${e.point.name} — by SKU` });
                    }
                }
            },
            title: { text: null },
            legend: { enabled: false },
            accessibility: { announceNewData: { enabled: true } },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        formatter: function () {
                            const val = this.y;
                            let abbreviated;
                            if (val >= 1_000_000_000)      abbreviated = Highcharts.numberFormat(val / 1_000_000_000, 1) + 'B';
                            else if (val >= 1_000_000)     abbreviated = Highcharts.numberFormat(val / 1_000_000, 1) + 'M';
                            else if (val >= 1_000)         abbreviated = Highcharts.numberFormat(val / 1_000, 1) + 'K';
                            else                           abbreviated = Highcharts.numberFormat(val, 2);
                            return `<b>${this.point.name}</b><br>₱ ${abbreviated} (${Highcharts.numberFormat(this.percentage, 1)}%)`;
                        }
                    },
                    showInLegend: true
                }
            },
            tooltip: {
                formatter: function () {
                    const val = this.y;
                    const full = Highcharts.numberFormat(val, 2);
                    const label = this.point.full_name || this.point.name;
                    return `<span style="font-size:11px">${this.series.name}</span><br>
                            <b>${label}</b><br>
                            ₱ <b>${full}</b> (${Highcharts.numberFormat(this.percentage, 1)}%)`;
                }
            },
            series: [{
                name: 'Sales by Brand',
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
            chart = Highcharts.chart('container-brands', buildConfig($wire.chart_data));
        };

        initChart();

        $wire.on('update-chart', (event) => {
            chart.destroy();
            chart = Highcharts.chart('container-brands', buildConfig(event.data));
        });
    </script>
@endscript
