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

        $this->chart_data = collect($raw)
            ->when($this->account_id, fn($col) => $col->where('account_id', $this->account_id))
            ->groupBy('salesman_code')
            ->map(function ($items) {
                $first = $items->first();
                return [
                    'name' => $first['salesman_name'] ?: $first['salesman_code'] ?: 'Unknown',
                    'y'    => round($items->sum('sales'), 2),
                ];
            })
            ->sortByDesc('y')
            ->values()
            ->toArray();

        $this->dispatch('update-chart', data: $this->chart_data);
    }
};
?>

<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">SALES BY SALESMAN {{ $this->year }}</h3>
        </div>
        <div class="card-body" wire:ignore>
            <div id="container-account-salesmen"></div>
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

    const buildConfig = (data) => ({
        colors: getColors(),
        credits: { enabled: false },
        chart: { type: 'pie' },
        title: { text: null },
        legend: { enabled: false },
        accessibility: { announceNewData: { enabled: true } },
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
                borderWidth: 2,
                cursor: 'pointer',
                dataLabels: {
                    enabled: true,
                    formatter: function () {
                        return `<b>${this.point.name}</b><br>${Highcharts.numberFormat(this.percentage, 1)}%`;
                    },
                    distance: 20
                },
                showInLegend: true
            }
        },
        series: [{
            enableMouseTracking: true,
            animation: { duration: 1000 },
            colorByPoint: true,
            data: data
        }]
    });

    const initChart = () => {
        chart = Highcharts.chart('container-account-salesmen', buildConfig($wire.chart_data));
    };

    initChart();

    $wire.on('update-chart', (event) => {
        chart.destroy();
        chart = Highcharts.chart('container-account-salesmen', buildConfig(event.data));
    });

    // Rebuild on dark mode toggle
    const observer = new MutationObserver(() => {
        chart.destroy();
        chart = Highcharts.chart('container-account-salesmen', buildConfig($wire.chart_data));
    });
    observer.observe(document.body, { attributes: true, attributeFilter: ['class'] });
</script>
@endscript
