<?php

use Livewire\Component;
use App\Http\Traits\SalesDataAggregator;

new class extends Component
{
    use SalesDataAggregator;

    public $year;
    public $chart_data = []; // Stores the formatted data for the grid

    public function mount() {
        $this->year = date('Y');
        $this->loadChartData();
    }

    public function updatedYear() {
        $this->loadChartData();
        $this->dispatch('refresh-grid', data: $this->chart_data);
    }

    public function loadChartData() {
        $raw = $this->getYearlySalesData($this->year);

        // Group, Sum, Sort
        $ranked = collect($raw)
            ->groupBy('sku')
            ->map(function($items) {
                return [
                    'stock_code' => $items->first()['sku'],
                    'name' => $items->first()['name'],
                    'y' => $items->sum('sales')
                ];
            })
            ->sortByDesc('y')
            ->values(); // Reset keys for ranking

        // Prepare Grid Columns
        $ranks = [];
        $skus = [];
        $descriptions = [];
        $sales = [];

        foreach ($ranked as $index => $item) {
            $ranks[] = $index + 1;
            $skus[] = $item['stock_code'];
            $descriptions[] = $item['name'];
            $sales[] = number_format($item['y'], 2, '.', ',');
        }

        $this->chart_data = [
            'Rank' => $ranks,
            'Stock Code' => $skus,
            'Description' => $descriptions,
            'Total Sales' => $sales
        ];
    }
};
?>

<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">TOP 10 SKU SALES ({{ $year }})</h3>
            <div class="card-tools">
                <input type="number" class="form-control form-control-sm" wire:model.live.debounce.500ms="year">
            </div>
        </div>
        <div class="card-body" wire:ignore>
            <div id="container4"></div>
        </div>
    </div>
</div>

@assets
    <script src="https://cdn.jsdelivr.net/npm/@highcharts/grid-lite/grid-lite.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@highcharts/grid-lite/css/grid-lite.css">
@endassets

@script
    <script>
        // Function to render the Grid
        function renderGrid(dataColumns) {
            // Check if we have data, otherwise handle empty state
            if (!dataColumns || Object.keys(dataColumns).length === 0) {
                document.getElementById('container4').innerHTML = '<p class="text-center p-3">No data available for this year.</p>';
                return;
            }

            // Clear previous grid to avoid duplication
            document.getElementById('container4').innerHTML = '';

            Grid.grid('container4', {
                dataTable: {
                    columns: dataColumns
                },
                rendering: {
                    rows: {
                        minVisibleRows: 10 // Show all top 10
                    }
                },
                columns: [
                    { id: 'Rank', width: 50 },
                    { id: 'Stock Code', width: 150 },
                    { id: 'Description' }, // Auto width
                    {
                        id: 'Total Sales',
                        width: 150,
                        // Simple formatter for currency
                        format: (value) => value ? '$' + Number(value).toLocaleString() : ''
                    }
                ]
            });
        }

        // 1. Initial Render
        renderGrid($wire.chart_data);

        // 2. Listen for 'refresh-grid' event from PHP
        $wire.on('refresh-grid', (event) => {
            // Livewire 3 events pass data in event.data or the first argument object
            const data = event.data || event[0]?.data;
            renderGrid(data);
        });
    </script>
@endscript
