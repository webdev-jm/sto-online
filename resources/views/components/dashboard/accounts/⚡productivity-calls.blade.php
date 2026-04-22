<?php

use Livewire\Component;
use Livewire\Attributes\Reactive;
use Illuminate\Support\Facades\DB;
use App\Models\Account;

new class extends Component
{
    #[Reactive]
    public $year;
    #[Reactive]
    public $account_id;
    public $chart_data = [];

    public function mount($year, $account_id): void
    {
        $this->year       = $year;
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
        if (empty($this->account_id)) {
            $this->chart_data = ['categories' => [], 'series' => []];
            $this->dispatch('update-chart', data: $this->chart_data);
            return;
        }

        $account = Account::find($this->account_id);

        $rows = DB::connection($account->db_data->connection_name)
            ->table('sales as s')
            ->select([
                's.document_number as document_number',
                DB::raw("COALESCE(sm.name, csm.name, 'Unknown Salesman') as salesman_name"),
                DB::raw('MONTH(s.date) as month'),
            ])
            ->leftJoin('customers as c', 'c.id', '=', 's.customer_id')
            ->leftJoin('salesmen as sm', 'sm.id', '=', 's.salesman_id')
            ->leftJoin('salesmen as csm', 'csm.id', '=', 'c.salesman_id')
            ->whereYear('s.date', $this->year)
            ->whereNull('s.deleted_at')
            ->distinct()
            ->get();

        $collection = $rows->map(fn($row) => [
            'document_number' => $row->document_number,
            'salesman_name' => $row->salesman_name,
            'month'         => (int) $row->month,
        ]);

        $activeMonthNumbers = $collection->pluck('month')->unique()->sort()->values();
        $categories = $activeMonthNumbers->map(fn($m) => \Carbon\Carbon::create($this->year, $m, 1)->format('F'))->toArray();

        $salesmenNames = $collection->pluck('salesman_name')->unique()->values();

        $series = $salesmenNames->map(function ($salesman) use ($collection, $activeMonthNumbers) {
            $monthlyData = $activeMonthNumbers->map(fn($monthNum) =>
                $collection
                    ->where('salesman_name', $salesman)
                    ->where('month', $monthNum)
                    ->groupBy('document_number')
                    ->count()
            );

            return [
                'name'              => $salesman,
                'data'              => $monthlyData->values()->toArray(),
                'total_for_sorting' => $monthlyData->sum(),
            ];
        })
        ->sortBy('total_for_sorting')
        ->values()
        ->map(function ($item) {
            unset($item['total_for_sorting']);
            return $item;
        })
        ->toArray();

        $this->chart_data = [
            'categories' => $categories,
            'series'     => $series,
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
        <div class="chart-sk">
            <div class="chart-sk-shimmer"></div>
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
                stacking: 'normal',
                dataLabels: { enabled: true }
            }
        },
        series: chartData.series || []
    });

    const initChart = () => {
        chart = Highcharts.chart('container-productivity-calls', buildConfig($wire.chart_data));
    };

    initChart();

    $wire.on('update-chart', (event) => {
        const data = event.data;
        chart.update({
            xAxis: { categories: data.categories },
            series: data.series
        }, true, true);
    });
</script>
@endscript
