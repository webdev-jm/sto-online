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

    public function mount($year, $account_id): void
    {
        $this->year = $year;
        $this->account_id = $account_id;
        $this->chartUpdated();
    }

    public function updatedYear(): void
    {
        $this->chartUpdated();
    }

    public function updatedAccountId(): void
    {
        $this->chartUpdated();
    }

    public function chartUpdated(): void
    {
        $raw = $this->getYearlySalesData($this->year);

        $collection = collect($raw)
            ->map(function ($item) {
                $item['province'] = isset($item['province']) ? mb_strtoupper($item['province']) : null;
                $item['city']     = isset($item['city'])     ? mb_strtoupper($item['city'])     : null;
                return $item;
            });
            // ->when($this->account_id, fn($col) => $col->where('account_id', $this->account_id));

        $drilldownSeries = [];

        $mapData = $collection
            ->groupBy('province')
            ->map(function ($items, $province) use (&$drilldownSeries) {
                $provinceName = $province ?: 'UNKNOWN';
                $drillId      = 'province_' . md5($provinceName);

                $drilldownSeries[] = [
                    'id'   => $drillId,
                    'name' => $provinceName,
                    'type' => 'column',
                    'data' => $items->groupBy('city')
                        ->map(fn($cityItems, $city) => [
                            'name'     => $city ?: 'UNKNOWN',
                            'y'        => round($cityItems->sum('sales'), 2),
                            'accounts' => $cityItems->groupBy('account_name')
                                ->map(fn($ai, $an) => [
                                    'name'  => $an,
                                    'value' => round($ai->sum('sales'), 2),
                                ])
                                ->sortByDesc('value')
                                ->values()
                                ->toArray(),
                        ])
                        ->sortByDesc('y')
                        ->values()
                        ->toArray(),
                ];

                return [
                    'name'      => $provinceName,
                    'value'     => round($items->sum('sales'), 2),
                    'drilldown' => $drillId,
                    'accounts'  => $items->groupBy('account_name')
                        ->map(fn($ai, $an) => [
                            'name'  => $an,
                            'value' => round($ai->sum('sales'), 2),
                        ])
                        ->sortByDesc('value')
                        ->values()
                        ->toArray(),
                ];
            })
            ->values()
            ->toArray();

        $this->chart_data = [
            'data'      => $mapData,
            'drilldown' => $drilldownSeries,
        ];

        $this->dispatch('update-chart', data: $this->chart_data);
    }
};
?>

<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">SALES BY PROVINCE {{ $this->year }}</h3>
        </div>
        <div class="card-body" wire:ignore>
            <div id="container-sales-by-address" style="height: 600px;"></div>
        </div>
    </div>
</div>

@script
<script>
    let chart;
    let geoJson = null;
    let drilldownData = [];

    const formatValue = (val) => {
        if (val >= 1_000_000_000) return '₱ ' + Highcharts.numberFormat(val / 1_000_000_000, 1) + 'B';
        if (val >= 1_000_000)     return '₱ ' + Highcharts.numberFormat(val / 1_000_000, 1) + 'M';
        if (val >= 1_000)         return '₱ ' + Highcharts.numberFormat(val / 1_000, 1) + 'K';
        return '₱ ' + Highcharts.numberFormat(val, 2);
    };

    const buildMapData = (salesData) => {
        const lookup = new Map();
        geoJson.features.forEach(f => {
            const name = f.properties.name;
            if (name) {
                lookup.set(name.toUpperCase(), f.properties['hc-key']);
            }
        });

        return salesData.map(item => ({
            'hc-key':   lookup.get(item.name) ?? null,
            name:       item.name,
            value:      item.value,
            drilldown:  item.drilldown,
            accounts:   item.accounts ?? [],
        })).filter(item => item['hc-key'] !== null);
    };

    const tooltipFormatter = function () {
        const val = this.point.value ?? this.point.y ?? 0;
        if (!val) {
            return `<b>${this.point.name}</b><br>No data`;
        }
        let html = `<b>${this.point.name}</b><br>Total: ₱ <b>${Highcharts.numberFormat(val, 2)}</b>`;
        const accounts = this.point.accounts ?? [];
        if (accounts.length) {
            html += '<br><br>';
            accounts.forEach(a => {
                html += `${a.name}: ₱ <b>${Highcharts.numberFormat(a.value, 2)}</b><br>`;
            });
        }
        return html;
    };

    const buildConfig = (chartData) => {
        drilldownData = chartData.drilldown ?? [];

        return {
            credits: { enabled: false },
            chart: {
                map: geoJson,
            },
            title: { text: null },
            colorAxis: {
                min: 0,
                minColor: '#E6F3FF',
                maxColor: '#0574B4',
            },
            legend: {
                layout: 'vertical',
                align: 'left',
                verticalAlign: 'bottom',
            },
            mapNavigation: {
                enabled: true,
                buttonOptions: { verticalAlign: 'bottom' },
            },
            tooltip: {
                useHTML: true,
                formatter: tooltipFormatter,
            },
            series: [{
                name: 'Sales by Province',
                data: buildMapData(chartData.data ?? []),
                joinBy: 'hc-key',
                nullColor: '#F0F0F0',
                borderColor: '#A0A0A0',
                borderWidth: 0.5,
                cursor: 'pointer',
                states: {
                    hover: { color: '#F4A021' },
                },
                dataLabels: { enabled: false },
                point: {
                    events: {
                        click: function () {
                            const drillId = this.options.drilldown;
                            if (!drillId) return;
                            const series = drilldownData.find(s => s.id === drillId);
                            if (series) {
                                chart.addSeriesAsDrilldown(this, series);
                            }
                        }
                    }
                }
            }],
            drilldown: {
                activeDataLabelStyle: {
                    textDecoration: 'none',
                    fontStyle: 'italic',
                },
                breadcrumbs: {
                    position: { align: 'right' },
                },
            },
        };
    };

    const initChart = (data) => {
        chart = Highcharts.mapChart('container-sales-by-address', buildConfig(data));
    };

    fetch('{{ asset('vendor/highcharts/maps/ph-all.geo.json') }}')
        .then(r => r.json())
        .then(json => {
            geoJson = json;
            initChart($wire.chart_data);
        });

    $wire.on('update-chart', (event) => {
        if (!geoJson) return;
        if (chart) chart.destroy();
        initChart(event.data);
    });
</script>
@endscript
