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
    #[Reactive]
    public ?int $account_id = null;
    public ?string $account_code = null;
    public $chart_data    = [];
    public string $insight        = '';
    public bool   $loadingInsight = false;

    public function mount($year, $account_id = null): void {
        $this->year = $year;
        $this->account_id = $account_id;
        if ($account_id) {
            $this->account_code = Account::find($account_id)?->account_code;
        }
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
        $count = count($this->chart_data);
        if ($count === 0) {
            return "No UBO matrix data available for {$this->year}.";
        }
        $topSales = collect($this->chart_data)->sortByDesc('x')->first();
        $topUnits = collect($this->chart_data)->sortByDesc('y')->first();
        return "{$count} active buyers tracked in the UBO matrix for {$this->year}. "
            . "Highest sales buyer: {$topSales['name']} (₱" . number_format($topSales['x'], 2) . "). "
            . "Most units: {$topUnits['name']} (" . number_format($topUnits['y'], 0) . " pcs).";
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

        if ($this->account_code) {
            $rows = $rows->where('account_code', $this->account_code)->values();
        }

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

<div wire:init="generateInsight">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">UBO MATRIX ({{ $year }})</h3>
        </div>
        <div class="chart-sk">
            <div class="chart-sk-shimmer"></div>
        </div>

        <div class="card-body" wire:ignore>
            <div id="container-ubo-matrix"></div>
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

    const initChart = () => {
        chart = Highcharts.chart('container-ubo-matrix', {
            credits: {
                enabled: false
            },
            chart: {
                type: 'scatter',
                panning: {
                    enabled: true,
                    type: 'xy'
                },
                panKey: 'ctrl',
                zooming: {
                    type: 'xy',
                    mouseWheel: {
                        enabled: true,
                        type: 'xy'
                    },
                    resetButton: {
                        position: {
                            align: 'right',
                            verticalAlign: 'top',
                            x: -10,
                            y: 10
                        }
                    }
                }
            },
            title: {
                text: null,
                enabled: false
            },
            legend: { enabled: false },
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
        chart.redraw();
    });
</script>
@endscript
