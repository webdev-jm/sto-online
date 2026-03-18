<?php

use Livewire\Component;
use Livewire\Attributes\Reactive;
use App\Http\Traits\SalesDataAggregator;
use App\Http\Traits\UomConversionTrait;
use App\Models\SMSProduct;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

new class extends Component
{
    use SalesDataAggregator;
    use UomConversionTrait;

    #[Reactive]
    public $year;
    public $table_data     = [];
    public $raw_table_data = [];
    public $products;
    public $search         = '';

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

    public function updatedSearch() {
        $this->applySearch();
    }

    public function applySearch() {
        if (empty($this->search)) {
            $this->table_data = $this->raw_table_data;
            return;
        }

        $term = strtolower($this->search);

        $this->table_data = collect($this->raw_table_data)
            ->filter(fn($row) =>
                str_contains(strtolower($row['account']), $term) ||
                str_contains(strtolower($row['sku']), $term)
            )
            ->values()
            ->toArray();
    }

    public function chartUpdated() {
        $cache_key = "yearly_inventory_{$this->year}";
        $raw       = Cache::remember($cache_key, 60 * 15, fn() => $this->getYearlyInventoryData($this->year));

        $inventories = collect($raw);

        $api_url   = config('services.sysprodata.url');
        $api_token = config('services.sysprodata.token');

        // Get latest month per account_code
        $latest_month_per_account = $inventories
            ->groupBy(fn($item) => $item['account_code'])
            ->map(fn($items) => $items->max('month'));

        $grouped = $inventories
            ->filter(fn($item) =>
                $item['month'] == ($latest_month_per_account[$item['account_code']] ?? null)
            )
            ->groupBy(fn($item) => $item['sku'] . '_' . $item['short_name'])
            ->map(fn($items) => [
                'first'        => $items->sortByDesc('month')->first(),
                'total'        => $items->sum('total'),
                'latest_month' => $items->max('month'), // carry it forward
            ]);

        $unique_accounts = $grouped
            ->mapWithKeys(fn($row) => [$row['first']['account_code'] => $row])
            ->filter(fn($row) => !empty($row['first']['account_code']));

        $account_keys = $unique_accounts->keys()->values();

        $responses = Http::pool(function ($pool) use ($unique_accounts, $account_keys, $api_url, $api_token) {
            return $account_keys->map(function ($account_code) use ($pool, $unique_accounts, $api_url, $api_token) {
                $row   = $unique_accounts[$account_code];
                $first = $row['first'];
                $month = $row['latest_month']; // use per-account month

                return $pool->as($account_code)
                    ->withHeaders([
                        'Accept'        => 'application/json',
                        'Authorization' => 'Bearer ' . $api_token,
                        'year'          => $this->year,
                        'month'         => $month,
                        'company'       => 'BEVA',
                        'account_code'  => $account_code,
                    ])
                    ->timeout(30)
                    ->get($api_url . 'getOrders');
            })->all();
        });

        // Get all unique account+month combos for sell_out query
        $account_month_pairs = $unique_accounts->map(fn($row) => [
            'account_code' => $row['first']['account_code'],
            'month'        => $row['latest_month'],
        ])->values();

        $sales_data = Cache::remember("sales_data_{$this->year}_per_account", 60 * 15, function () use ($account_month_pairs) {
            return DB::connection('sqlite_reports')
                ->table('sales_data')
                ->select('account_code', 'account_name', 'stock_code', 'uom', 'month', DB::raw('SUM(quantity) as total'))
                ->where('year', $this->year)
                ->where(function ($query) use ($account_month_pairs) {
                    foreach ($account_month_pairs as $pair) {
                        $query->orWhere(function ($q) use ($pair) {
                            $q->where('account_code', $pair['account_code'])
                            ->where('month', $pair['month']);
                        });
                    }
                })
                ->groupBy('account_code', 'account_name', 'stock_code', 'uom', 'month')
                ->get();
        });

        $this->raw_table_data = $grouped->map(function ($row) use ($responses, $sales_data) {
            $first        = $row['first'];
            $product      = $this->products->get($first['sku']);
            $sell_in      = 0;
            $sell_out     = 0;
            $account_code = $first['account_code'] ?? null;
            $latest_month = $row['latest_month'];
            $response     = $responses[$account_code] ?? null;

            if ($response instanceof \Throwable) {
                \Log::warning("Pool request failed for account [{$account_code}]: " . $response->getMessage());
            } elseif ($response && $response->successful()) {
                $sell_in = collect($response->json())
                    ->where('StockCode', $first['sku'])
                    ->sum('total');
            }

            if ($sales_data->isNotEmpty()) {
                $sell_out = $sales_data
                    ->where('stock_code', $first['sku'])
                    ->where('account_code', $account_code)
                    ->where('month', $latest_month)
                    ->sum(fn($item) => $this->convertUom($product, $item->uom, $item->total, 'PCS'));
            }

            return [
                'account'       => $first['short_name'],
                'sku'           => $first['sku'],
                'total'         => $row['total'],
                'sell_in'       => $sell_in,
                'sell_out'      => $sell_out,
                'latest_month'  => $latest_month, // optional: useful for debugging
            ];
        })->values()->toArray();

        $this->applySearch();
    }
};
?>

<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">ENDING INVENTORY {{ $year }} <i class="fa fa-spinner fa-spin fa-sm" wire:loading></i></h3>
            <div class="card-tools m-0">
                <input type="text" class="form-control form-control-sm" placeholder="Search" wire:model.live.debounce.300ms="search">
            </div>
        </div>
        <div class="card-body table-responsive p-0" style="max-height: 300px; overflow-y: auto;">

            <table class="table table-bordered table-sm table-hover m-0 text-xs">
                <colgroup>
                    <col span="6">
                </colgroup>

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

                <tbody wire:loading.remove wire:target="search, chartUpdated">
                    @foreach($table_data as $data)
                        <tr>
                            <td>{{ $data['account'] }}</td>
                            <td>{{ $data['sku'] }}</td>
                            <td>{{ number_format($data['total'] ?? 0) }}</td>
                            <td>{{ number_format($data['sell_in'] ?? 0) }}</td>
                            <td>{{ number_format($data['sell_out'] ?? 0) }}</td>
                            <td>{{ number_format(($data['total'] + $data['sell_in']) - $data['sell_out']) }}</td>
                        </tr>
                    @endforeach
                </tbody>

                <tbody wire:loading wire:target="search, chartUpdated">
                    <tr>
                        <td colspan="6">
                            <div class="d-flex justify-content-center align-items-center" style="min-height: 100px;">
                                <div class="spinner-border spinner-border-sm text-secondary mr-2"></div>
                                <span class="text-muted text-xs">Searching...</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="card-footer"></div>
    </div>
</div>
