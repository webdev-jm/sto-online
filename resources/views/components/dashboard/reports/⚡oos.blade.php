<?php

use Livewire\Component;
use Livewire\Attributes\Reactive;
use App\Http\Traits\SalesDataAggregator;
use App\Http\Traits\AccountProduct;

new class extends Component
{
    use SalesDataAggregator;
    use AccountProduct;

    #[Reactive]
    public $year;
    public $table_data    = [];
    public string $insight        = '';
    public bool   $loadingInsight = false;

    public function mount($year) {
        $this->year = $year;
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
        $total     = count($this->table_data);
        $distCount = collect($this->table_data)->pluck('account_code')->unique()->count();
        if ($total === 0) {
            return "No out-of-stock items recorded for {$this->year}.";
        }
        $topDist = collect($this->table_data)->groupBy('account_code')
            ->map->count()->sortByDesc(fn($c) => $c)->keys()->first();
        return "{$total} out-of-stock SKUs across {$distCount} distributors for {$this->year}. "
            . "Most affected: {$topDist}.";
    }

    public function chartUpdated(): void
    {
        $inventoryData = collect($this->getYearlyInventoryData($this->year));

        $accountMetaByCode = $inventoryData
            ->groupBy('account_code')
            ->map(fn($items) => [
                'short_name' => $items->first()['short_name'],
                'month'      => $items->max('month'),
            ]);

        $assignedProductsByAccount = $inventoryData
            ->pluck('account_code')
            ->unique()
            ->mapWithKeys(fn($accountCode) => [
                $accountCode => collect($this->getAssignedProducts($accountCode))
                    ->keyBy('stock_code')
            ]);

        $assignedSkusByAccount = $assignedProductsByAccount
            ->map(fn($products) => $products->keys()->toArray());

        $inventorySkusByAccount = $inventoryData
            ->groupBy('account_code')
            ->map(fn($items) => $items->pluck('sku')->unique()->toArray());

        $this->table_data = $assignedSkusByAccount
            ->flatMap(function($skus, $accountCode) use ($inventorySkusByAccount, $accountMetaByCode, $assignedProductsByAccount) {
                if (!isset($inventorySkusByAccount[$accountCode])) {
                    return [];
                }

                $inventorySkus = $inventorySkusByAccount[$accountCode];
                $missingSkus = array_diff($skus, $inventorySkus);
                $meta = $accountMetaByCode[$accountCode] ?? [];

                $products = $assignedProductsByAccount[$accountCode] ?? collect();

                return collect($missingSkus)->map(fn($sku) => [
                    'account_code' => $accountCode,
                    'short_name'   => $meta['short_name'] ?? null,
                    'sku'          => $sku,
                    'description'  => trim(($products->get($sku)?->description ?? '') . ' ' . ($products->get($sku)?->size ?? '')),
                    'month'        => $meta['month'] ?? null,
                ]);
            })
            ->values()
            ->toArray();
    }
};
?>

<div wire:init="generateInsight">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">OUT OF STOCK (OOS) {{ $year }}</h3>
        </div>
        <div class="card-body table-responsive p-0" style="max-height: 700px; overflow-y: auto;">
            <table class="table table-bordered table-sm table-hover m-0 text-xs">
                <thead class="bg-secondary" style="position: sticky; top: 0; z-index: 10;">
                    <tr>
                        <th>DISTRIBUTOR</th>
                        <th>STOCK CODE</th>
                        <th>AS OF</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($table_data as $index => $item)
                        <tr>
                            <td>{{ $item['account_code'] }} - {{ $item['short_name'] }}</td>
                            <td title="{{ $item['description'] }}">{{ $item['sku'] }}</td>
                            <td>{{ \DateTime::createFromFormat('!m', $item['month'])->format('M') }}</td>
                        </tr>
                    @endforeach
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
