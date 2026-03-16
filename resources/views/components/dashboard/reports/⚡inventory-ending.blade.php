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
            return SMSProduct::get();
        });

        $this->chartUpdated();
    }

    public function updatedYear() {
        $this->chartUpdated();
    }

    public function chartUpdated() {
        $raw         = $this->getYearlyInventoryData($this->year);
        $inventories = collect($raw);
        $latest_month = $inventories->max('month');


        $api_url   = env('API_URL_SYSPRODATA');
        $api_token = env('API_TOKEN_SYSPRODATA');

        $grouped = $inventories
            ->groupBy(fn($item) => $item['sku'] . '_' . $item['short_name'])
            ->filter(fn($items) => $items->where('month', $latest_month)->isNotEmpty())
            ->map(fn($items) => [
                'first'   => $items->sortByDesc('month')->first(),
                'total'   => $items->where('month', $latest_month)->sum('total'),
            ]);

        // Build all HTTP requests concurrently via pool
        $keys = $grouped->keys()->values();
        $responses = Http::pool(function ($pool) use ($grouped, $keys, $api_url, $api_token, $latest_month) {
            return $keys->map(function ($key) use ($pool, $grouped, $api_url, $api_token, $latest_month) {
                $first = $grouped[$key]['first'];
                return $pool->as($key)
                    ->withHeaders([
                        'Accept'         => 'application/json',
                        'Authorization'  => 'Bearer ' . $api_token,
                        'year'           => $this->year,
                        'month'          => $latest_month,
                        'company'        => 'BEVA',
                        'account_code'   => $first['account_code'] ?? null,
                        'stock_code'     => $first['sku'],
                    ])
                    ->timeout(30)
                    ->get($api_url . 'getOrders');
            })->all();
        });

        // Map final table_data using pooled responses
        $this->table_data = $grouped->map(function ($row, $key) use ($responses) {
            $first   = $row['first'];
            $product = $this->products->firstWhere('stock_code', $first['sku']);
            $sell_in = 0;

            $response = $responses[$key] ?? null;

            if ($response && $response->successful()) {
                foreach ($response->json() as $val) {
                    $sell_in += $this->convertUom($product, $val['uom'], $val['total'], 'PCS');
                }
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
