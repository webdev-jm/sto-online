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
    public $table_data = [];

    public function mount($year) {
        $this->year = $year;
        $this->chartUpdated();


    }

    public function updatedYear() {
        $this->chartUpdated();
    }

    public function chartUpdated() {
        $inventoryData = collect($this->getYearlyInventoryData($this->year));

        $accountMetaByCode = $inventoryData
            ->groupBy('account_code')
            ->map(fn($items) => [
                'short_name' => $items->first()['short_name'],
                'month'      => $items->max('month'),
            ]);

        $assignedSkusByAccount = $inventoryData
            ->pluck('account_code')
            ->unique()
            ->mapWithKeys(fn($accountCode) => [
                $accountCode => collect($this->getAssignedProducts($accountCode))
                    ->pluck('stock_code')
                    ->toArray()
            ]);

        $inventorySkusByAccount = $inventoryData
            ->groupBy('account_code')
            ->map(fn($items) => $items->pluck('sku')->unique()->toArray());

        $this->table_data = $assignedSkusByAccount
            ->flatMap(function($skus, $accountCode) use ($inventorySkusByAccount, $accountMetaByCode) {
                if (!isset($inventorySkusByAccount[$accountCode])) {
                    return [];
                }

                $inventorySkus = $inventorySkusByAccount[$accountCode];
                $missingSkus = array_diff($skus, $inventorySkus);
                $meta = $accountMetaByCode[$accountCode] ?? [];

                return collect($missingSkus)->map(fn($sku) => [
                    'account_code' => $accountCode,
                    'short_name'   => $meta['short_name'] ?? null,
                    'sku'          => $sku,
                    'month'        => $meta['month'] ?? null,
                ]);
            })
            ->values()
            ->toArray();
    }
};
?>

<div>
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
                            <td>{{ $item['sku'] }}</td>
                            <td>{{ \DateTime::createFromFormat('!m', $item['month'])->format('M') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer"></div>
    </div>
</div>
