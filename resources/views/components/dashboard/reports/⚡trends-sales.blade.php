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
    #[Reactive]
    public ?string $date_from = null;
    #[Reactive]
    public ?string $date_to = null;
    public array $chart_data = [];

    public function mount($year = null, $account_id = null, $date_from = null, $date_to = null): void
    {
        $this->year       = $year;
        $this->account_id = $account_id;
        $this->date_from  = $date_from;
        $this->date_to    = $date_to;
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

    public function updatedDateFrom(): void
    {
        $this->chartUpdated();
    }

    public function updatedDateTo(): void
    {
        $this->chartUpdated();
    }

    public function chartUpdated(): void
    {
        if ($this->date_from && $this->date_to) {
            $plan = $this->getMonthPlanForRange($this->date_from, $this->date_to);
            $all  = $this->getSalesDataForRange($this->date_from, $this->date_to, $this->account_id);
        } else {
            $plan  = $this->getRollingMonthPlan(18);
            $years = collect($plan)->pluck('year')->unique();
            $all   = collect();
            foreach ($years as $yr) {
                $all = $all->merge($this->getSalesData($yr, $this->account_id)->all());
            }
        }

        $byYearMonth = $all->groupBy(fn($row) => $row['year'] . '-' . $row['month'])
            ->map(fn($rows) => round($rows->sum('sales'), 2));

        $categories = [];
        $seriesData = [];

        foreach ($plan as $m) {
            $categories[] = $m['label'];
            $seriesData[] = ['name' => $m['label'], 'y' => $byYearMonth[$m['year'] . '-' . $m['month']] ?? 0];
        }

        $this->chart_data = [
            'categories' => $categories,
            'series'     => [['name' => 'Monthly Sales', 'data' => $seriesData, 'color' => 'rgba(5,141,199,1)']],
        ];

        $this->dispatch('update-trends-sales', data: $this->chart_data);
    }
};
?>

<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">SALES TREND &mdash; Last 18 Months</h3>
        </div>
        <div class="chart-sk">
            <div class="chart-sk-shimmer"></div>
        </div>
        <div class="card-body" wire:ignore>
            <div id="hc-trends-sales"></div>
        </div>
    </div>
</div>

@script
<script>
    let trendsSalesChart;

    const buildTrendsSalesConfig = (data) => ({
        credits: { enabled: false },
        chart: { type: 'line' },
        title: { text: null },
        accessibility: { enabled: false },
        xAxis: { categories: data.categories, crosshair: true, labels: { rotation: -45, style: { fontSize: '10px' } } },
        yAxis: { title: { text: 'Sales (₱)' } },
        legend: { enabled: false },
        plotOptions: {
            line: { marker: { enabled: true, radius: 3 } }
        },
        tooltip: {
            formatter: function () {
                const val = this.y;
                if (val >= 1_000_000_000) return `<b>${this.name}</b><br>₱ ${Highcharts.numberFormat(val / 1_000_000_000, 2)}B`;
                if (val >= 1_000_000)     return `<b>${this.name}</b><br>₱ ${Highcharts.numberFormat(val / 1_000_000, 2)}M`;
                if (val >= 1_000)         return `<b>${this.name}</b><br>₱ ${Highcharts.numberFormat(val / 1_000, 2)}K`;
                return `<b>${this.name}</b><br>₱ ${Highcharts.numberFormat(val, 2)}`;
            }
        },
        series: data.series,
    });

    trendsSalesChart = Highcharts.chart('hc-trends-sales', buildTrendsSalesConfig($wire.chart_data));

    $wire.on('update-trends-sales', (event) => {
        trendsSalesChart.destroy();
        trendsSalesChart = Highcharts.chart('hc-trends-sales', buildTrendsSalesConfig(event.data));
    });
</script>
@endscript
