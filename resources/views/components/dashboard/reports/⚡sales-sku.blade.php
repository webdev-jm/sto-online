<?php

use Livewire\Component;
use App\Models\Account;
use App\Models\SMSProduct;
use App\Models\SMSPriceCode;
use App\Http\Traits\PriceCodeTrait;

new class extends Component
{
    use PriceCodeTrait;

    public $year;
    public $chart_data = [];

    public function mount() {
        $this->year = date('Y');

        $consolidated = $this->consolidateSalesData();
        $currentYearData = $consolidated[$this->year]['data'] ?? [];

        // 1. Separate Names and Values for Highcharts
        $this->chart_data = [
            'categories' => array_column($currentYearData, 'name'), // SKU Names
            'data'       => array_column($currentYearData, 'y')     // Sales Values
        ];
    }

    public function consolidateSalesData() {
        $account_data = [];
        $accounts = Account::where('id', '>=', 10)->get();

        foreach($accounts as $account) {
            $account_data[$account->account_code] = $this->getSalesData($account);
        }

        $temp_consolidated = [];

        // 1. Aggregate All Accounts
        foreach ($account_data as $acc_code => $years) {
            if (!$years) continue;

            foreach ($years as $year => $year_info) {
                foreach ($year_info['data'] as $stock_item) {
                    $stock_code = $stock_item['stock_code'];

                    if (!isset($temp_consolidated[$year][$stock_code])) {
                        $temp_consolidated[$year][$stock_code] = [
                            'name'       => $stock_item['name'],
                            'y'          => 0,
                            'stock_code' => $stock_code
                        ];
                    }

                    $temp_consolidated[$year][$stock_code]['y'] += $stock_item['y'];
                }
            }
        }

        $final_output = [];

        foreach ($temp_consolidated as $year => $stock_codes) {

            // --- NEW LOGIC: Sort & Limit ---

            // 2. Sort by Sales ('y') Descending (Highest first)
            usort($stock_codes, function ($a, $b) {
                return $b['y'] <=> $a['y'];
            });

            // 3. Take only the Top 10
            $top10 = array_slice($stock_codes, 0, 10);

            // round values
            $top10 = array_map(function($item) {
                $item['y'] = round($item['y'], 2); // <--- FIX HERE
                return $item;
            }, $top10);

            $final_output[$year] = [
                'name' => "Year " . $year,
                'data' => $top10
            ];
        }

        return $final_output;
    }

    // ... (Keep getSalesData logic as is) ...
    public function getSalesData($account) {
        // ... include the optimized getSalesData function from previous steps ...
        // (If you need me to paste the full function again, let me know)
        set_time_limit(120);
        ini_set('memory_limit', '256M');

        $jsonPath = storage_path('app/reports/consolidated_account_data-'.$account->account_code.'.json');

        if (!file_exists($jsonPath)) {
            return null;
        }

        $raw = json_decode(file_get_contents($jsonPath), true);
        $collection = collect($raw['sales_data']);

        $stockCodes = $collection->pluck('stock_code')->unique();

        $products = SMSProduct::whereIn('stock_code', $stockCodes)
                    ->get()
                    ->keyBy('stock_code');

        $smsAccount = $account->sms_account;
        $smsCompany = $smsAccount ? $smsAccount->company : null;

        if (!$smsAccount || !$smsCompany) return null;

        $priceCodes = SMSPriceCode::where('company_id', $smsCompany->id)
                    ->where('code', $smsAccount->price_code)
                    ->whereIn('product_id', $products->pluck('id'))
                    ->get()
                    ->keyBy('product_id');

        $priceCache = [];

        foreach ($products as $code => $product) {
            $pCode = $priceCodes->get($product->id);
            $basePrice = $pCode ? $this->calculateBaseUnitPrice($product, $pCode) : 0;

            if ($smsAccount->discount && $basePrice > 0) {
                $basePrice = $this->applyDiscounts($basePrice, $smsAccount->discount);
            }
            $priceCache[$code] = $basePrice;
        }

        return $collection
            ->groupBy('year')
            ->map(function ($yearItems, $year) use ($products, $priceCache) {
                return [
                    'name' => "Year $year",
                    'data' => $yearItems->groupBy('stock_code')
                        ->map(function ($skuItems, $stockCode) use ($products, $priceCache) {
                            $totalSales = $skuItems->sum(function($i) use ($products, $priceCache) {
                                $code = $i['stock_code'];
                                $netBasePrice = $priceCache[$code] ?? 0;
                                if ($netBasePrice == 0) return 0;
                                $product = $products->get($code);
                                if (!$product) return 0;
                                $uomFactor = $this->getConversionFactor($product, $i['uom']);
                                return $i['quantity'] * $uomFactor * $netBasePrice;
                            });

                            return [
                                'name' => $stockCode,
                                'y' => (float) $totalSales,
                                'stock_code' => $stockCode
                            ];
                        })
                        ->values() // NOTE: Removed sortBy here since we sort globally later
                        ->toArray()
                ];
            })
            ->toArray();
    }

    public function updated($property) {
        if($property === 'year') {
            $this->chartUpdated();
        }
    }

    public function chartUpdated() {
        $consolidated = $this->consolidateSalesData();
        $currentYearData = $consolidated[$this->year]['data'] ?? [];

        // 1. Separate Names and Values for Highcharts
        $this->chart_data = [
            'categories' => array_column($currentYearData, 'name'), // SKU Names
            'data'       => array_column($currentYearData, 'y')     // Sales Values
        ];

        $this->dispatch('update-chart', data: $this->chart_data);
    }
};
?>

<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">TOP SKU BASED ON SALES ({{ $year }})</h3>
            <div class="card-tools">
                <input type="number" class="form-control form-control-sm" wire:model.live="year">
            </div>
        </div>
        <div class="card-body" wire:ignore>
            <div id="container3"></div>
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
        chart = Highcharts.chart('container3', {
            chart: {
                type: 'bar'
            },
            title: {
                text: 'TOP SKU BASED ON SALES ' + $wire.year
            },
            xAxis: {
                // Bind Categories dynamically
                categories: $wire.chart_data['categories'],
                title: {
                    text: 'Product SKU'
                }
            },
            yAxis: {
                min: 0,
                title: {
                    text: 'Total Sales ({{ $year }})',
                    align: 'high'
                }
            },
            tooltip: {
                valuePrefix: '₱ '
            },
            series: [{
                name: 'Sales Amount',
                // Bind Data dynamically
                data: $wire.chart_data['data']
            }]
        });
    };

    initChart();

    $wire.on('update-chart', (event) => {
        chart.series[0].setData($wire.chart_data['data']);
        chart.xAxis[0].setCategories($wire.chart_data['categories']);
        chart.setTitle({text: 'TOP SKU BASED ON SALES ' + $wire.year});
    });

</script>
@endscript
