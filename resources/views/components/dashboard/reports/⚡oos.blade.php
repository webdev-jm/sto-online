<?php

use Livewire\Component;
use Livewire\Attributes\Reactive;
use App\Http\Traits\SalesDataAggregator;

new class extends Component
{
    use SalesDataAggregator;

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

        $this->table_data = collect($this->getYearlyInventoryData($this->year))
            ->groupBy(fn($item) => $item['sku'] . '_' . $item['account_id'])
            ->map(function($items) {
                return $items->sortByDesc('month')->first();
            })
            ->filter(function($latest) {
                return $latest['total'] == 0;
            })
            ->map(function($latest) {
                return [
                    'account_code' => $latest['account_code'],
                    'account_name' => $latest['short_name'],
                    'sku'        => $latest['sku'],
                    'full_name'  => $latest['full_name'],
                    'uom'        => $latest['uom'],
                    'total'      => $latest['total'],
                    'month'      => $latest['month'],
                ];
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
                        <th>MONTH</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($table_data as $index => $item)
                        <tr>
                            <td>{{ $item['account_code'] }} - {{ $item['account_name'] }}</td>
                            <td>{{ $item['sku'] }}</td>
                            <td>{{ $item['month'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer"></div>
    </div>
</div>
