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

        $filtered = $collection->when($this->account_id, fn($col) => $col->where('account_id', $this->account_id));

        // Determine active months for the X-Axis
        $activeMonthNumbers = $filtered->pluck('month')->unique()->sort()->values();
        $categories = $activeMonthNumbers->map(function($m) {
            return \Carbon\Carbon::create($this->year, $m, 1)->format('F');
        })->toArray();

        // Get unique list of salesmen
        $salesmenNames = $filtered->pluck('salesman_name')->unique()->values();

        // Create and Sort the Series
        $series = $salesmenNames->map(function($salesman) use ($filtered, $activeMonthNumbers) {
            $monthlyData = $activeMonthNumbers->map(function($monthNum) use ($filtered, $salesman) {
                return $filtered->where('salesman_name', $salesman)
                    ->where('month', $monthNum)
                    ->groupBy('customer_code')
                    ->count();
            });

            return [
                'name' => $salesman ?: 'Unknown Salesman',
                'data' => $monthlyData->values()->toArray(),
                'total_for_sorting' => $monthlyData->sum() // Temp value to help us sort
            ];
        })
        ->sortBy('total_for_sorting') // Sort based on the sum of all active months
        ->values()
        ->map(function($item) {
            // Clean up: remove the temp sorting key before sending to JS
            unset($item['total_for_sorting']);
            return $item;
        })
        ->toArray();

        $this->chart_data = [
            'categories' => $categories,
            'series' => $series
        ];

        $this->dispatch('update-chart', data: $this->chart_data);
    }

};
?>

<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">PRODUCTIVITY CALLS {{ $this->year }}</h3>
        </div>
        <div class="card-body" wire:ignore>
            <div id="container-productivity-calls" style="height: 500px;"></div>
        </div>
    </div>
</div>

@script
<script>
    let chart;

    const buildConfig = (chartData) => ({
        credits: { enabled: false },
        chart: { type: 'bar' },
        title: { text: null },
        xAxis: {
            // Use the salesmen names sent from PHP
            categories: chartData.categories || [],
            title: { text: 'Months' }
        },
        yAxis: {
            min: 0,
            title: { text: 'Unique Customer Calls' }
        },
        legend: { reversed: true },
        plotOptions: {
            series: {
                stacking: 'normal', // Keeps months stacked per salesman
                dataLabels: { enabled: true }
            }
        },
        // Use the series array generated in PHP
        series: chartData.series || []
    });

    const initChart = () => {
        chart = Highcharts.chart('container-productivity-calls', buildConfig($wire.chart_data));
    };

    initChart();

    $wire.on('update-chart', (event) => {
        // Efficiency: Use update instead of destroy/re-init if possible
        const data = event.data;
        chart.update({
            xAxis: { categories: data.categories },
            series: data.series
        }, true, true);
    });
</script>
@endscript
