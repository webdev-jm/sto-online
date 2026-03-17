<?php

use Livewire\Component;
use Livewire\Attributes\Reactive;
use App\Http\Traits\SalesDataAggregator;
use App\Http\Traits\UomConversionTrait;
use App\Models\SMSProduct;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

new class extends Component
{
    use SalesDataAggregator;
    use UomConversionTrait;

    #[Reactive]
    public $year;
    public $table_data = [];
    public $products;

    public function mount($year) {
        $this->year = $year;

        $this->products = Cache::remember('products_cache', 60 * 60, function () {
            return SMSProduct::get()->keyBy('stock_code');
        });

        $this->chartUpdated();
    }

    public function updatedYear() {
        $this->chartUpdated();
    }

    public function chartUpdated() {
        $cache_key = "yearly_inventory_{$this->year}";
        $raw       = Cache::remember($cache_key, 60 * 15, fn() => $this->getYearlyInventoryData($this->year));

        $inventories  = collect($raw);
        // $latest_month = $inventories->max('month');
        $latest_month = 1;

        $api_url   = config('services.sysprodata.url');
        $api_token = config('services.sysprodata.token');

        $grouped = $inventories
            ->where('month', $latest_month)
            ->groupBy(fn($item) => $item['sku'] . '_' . $item['short_name'])
            ->map(fn($items) => [
                'first' => $items->sortByDesc('month')->first(),
                'total' => $items->sum('total'),
            ]);

        // Deduplicate by account_code — one request per account, not per SKU
        $unique_accounts = $grouped
            ->mapWithKeys(fn($row) => [$row['first']['account_code'] => $row['first']])
            ->filter(fn($first) => !empty($first['account_code']));

        $account_keys = $unique_accounts->keys()->values();

        $responses = Http::pool(function ($pool) use ($unique_accounts, $account_keys, $api_url, $api_token, $latest_month) {
            return $account_keys->map(function ($account_code) use ($pool, $unique_accounts, $api_url, $api_token, $latest_month) {
                $first = $unique_accounts[$account_code];
                return $pool->as($account_code)
                    ->withHeaders([
                        'Accept'        => 'application/json',
                        'Authorization' => 'Bearer ' . $api_token,
                        'year'          => $this->year,
                        'month'         => $latest_month,
                        'company'       => 'BEVA',
                        'account_code'  => $account_code,
                    ])
                    ->timeout(30)
                    ->get($api_url . 'getOrders');
            })->all();
        });


        $this->table_data = $grouped->map(function ($row) use ($responses) {
            $first        = $row['first'];
            $product      = $this->products->get($first['sku']);
            $sell_in      = 0;
            $account_code = $first['account_code'] ?? null;
            $response     = $responses[$account_code] ?? null;

            if ($response instanceof \Throwable) {
                \Log::warning("Pool request failed for account [{$account_code}]: " . $response->getMessage());
            } elseif ($response && $response->successful()) {
                $sell_in = collect($response->json())
                    ->where('StockCode', $first['sku'])
                    ->sum(fn($item) => $this->convertUom($product, $item['uom'], $item['total'], 'PCS'));
            }

            return [
                'account' => $first['short_name'],
                'sku'     => $first['sku'],
                'total'   => $row['total'],
                'sell_in' => $sell_in,
            ];
        })->values();
    }
};
?>

<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">ENDING INVENTORY {{ $year }}</h3>
        </div>
        <div class="card-body table-responsive p-0" style="max-height: 300px; overflow-y: auto;">
            <table class="table table-bordered table-sm table-hover m-0 text-xs">
                <thead class="bg-secondary" style="position: sticky; top: 0; z-index: 10;">
                    <tr>
                        <th>DISTRIBUTOR</th>
                        <th>STOCK CODE</th>
                        <th>OPENING BALANCE</th>
                        <th>SELL IN</th>
                        <th>SELL OUT</th>
                        <th>SHOULD BE</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($table_data as $data)
                        <tr>
                            <td>{{ $data['account'] }}</td>
                            <td>{{ $data['sku'] }}</td>
                            <td>{{ number_format($data['total'] ?? 0) }}</td>
                            <td>{{ number_format($data['sell_in'] ?? 0) }}</td>
                            <td></td>
                            <td></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer"></div>
    </div>
</div>
