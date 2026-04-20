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
    public $account_id;

    public $chart_data = [];

    public function mount($year, $account_id) {
        $this->year = $year;
        $this->account_id = $account_id;
        $this->chartUpdated();
    }

    public function updatedYear() {
        $this->chartUpdated();
    }

    public function updatedAccountId() {
        $this->chartUpdated();
    }

    public function chartUpdated() {
        $raw = $this->getYearlySalesData($this->year);

        $collection = collect($raw)
            // Normalize casing before processing
            ->map(function ($item) {
                // Convert to a standard format (Upper Case First or All Upper)
                $item['province'] = isset($item['province']) ? mb_strtoupper($item['province']) : null;
                $item['city'] = isset($item['city']) ? mb_strtoupper($item['city']) : null;
                return $item;
            })
            ->when($this->account_id, fn($col) => $col->where('account_id', $this->account_id));

        $drilldown = [];

        $chart_data = $collection
            ->groupBy('province')
            ->map(function ($items, $province) use (&$drilldown) {
                // Use the normalized province for the ID
                $provinceName = $province ?: 'UNKNOWN PROVINCE';
                $provinceDrillId = 'province_' . md5($provinceName);

                $drilldown[] = [
                    'id'   => $provinceDrillId,
                    'name' => $provinceName,
                    'type' => 'column',
                    'data' => $items->groupBy('city')
                                ->map(fn($cityItems, $city) => [
                                    'name' => $city ?: 'UNKNOWN CITY',
                                    'y'    => round($cityItems->sum('sales'), 2),
                                ])
                                ->sortByDesc('y')
                                ->values()
                                ->toArray(),
                ];

                return [
                    'name'      => $provinceName,
                    'y'         => round($items->sum('sales'), 2),
                    'drilldown' => $provinceDrillId,
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
            <h3 class="card-title">SALES BY ADDRESS {{ $this->year }}</h3>
        </div>
        <div class="card-body" wire:ignore>
            <div id="container-sales-by-address" style="height: 500px;"></div>
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
                    this.xAxis[0].setTitle({ text: 'Province' }, false);
                    this.redraw();
                },
                drilldown: function (e) {
                    this.xAxis[0].setTitle({ text: 'City' }, false);
                    this.redraw();
                }
            }
        },
        legend: { enabled: false },
        title: { text: null },
        accessibility: { announceNewData: { enabled: true } },
        xAxis: {
            type: 'category',
            crosshair: true,
            title: { text: 'Province' }
        },
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
            name: 'Sales by Province',
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
        chart = Highcharts.chart('container-sales-by-address', buildConfig($wire.chart_data));
    };

    initChart();

    $wire.on('update-chart', (event) => {
        chart.destroy();
        chart = Highcharts.chart('container-sales-by-address', buildConfig(event.data));
    });
</script>
@endscript
