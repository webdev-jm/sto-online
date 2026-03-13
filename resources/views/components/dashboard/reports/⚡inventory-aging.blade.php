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

    const EXPIRY_BUCKETS = [
        '1–3 Months'  => [1,  3],
        '3–6 Months'  => [3,  6],
        '6–12 Months' => [6,  12],
        '18+ Months'  => [18, null],
    ];

    public function mount($year) {
        $this->year = $year;
        $this->chartUpdated();
    }

    public function updatedYear() {
        $this->chartUpdated();
    }

    public function chartUpdated() {
        $raw = $this->getYearlyInventoryAgingData($this->year);

        $bucketTotals = array_fill_keys(array_keys(self::EXPIRY_BUCKETS), 0);

        foreach ($raw as $item) {
            $remainingDays   = (int) $this->computeRemainingDays($item['expiry_date']);
            $remainingMonths = $remainingDays / 30.44;
            $bucket          = $this->resolveBucket($remainingMonths);

            if ($bucket === null) continue;

            $bucketTotals[$bucket] += (float) $item['total_inventory'];
        }

        $this->chart_data = array_values($bucketTotals);

        $this->dispatch('update-chart',
            data: $this->chart_data,
            year: $this->year
        );
    }

    private function resolveBucket(float $months): ?string
    {
        foreach (self::EXPIRY_BUCKETS as $label => [$min, $max]) {
            if ($months >= $min && ($max === null || $months < $max)) {
                return $label;
            }
        }
        return null;
    }
};
?>

<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">INVENTORY AGING</h3>
        </div>
        <div class="card-body" wire:ignore>
            <div id="container-aging"></div>
        </div>
    </div>
</div>

@script
<script>
    const BUCKET_LABELS = ['1–3 Months', '3–6 Months', '6–12 Months', '18+ Months'];

    let chart;

    const initChart = () => {
        chart = Highcharts.chart('container-aging', {
            chart: {
                type: 'column'
            },
            title: {
                text: 'INVENTORY AGING ' + $wire.year
            },
            xAxis: {
                categories: BUCKET_LABELS,
                title: { text: 'Months Before Expiration' },
                crosshair: true
            },
            yAxis: {
                min: 0,
                title: { text: 'Total Quantity on Hand' }
            },
            legend: {
                enabled: false
            },
            tooltip: {
                headerFormat: '<b>{point.key}</b><br>',
                pointFormat: 'Total Quantity: <b>{point.y:,.2f}</b>',
            },
            plotOptions: {
                column: {
                    colorByPoint: true,
                    borderWidth: 0,
                    dataLabels: {
                        enabled: true,
                        format: '{point.y:,.2f}'
                    }
                }
            },
            series: [{
                name: 'Quantity',
                data: $wire.chart_data
            }]
        });
    };

    initChart();

    $wire.on('update-chart', (event) => {
        chart.series[0].setData(event.data, false);
        chart.setTitle({ text: 'INVENTORY AGING ' + event.year });
        chart.redraw();
    });
</script>
@endscript
