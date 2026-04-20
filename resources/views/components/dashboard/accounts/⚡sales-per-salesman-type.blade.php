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
        $this->year       = $year;
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
        $collection = collect($raw);

        // Filter by account if necessary
        $filtered = $collection->when($this->account_id, fn($col) => $col->where('account_id', $this->account_id));

        $drilldownSeries = [];

        $mainData = $filtered
            ->groupBy('salesman_type')
            ->map(function ($items, $type) use (&$drilldownSeries) {
                $typeName = $type ?: 'Unknown Type';
                // Normalize casing for the ID to prevent mismatches
                $drillId = 'type_' . md5(strtoupper($typeName));

                // Level 2: Individual Salesmen under this type
                $drilldownSeries[] = [
                    'id'   => $drillId,
                    'name' => $typeName,
                    'type' => 'pie', // Keep it as a pie chart for consistency
                    'data' => $items->groupBy('salesman_name')
                                ->map(fn($salesmanItems, $name) => [
                                    'name' => $name ?: 'Unknown Salesman',
                                    'y'    => round($salesmanItems->sum('sales'), 2),
                                ])
                                ->sortByDesc('y')
                                ->values()
                                ->toArray(),
                ];

                return [
                    'name'      => $typeName,
                    'y'         => round($items->sum('sales'), 2),
                    'drilldown' => $drillId,
                ];
            })
            ->sortByDesc('y')
            ->values()
            ->toArray();

        $this->chart_data = [
            'series'    => $mainData,
            'drilldown' => $drilldownSeries,
        ];

        $this->dispatch('update-chart', data: $this->chart_data);
    }
};
?>

<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">SALES BY SALESMAN TYPE {{ $this->year }}</h3>
        </div>
        <div class="card-body" wire:ignore>
            <div id="container-account-salesman-type"></div>
        </div>
    </div>
</div>

@script
<script>
    let chart;

    const isDark    = () => document.body.classList.contains('dark-mode');
    const getColors = () => isDark()
        ? ['rgba(5,141,199,0.88)', 'rgba(80,180,50,0.88)', 'rgba(237,86,27,0.88)',
           'rgba(162,75,209,0.88)', 'rgba(255,196,0,0.88)', 'rgba(50,200,150,0.88)']
        : ['rgba(5,141,199,0.7)',  'rgba(80,180,50,0.7)',  'rgba(237,86,27,0.7)',
           'rgba(162,75,209,0.7)', 'rgba(255,196,0,0.7)',  'rgba(50,200,150,0.7)'];

    const buildConfig = (chartData) => ({
        colors: getColors(),
        credits: { enabled: false },
        chart: { type: 'pie' },
        title: { text: null },
        legend: { enabled: true }, // Enabled for drilldown context
        tooltip: {
            formatter: function () {
                const val = Highcharts.numberFormat(this.y, 2);
                return `<span style="color:${this.point.color}">\u25cf</span>
                        <b>${this.point.name}</b><br>
                        ₱ ${val} (${Highcharts.numberFormat(this.percentage, 1)}%)`;
            }
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    enabled: true,
                    format: '<b>{point.name}</b>: {point.percentage:.1f}%'
                }
            }
        },
        // Main Series
        series: [{
            name: 'Salesman Types',
            colorByPoint: true,
            data: chartData.series || []
        }],
        // Drilldown Series
        drilldown: {
            series: chartData.drilldown || [],
            activeDataLabelStyle: {
                textDecoration: 'none',
                fontStyle: 'italic'
            }
        }
    });

    const initChart = () => {
        chart = Highcharts.chart('container-account-salesman-type', buildConfig($wire.chart_data));
    };

    initChart();

    $wire.on('update-chart', (event) => {
        chart.destroy();
        chart = Highcharts.chart('container-account-salesman-type', buildConfig(event.data));
    });

    // Rebuild on dark mode toggle
    const observer = new MutationObserver(() => {
        chart.destroy();
        chart = Highcharts.chart('container-account-salesman-type', buildConfig($wire.chart_data));
    });
    observer.observe(document.body, { attributes: true, attributeFilter: ['class'] });
</script>
@endscript
