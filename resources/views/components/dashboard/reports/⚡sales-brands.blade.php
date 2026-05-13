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
            return "No brand sales data available for {$this->year}.";
        }
        $top3 = collect($this->chart_data['data'])->take(3)
            ->map(fn($d) => "{$d['name']}: ₱" . number_format($d['y'], 2))->implode(', ');
        return "Sales by brand group for {$this->year}. Top 3: {$top3}.";
    }

    public function chartUpdated(): void
    {
        $raw = $this->getYearlySalesData($this->year);
        $collection = collect($raw);

        $drilldown  = [];

        $chart_data = $collection
            ->groupBy('brand_tag')
            ->map(function ($tagItems, $brandTag) use (&$drilldown) {
                $tagDrillId = 'tag_' . md5($brandTag);

                // Level 1: brand_tag → brands
                $brandData = $tagItems->groupBy('brand')
                    ->map(function ($brandItems, $brand) use ($brandTag, &$drilldown) {
                        $brandDrillId = 'brand_' . md5($brandTag . $brand);

                        // Level 2: brand → category
                        $categoryData = $brandItems->groupBy('category')
                            ->map(function ($catItems, $category) use ($brandTag, $brand, &$drilldown) {
                                $catDrillId = 'cat_' . md5($brandTag . $brand . $category);

                                // Level 3: category → SKU
                                $drilldown[] = [
                                    'id'   => $catDrillId,
                                    'name' => $category ?: 'Uncategorized',
                                    'type' => 'pie',
                                    'data' => $catItems->groupBy('sku')
                                                ->map(fn($skuItems, $sku) => [
                                                    'name'      => $sku ?: 'Unknown SKU',
                                                    'full_name' => $skuItems->first()['full_name'] ?? '',
                                                    'y'         => round($skuItems->sum('sales'), 2),
                                                ])
                                                ->sortByDesc('y')
                                                ->values()
                                                ->toArray(),
                                ];

                                return [
                                    'name'      => $category ?: 'Uncategorized',
                                    'y'         => round($catItems->sum('sales'), 2),
                                    'drilldown' => $catDrillId,
                                ];
                            })
                            ->sortByDesc('y')
                            ->values()
                            ->toArray();

                        // Level 2 drilldown: brand → categories
                        $drilldown[] = [
                            'id'   => $brandDrillId,
                            'name' => $brand ?: 'Other',
                            'type' => 'pie',
                            'data' => $categoryData,
                        ];

                        return [
                            'name'      => $brand ?: 'Other',
                            'y'         => round($brandItems->sum('sales'), 2),
                            'drilldown' => $brandDrillId,
                        ];
                    })
                    ->sortByDesc('y')
                    ->values()
                    ->toArray();

                // Level 1 drilldown: brand_tag → brands
                $drilldown[] = [
                    'id'   => $tagDrillId,
                    'name' => $brandTag ?: 'Untagged',
                    'type' => 'pie',
                    'data' => $brandData,
                ];

                return [
                    'name'      => $brandTag ?: 'Untagged',
                    'y'         => round($tagItems->sum('sales'), 2),
                    'drilldown' => $tagDrillId,
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

<div wire:init="generateInsight">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">SALES BY BRAND {{ $this->year }}</h3>
        </div>
        <div class="chart-sk">
            <div class="chart-sk-shimmer"></div>
        </div>

        <div class="card-body" wire:ignore>
            <div id="container-brands"></div>
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
                type: 'pie',
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
                            return `<b>${this.point.name}</b><br>${Highcharts.numberFormat(this.percentage, 1)}%`;
                        }
                    },
                    showInLegend: true
                }
            },
            tooltip: {
                formatter: function () {
                    const val  = this.y;
                    const full = Highcharts.numberFormat(val, 2);
                    const label = this.point.full_name || this.point.name;
                    return `<span style="font-size:11px">${this.series.name}</span><br>
                            <b>${label}</b><br>
                            ₱ <b>${full}</b> (${Highcharts.numberFormat(this.percentage, 1)}%)`;
                }
            },
            series: [{
                name: 'BRAND CATEGORIES',
                styledMode: true,
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
