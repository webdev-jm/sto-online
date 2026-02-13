<?php

use Livewire\Component;
use App\Models\Account;
use App\Http\Traits\UomConversionTrait;
use App\Models\SMSProduct;

new class extends Component
{
    use UomConversionTrait;
    public $year;
    public $chart_data = [];
    public $products = [];

    public function mount() {
        $this->year = date('Y');

        // 1. Initialize the structure
        $this->chart_data = [
            'categories' => [
                'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
            ],
            'data' => []
        ];

        // 2. Get Consolidated Data
        $consolidated = $this->consolidateSalesData();

        // 3. Assign specific year data or fallback to zeros
        if(isset($consolidated[$this->year])) {
            $this->chart_data['data'] = $consolidated[$this->year]['data'];
        } else {
            // Fallback: 12 zeros if no data for current year
            $this->chart_data['data'] = array_fill(0, 12, 0);
        }

        $this->products = SMSProduct::get()->keyBy('stock_code');
    }

    public function getSalesData($account) {
        $sales_data = NULL;
        $jsonPath =  storage_path('app/reports/consolidated_account_data-'.$account->account_code.'.json');

        if (file_exists($jsonPath)) {
            $raw = json_decode(file_get_contents($jsonPath), true);

            // Return the collection directly, no need to wrap in []
            $sales_data = collect($raw['sales_data'])
                ->groupBy('year')
                ->map(function ($months, $year) {
                    return [
                        'name' => "Year $year",
                        'data' => $months->groupBy('month')
                            ->map(function ($items, $month) {
                                return [
                                    'y' => $items->sum(function($i) {
                                        $product = $this->products[$i['stock_code']] ?? null;
                                        
                                        // Safety check: if product not found, return raw quantity or 0
                                        if (!$product) return (float) $i['quantity'];

                                        return (float) $this->convertUom(
                                            $product, 
                                            $i['uom'], 
                                            $i['quantity'], 
                                            'PCS'
                                        );
                                    }),
                                    'month_num' => (int) $month
                                ];
                            })
                            ->values()
                            ->toArray()
                    ];
                });
        }

        return $sales_data;

    }

    public function consolidateSalesData() {
       $accounts = Account::where('id', '>=', 10)->get();

        // Temporary bucket to sum up all accounts: [Year][Month] = Total
        $temp_consolidated = [];

        foreach($accounts as $account) {
            $yearsData = $this->getSalesData($account);

            if (!$yearsData) continue;

            // Loop through the years in this account's file
            foreach ($yearsData as $year => $year_info) {
                // Loop through the months in this year
                foreach ($year_info['data'] as $month_item) {
                    $month_num = $month_item['month_num'];

                    // Initialize if not set
                    if (!isset($temp_consolidated[$year][$month_num])) {
                        $temp_consolidated[$year][$month_num] = 0;
                    }

                    // Accumulate the Total Quantity
                    $temp_consolidated[$year][$month_num] += $month_item['y'];
                }
            }
        }

        // Format for Highcharts
        $final_output = [];

        foreach ($temp_consolidated as $year => $months) {
            $monthly_series = [];

            // Force loop 1 to 12 (Jan to Dec)
            for ($m = 1; $m <= 12; $m++) {
                // Use the sum if it exists, otherwise 0
                $monthly_series[] = $months[$m] ?? 0;
            }

            $final_output[$year] = [
                'name' => "Year " . $year,
                'data' => $monthly_series // Now a clean array: [100, 0, 50, ...]
            ];
        }

        return $final_output;

    }

    public function updated($property) {
        if($property === 'year') {
            $this->chartUpdated();
        }
    }

    public function chartUpdated() {
        $consolidated = $this->consolidateSalesData();

        if(isset($consolidated[$this->year])) {
            $this->chart_data['data'] = $consolidated[$this->year]['data'];
        } else {
            // Fallback: 12 zeros if no data for current year
            $this->chart_data['data'] = array_fill(0, 12, 0);
        }

        $this->dispatch('update-chart', data: $this->chart_data);
    }
};
?>

<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">MONTHLY SALES VOLUME</h3>
            <div class="card-tools">
                <input type="number" class="form-control form-control-sm" wire:model.live="year">
            </div>
        </div>
        <div class="card-body" wire:ignore>
            <div id="container2"></div>
        </div>
    </div>
</div>

@assets
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/data.js"></script>
    <script src="https://code.highcharts.com/modules/drilldown.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/export-data.js"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>
@endassets

@script
<script>
    let chart;

    const initChart = () => {
        chart = Highcharts.chart('container2', {
            chart: {
                type: 'column'
            },
            title: {
                text: 'MONTHLY SALES VOLUME ' + $wire.year
            },
            xAxis: {
                categories: $wire.chart_data['categories'],
                title: {
                    text: 'Months'
                }
            },
            yAxis: {
                min: 0,
                title: {
                    text: 'Total Quantity Sold (PCS)'
                }
            },
            series: [{
                name: 'Sales Volume',
                data: $wire.chart_data['data'] // Sample data
            }]
        });
    };

    initChart();

    $wire.on('update-chart', (event) => {
        chart.series[0].setData($wire.chart_data['data']);
        chart.xAxis[0].setCategories($wire.chart_data['categories']);
        chart.setTitle({text: 'MONTHLY SALES VOLUME ' + $wire.year});
    });

</script>
@endscript
