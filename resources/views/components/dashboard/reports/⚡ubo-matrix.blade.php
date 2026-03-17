<?php

use Livewire\Component;
use Livewire\Attributes\Reactive;
use Illuminate\Support\Facades\DB;
use App\Models\Account;
use App\Models\SMSProduct;
use App\Models\SMSPriceCode;
use App\Http\Traits\PriceCodeTrait;
use Illuminate\Support\Facades\Cache;

ini_set('memory_limit', '256M');
new class extends Component
{
    use PriceCodeTrait;

    #[Reactive]
    public $year;
    public $chart_data = [];

    public function mount($year) {
        $this->year = $year;
        $this->chartUpdated();
    }

    public function updatedYear() {
        $this->chartUpdated();
    }

    public function chartUpdated()
    {
        $rows = Cache::remember("ubo-matrix_{$this->year}", 60 * 60, function() {
            return DB::connection('sqlite_reports')
                ->table('sales_data')
                ->select(
                    'customer_code',
                    'customer_name',
                    'account_code',
                    'account_name',
                    'channel_code',
                    'channel_name',
                    'customer_status',
                    'stock_code',
                    'uom',
                    DB::raw('SUM(quantity) as total_qty'),
                )
                ->where('year', $this->year)
                ->where('customer_status', 0)
                ->groupBy(
                    'customer_code', 'customer_name',
                    'account_code',  'account_name',
                    'channel_code',  'channel_name',
                    'customer_status', 'stock_code', 'uom'
                )
                ->get();
        });

        if (!$rows->isEmpty()) {

            $stockCodes = $rows->pluck('stock_code')->unique();
            $products   = SMSProduct::whereIn('stock_code', $stockCodes)
                ->get()->keyBy('stock_code');

            $accounts = Account::where('id', '>=', 10)
                ->with('sms_account.company')
                ->get()
                ->filter(fn($a) => $a->sms_account && $a->sms_account->company)
                ->keyBy('account_code');

            $priceCache = [];
            foreach ($accounts as $accountCode => $account) {
                $smsAccount = $account->sms_account;
                $priceCodes = SMSPriceCode::where('company_id', $smsAccount->company->id)
                    ->where('code', $smsAccount->price_code)
                    ->whereIn('product_id', $products->pluck('id'))
                    ->get()->keyBy('product_id');

                foreach ($products as $sku => $product) {
                    $pCode     = $priceCodes->get($product->id);
                    $basePrice = $pCode ? $this->calculateBaseUnitPrice($product, $pCode) : 0;
                    if ($smsAccount->discount && $basePrice > 0) {
                        $basePrice = $this->applyDiscounts($basePrice, $smsAccount->discount);
                    }
                    $priceCache[$accountCode][$sku] = $basePrice;
                }
            }

            $this->chart_data = $rows->groupBy('customer_code')
                ->map(function ($items) use ($products, $priceCache) {
                    $first      = $items->first();
                    $totalSales = 0;
                    $totalQty   = 0;

                    foreach ($items as $item) {
                        $product   = $products->get($item->stock_code);
                        if (!$product) continue;

                        $netPrice  = $priceCache[$item->account_code][$item->stock_code] ?? 0;
                        $uomFactor = $this->getConversionFactor($product, $item->uom);
                        $qtyPcs    = $item->total_qty * $uomFactor;

                        $totalQty   += $qtyPcs;
                        $totalSales += $qtyPcs * $netPrice;
                    }

                    return [
                        'x'            => round($totalSales, 2),
                        'y'            => round($totalQty, 2),
                        'z'            => 0.1,
                        'name'         => $first->customer_name,
                        'account'      => $first->account_name,
                        'channel_code' => $first->channel_code,
                        'channel_name' => $first->channel_name,
                    ];
                })
                ->values()
                ->toArray();

            $this->dispatch('update-chart',
                data: $this->chart_data,
                year: $this->year
            );
        }
    }
};
?>

<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">UBO MATRIX ({{ $year }})</h3>
        </div>
        <div class="card-body" wire:ignore>
            <div id="container-ubo-matrix"></div>
        </div>
    </div>
</div>

@script
<script>
    let chart;

    const initChart = () => {
        chart = Highcharts.chart('container-ubo-matrix', {
            chart: { type: 'scatter', zooming: { type: 'xy' } },
            legend: { enabled: false },
            title: { text: 'UBO MATRIX ' + $wire.year },
            xAxis: {
                gridLineWidth: 1,
                title: { text: 'Sales' },
                labels: { format: '{value} php' },
            },
            yAxis: {
                startOnTick: false,
                endOnTick: false,
                title: { text: 'Units Sold' },
                labels: { format: '{value} pcs' },
                maxPadding: 0.2,
            },
            tooltip: {
                useHTML: true,
                headerFormat: '<table>',
                pointFormat:
                    '<tr><th colspan="2"><h3>{point.name}</h3></th></tr>' +
                    '<tr><th>Sales (php):</th><td>{point.x:,.2f}</td></tr>' +
                    '<tr><th>Units Sold (pcs):</th><td>{point.y:,.2f}</td></tr>' +
                    '<tr><th>Account:</th><td>{point.account}</td></tr>' +
                    '<tr><th>Channel:</th><td>{point.channel_name}</td></tr>',
                footerFormat: '</table>',
                followPointer: true
            },
            // ✅ Read directly from Blade-rendered computed property on init
            series: [{ data: @json($this->chart_data) }]
        });
    };

    initChart();

    $wire.on('update-chart', (event) => {
        chart.series[0].setData(event.data);
        chart.setTitle({ text: 'UBO MATRIX ' + event.year });
        chart.redraw();
    });
</script>
@endscript
