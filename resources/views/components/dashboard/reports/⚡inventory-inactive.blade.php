<?php

use Livewire\Component;
use Livewire\Attributes\Reactive;
use App\Http\Traits\SalesDataAggregator;
use App\Http\Traits\AccountProduct;
use App\Models\Account;

new class extends Component
{
    use SalesDataAggregator;
    use AccountProduct;

    #[Reactive]
    public $year;
    #[Reactive]
    public ?int $account_id = null;
    public $table_data      = [];
    public $raw_table_data  = [];
    public $brands          = [];
    public $search          = '';
    public $selectedBrand   = '';
    public string $insight        = '';
    public bool   $loadingInsight = false;

    public function mount($year, $account_id = null): void
    {
        $this->year       = $year;
        $this->account_id = $account_id;
        $this->buildTableData();
    }

    public function updatedYear(): void
    {
        $this->buildTableData();
        $this->generateInsight();
    }

    public function generateInsight(): void
    {
        $this->loadingInsight = true;
        try {
            $this->insight = app(\App\Services\OllamaService::class)->chat([
                ['role' => 'system', 'content' => 'You are a business data analyst for a Philippine FMCG distributor. Given chart data, respond with exactly one concise insight sentence. No markdown, no bullet points, no labels.'],
                ['role' => 'user',   'content' => $this->buildInsightSummary()],
            ]);
        } catch (\App\Exceptions\AiUnavailableException) {
        }
        $this->loadingInsight = false;
    }

    private function buildInsightSummary(): string
    {
        $total = count($this->table_data);
        if ($total === 0) {
            return "No inactive products found for {$this->year}.";
        }
        $deadStock = collect($this->table_data)->filter(fn($r) => $r['inventory'] > 0)->count();
        $noStock   = $total - $deadStock;
        $distCount = collect($this->table_data)->pluck('account_code')->unique()->count();
        return "{$total} inactive SKUs (zero sales) across {$distCount} distributors for {$this->year}. "
            . "{$deadStock} have inventory on hand (dead stock risk); "
            . "{$noStock} have no inventory and no sales.";
    }

    public function buildTableData(): void
    {
        $salesLookup = collect($this->getYearlySalesData($this->year))
            ->groupBy(fn($row) => $row['account_id'] . ':' . $row['sku'])
            ->map(fn($rows) => $rows->sum('qty_pcs'));

        $inventoryLookup = collect($this->getYearlyInventoryData($this->year))
            ->groupBy(fn($row) => $row['account_code'] . ':' . $row['sku'])
            ->map(fn($rows) => [
                'total'        => $rows->sum('total'),
                'latest_month' => $rows->max('month'),
            ]);

        $activeAccountIds = collect($this->getYearlySalesData($this->year))
            ->pluck('account_id')
            ->merge(collect($this->getYearlyInventoryData($this->year))->pluck('account_id'))
            ->filter()
            ->unique()
            ->values();

        $accounts = Account::where('id', '>=', 10)
            ->whereIn('id', $activeAccountIds)
            ->get();

        if ($this->account_id) {
            $accounts = $accounts->where('id', $this->account_id);
        }

        $rows = [];
        foreach ($accounts as $account) {
            $assigned = $this->getAssignedProducts($account->account_code);
            foreach ($assigned as $product) {
                $sku        = $product->stock_code;
                $totalSales = (float) ($salesLookup->get($account->id . ':' . $sku) ?? 0);
                if ($totalSales > 0) {
                    continue;
                }
                $invEntry = $inventoryLookup->get($account->account_code . ':' . $sku);
                $rows[]   = [
                    'account_code' => $account->account_code,
                    'short_name'   => $account->short_name,
                    'sku'          => $sku,
                    'description'  => trim(($product->description ?? '') . ' ' . ($product->size ?? '')),
                    'brand'        => $product->brand ?? '',
                    'inventory'    => $invEntry ? (float) $invEntry['total'] : 0.0,
                    'sales'        => 0,
                    'month'        => $invEntry ? (int) $invEntry['latest_month'] : null,
                ];
            }
        }

        usort($rows, fn($a, $b) => $a['account_code'] <=> $b['account_code'] ?: $a['sku'] <=> $b['sku']);
        $this->raw_table_data = $rows;
        $this->brands         = collect($rows)->pluck('brand')->filter()->unique()->sort()->values()->toArray();
        $this->applyFilters();
    }

    public function updatedSearch(): void
    {
        $this->applyFilters();
    }

    public function updatedSelectedBrand(): void
    {
        $this->applyFilters();
    }

    public function applyFilters(): void
    {
        $term  = strtolower($this->search);
        $brand = $this->selectedBrand;

        $this->table_data = collect($this->raw_table_data)
            ->when($brand, fn($c) => $c->filter(fn($row) => $row['brand'] === $brand))
            ->when($term,  fn($c) => $c->filter(fn($row) =>
                str_contains(strtolower($row['account_code']), $term) ||
                str_contains(strtolower($row['short_name']), $term) ||
                str_contains(strtolower($row['sku']), $term)
            ))
            ->values()
            ->toArray();
    }
};
?>

<div wire:init="generateInsight">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">INACTIVE PRODUCTS {{ $year }}</h3>
            <div class="card-tools m-0 d-flex" style="gap: 4px;">
                <select class="form-control form-control-sm" wire:model.live="selectedBrand">
                    <option value="">All Brands</option>
                    @foreach ($brands as $brand)
                        <option value="{{ $brand }}">{{ $brand }}</option>
                    @endforeach
                </select>
                <input type="text" class="form-control form-control-sm" placeholder="Search" wire:model.live.debounce.300ms="search">
            </div>
        </div>
        <div class="card-body table-responsive p-0" style="max-height: 500px; overflow-y: auto;">
            <table class="table table-bordered table-sm table-hover m-0 text-xs">
                <thead class="bg-secondary" style="position: sticky; top: 0; z-index: 10;">
                    <tr>
                        <th>DISTRIBUTOR</th>
                        <th>STOCK CODE</th>
                        <th>INVENTORY</th>
                        <th>SALES</th>
                        <th>AS OF</th>
                    </tr>
                </thead>
                <tbody wire:loading.remove wire:target="search, selectedBrand, buildTableData">
                    @foreach ($table_data as $index => $item)
                        <tr>
                            <td>{{ $item['account_code'] }} - {{ $item['short_name'] }}</td>
                            <td title="{{ $item['description'] }}">{{ $item['sku'] }}</td>
                            <td>{{ number_format($item['inventory']) }}</td>
                            <td>{{ number_format($item['sales']) }}</td>
                            <td>{{ $item['month'] ? \DateTime::createFromFormat('!m', $item['month'])->format('M') : '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tbody wire:loading wire:target="search, selectedBrand, buildTableData">
                    <tr>
                        <td colspan="5">
                            <div class="d-flex justify-content-center align-items-center" style="min-height: 100px;">
                                <div class="spinner-border spinner-border-sm text-secondary mr-2"></div>
                                <span class="text-muted text-xs">Searching...</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
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
