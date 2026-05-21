<?php

use Livewire\Component;
use Livewire\Attributes\Reactive;
use App\Http\Traits\SalesDataAggregator;
use App\Models\Province;
use Illuminate\Support\Facades\Cache;

new class extends Component
{
    use SalesDataAggregator;

    #[Reactive]
    public $year;
    #[Reactive]
    public $account_id;

    public $chart_data    = [];
    public string $insight        = '';
    public bool   $loadingInsight = false;

    public function mount($year, $account_id): void
    {
        $this->year = $year;
        $this->account_id = $account_id;
        $this->chartUpdated();
    }

    public function updatedYear(): void
    {
        $this->chartUpdated();
        $this->generateInsight();
    }

    public function updatedAccountId(): void
    {
        $this->chartUpdated();
        $this->generateInsight();
    }

    public function generateInsight(): void
    {
        $this->loadingInsight = true;
        try {
            $this->insight = app(\App\Services\OllamaService::class)->chat([
                ['role' => 'system', 'content' => 'You are a business data analyst for a Philippine FMCG distributor. Given geographic sales data, respond with exactly one concise insight sentence. No markdown, no bullet points, no labels.'],
                ['role' => 'user',   'content' => $this->buildInsightSummary()],
            ]);
        } catch (\App\Exceptions\AiUnavailableException) {
        }
        $this->loadingInsight = false;
    }

    private function buildInsightSummary(): string
    {
        if (empty($this->chart_data['data'])) {
            return "No geographic sales data available for {$this->year}.";
        }

        $provinces = collect($this->chart_data['data'])
            ->sortByDesc('value')
            ->take(5)
            ->map(fn($d) => "{$d['name']}: ₱" . number_format($d['value'], 2))
            ->implode(', ');

        return "Sales by province for {$this->year}: {$provinces}.";
    }

    public function chartUpdated(): void
    {
        $raw = $this->getYearlySalesData($this->year);

        $collection = collect($raw)
            ->map(function ($item) {
                $item['province'] = isset($item['province']) ? mb_strtoupper($item['province']) : null;
                $item['city']     = isset($item['city'])     ? mb_strtoupper($item['city'])     : null;
                return $item;
            })
            ->when($this->account_id, fn($col) => $col->where('account_id', $this->account_id));

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

<div wire:init="generateInsight">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">SALES BY PROVINCE {{ $this->year }}</h3>
        </div>
        <div class="chart-sk">
            <div class="chart-sk-shimmer"></div>
        </div>

        <div class="card-body" wire:ignore>
            <div id="container-sales-by-address"></div>
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

        // Aliases for province names that differ between our data and the geoJSON.
        // Keys are the UPPERCASE database values; values are the geoJSON hc-key.
        const aliases = {
            // Mindoro — geoJSON uses "Mindoro Oriental" / "Mindoro Occidental"
            'ORIENTAL MINDORO':           'ph-mr',
            'EAST MINDORO':               'ph-mr',
            'OCCIDENTAL MINDORO':         'ph-mc',
            'WEST MINDORO':               'ph-mc',

            // Quezon — geoJSON uses plain "Quezon"
            'QUEZON PROVINCE':            'ph-qz',
            'QUEZON PROV':                'ph-qz',
            'QUEZON PROV.':               'ph-qz',

            // North Cotabato — geoJSON uses "Cotabato"
            'NORTH COTABATO':             'ph-nc',
            'NORTHERN COTABATO':          'ph-nc',
            'NORTE DE COTABATO':          'ph-nc',
            'COTABATO PROVINCE':          'ph-nc',
            'COTABATO (NORTH)':           'ph-nc',
            'NORTH COTABATO PROVINCE':    'ph-nc',

            // Davao de Oro — geoJSON still uses old name "Compostela Valley"
            'DAVAO DE ORO':               'ph-cl',
            'DAVAO DE ORO PROVINCE':      'ph-cl',

            // Davao del Norte / del Sur / Oriental word-order variants
            'DAVAO ORIENTAL':             'ph-do',
            'ORIENTAL DAVAO':             'ph-do',
            'NORTH DAVAO':                'ph-dv',
            'DAVAO NORTE':                'ph-dv',
            'SOUTH DAVAO':                'ph-ds',
            'DAVAO SUR':                  'ph-ds',

            // Maguindanao — geoJSON has single entry; split into two provinces since 2022
            'MAGUINDANAO DEL NORTE':      'ph-mg',
            'MAGUINDANAO DEL SUR':        'ph-mg',
            'NORTH MAGUINDANAO':          'ph-mg',
            'SOUTH MAGUINDANAO':          'ph-mg',

            // Surigao — geoJSON uses "Surigao del Norte" / "Surigao del Sur"
            'SURIGAO NORTE':              'ph-di',
            'NORTH SURIGAO':              'ph-di',
            'SURIGAO SUR':                'ph-ss',
            'SOUTH SURIGAO':              'ph-ss',

            // Agusan — geoJSON uses "Agusan del Norte" / "Agusan del Sur"
            'AGUSAN NORTE':               'ph-an',
            'NORTH AGUSAN':               'ph-an',
            'AGUSAN SUR':                 'ph-as',
            'SOUTH AGUSAN':               'ph-as',

            // Lanao — geoJSON uses "Lanao del Norte" / "Lanao del Sur"
            'LANAO NORTE':                'ph-ln',
            'NORTH LANAO':                'ph-ln',
            'LANAO SUR':                  'ph-ls',
            'SOUTH LANAO':                'ph-ls',

            // Zamboanga — geoJSON uses "Zamboanga del Norte" / "Zamboanga del Sur"
            'ZAMBOANGA NORTE':            'ph-zn',
            'NORTH ZAMBOANGA':            'ph-zn',
            'ZAMBOANGA SUR':              'ph-zs',
            'SOUTH ZAMBOANGA':            'ph-zs',

            // Negros — geoJSON uses "Negros Occidental" / "Negros Oriental"
            'OCCIDENTAL NEGROS':          'ph-nd',
            'WEST NEGROS':                'ph-nd',
            'NEGROS DEL NORTE':           'ph-nd',
            'ORIENTAL NEGROS':            'ph-nr',
            'EAST NEGROS':                'ph-nr',

            // Samar — geoJSON uses "Samar" (Western), "Eastern Samar", "Northern Samar"
            'WESTERN SAMAR':              'ph-sm',
            'WEST SAMAR':                 'ph-sm',
            'SAMAR (WESTERN)':            'ph-sm',
            'SAMAR DEL ESTE':             'ph-es',
            'SAMAR (EASTERN)':            'ph-es',
            'EAST SAMAR':                 'ph-es',
            'SAMAR DEL NORTE':            'ph-ns',
            'SAMAR (NORTHERN)':           'ph-ns',
            'NORTH SAMAR':                'ph-ns',

            // Tawi-Tawi — common hyphen-drop and merged spelling
            'TAWI TAWI':                  'ph-tt',
            'TAWITAWI':                   'ph-tt',

            // Kalinga — geoJSON key is "Kalinga"; old combined province name still in use
            'KALINGA-APAYAO':             'ph-ap',
            'KALINGA APAYAO':             'ph-ap',

            // Mountain Province abbreviations
            'MT. PROVINCE':               'ph-mt',
            'MT PROVINCE':                'ph-mt',
            'MOUNTAIN PROV.':             'ph-mt',
            'MOUNTAIN PROV':              'ph-mt',

            // Ilocos — occasional "Province" suffix in data
            'ILOCOS NORTE PROVINCE':      'ph-in',
            'NORTH ILOCOS':               'ph-in',
            'ILOCOS SUR PROVINCE':        'ph-is',
            'SOUTH ILOCOS':               'ph-is',

            // Camarines — occasional misspelling
            'CAMARINES NORTE PROVINCE':   'ph-cn',
            'CAMARINES SUR PROVINCE':     'ph-cs',
            'CAM. NORTE':                 'ph-cn',
            'CAM. SUR':                   'ph-cs',

            // Leyte
            'LEYTE DEL SUR':              'ph-sl',
            'SOUTH LEYTE':                'ph-sl',

            // Misamis
            'MISAMIS OR.':                'ph-mn',
            'MISAMIS OCC.':               'ph-md',

            // Compostela Valley legacy name handled above via 'DAVAO DE ORO'
            'COMPOSTELA VALLEY':          'ph-cl',
            'COMP. VALLEY':               'ph-cl',
        };
        Object.entries(aliases).forEach(([alias, hcKey]) => lookup.set(alias, hcKey));

        const mapPoints    = [];
        const unmatchedItems = [];

        salesData.forEach(item => {
            const hcKey = lookup.get(item.name) ?? null;
            const point = {
                'hc-key':  hcKey,
                name:      item.name,
                value:     item.value,
                drilldown: item.drilldown,
                accounts:  item.accounts ?? [],
            };
            if (hcKey !== null) {
                mapPoints.push(point);
            } else {
                unmatchedItems.push(point);
                console.warn('[SalesByAddress] No geoJSON match for province:', item.name);
            }
        });

        return { mapPoints, unmatchedItems };
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

        const { mapPoints } = buildMapData(chartData.data ?? []);

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
                enableTouchZoom: false,
                buttonOptions: { verticalAlign: 'bottom' },
            },
            tooltip: {
                useHTML: true,
                formatter: tooltipFormatter,
            },
            series: [{
                name: 'Sales by Province',
                data: mapPoints,
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
