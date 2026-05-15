<?php

namespace App\Http\Livewire\Reports;

use Livewire\Component;
use Livewire\WithPagination;

use App\Models\SMSProduct;
use App\Models\MonthlyInventory;
use App\Models\Sale;
use App\Services\OllamaService;
use App\Exceptions\AiUnavailableException;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Http\Traits\UomConversionTrait;

class Vmi extends Component
{
    use UomConversionTrait;
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $account_branch;
    public $year, $month, $parameter, $month_param;
    public $search;

    public array $ai_recommendations = [];
    public bool $ai_loading = false;
    public ?string $ai_error = null;

    public $months_arr = [
        1 => '1 MONTH',
        2 => '2 MONTHS',
        3 => '3 MONTHS',
        6 => '6 MONTHS',
    ];

    public function updatedSearch(): void
    {
        $this->resetPage('inventory-page');
    }

    public function updatedMonth(): void
    {
        if ($this->month > 12) {
            $this->month = 12;
        } elseif ($this->month < 1) {
            $this->month = 1;
        }
    }

    public function mount($account_branch): void
    {
        $this->account_branch = $account_branch;

        $this->year = date('Y');
        $this->month = 4;

        $this->parameter = 4;
        $this->month_param = 1;
    }

    public function getAiRecommendations(): void
    {
        $this->ai_error = null;
        $this->ai_loading = true;

        ['data' => $data] = $this->computeData();

        $round2 = fn(float $v): float => round($v, 2);

        $payload = collect($data)->map(fn ($row, $pid) => [
            'id'   => $pid,
            'sale' => collect($row['months_data'])->pluck('sto')->map($round2)->values(),
            'gap'  => collect($row['months_data'])->pluck('w_cov_needed')->map($round2)->values(),
        ])->values()->toJson(JSON_UNESCAPED_UNICODE);

        try {
            $content = app(OllamaService::class)->chat([
                [
                    'role'    => 'system',
                    'content' => 'VMI analyst. Target WOC=' . $this->parameter . '. Each item: id=product int, sale=[1mo,2mo,3mo,6mo] avg monthly sales (CS), gap=[1mo,2mo,3mo,6mo] coverage gap vs target (positive=understocked, negative=overstocked). Write a 2-3 sentence analysis per product covering stock status, sales trend, and action needed. OUTPUT: raw JSON array only, no markdown. Each element: {"product_id":<id>,"analysis":"<2-3 sentence text>"}',
                ],
                [
                    'role'    => 'user',
                    'content' => $payload,
                ],
            ]);

            $this->ai_recommendations = collect(json_decode($content, true) ?? [])
                ->keyBy('product_id')
                ->toArray();
        } catch (AiUnavailableException $e) {
            $this->ai_recommendations = [];
            $this->ai_error = 'AI service is unavailable. Please try again later.';
        }

        $this->ai_loading = false;
    }

    public function render()
    {
        ['data' => $data, 'inventories' => $inventories] = $this->computeData();

        return view('livewire.reports.vmi')->with([
            'data'        => $data,
            'inventories' => $inventories,
        ]);
    }

    private function buildPrevDates(): array
    {
        $prev_dates = [];
        for ($i = 1; $i <= 6; $i++) {
            $d = new Carbon($this->year . '-' . $this->month . '-01');
            $prev_dates[$i] = $d->startOfMonth()->subMonth($i);
        }
        return $prev_dates;
    }

    private function computeData(): array
    {
        $prev_dates = $this->buildPrevDates();

        $inventories = MonthlyInventory::select(
                'product_id',
                'uom',
                DB::raw('SUM(total) as total'),
            )
            ->where('account_id', $this->account_branch->account_id)
            ->where('account_branch_id', $this->account_branch->id)
            ->where('year', $prev_dates[1]->year)
            ->where('month', $prev_dates[1]->month)
            ->where('total', '>', 0)
            ->when(!empty($this->search), function ($query) {
                $query->whereExists(function ($qry) {
                    $qry->select(DB::raw(1))
                        ->from(env('DB_DATABASE_2') . '.products')
                        ->whereColumn('products.id', 'monthly_inventories.product_id')
                        ->where(function ($qry1) {
                            $qry1->where('stock_code', 'like', '%' . $this->search . '%')
                                ->orWhere('description', 'like', '%' . $this->search . '%')
                                ->orWhere('size', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->groupBy('product_id', 'uom')
            ->paginate(20, ['*'], 'inventory-page');

        $product_ids = $inventories->pluck('product_id')->toArray();
        $products    = SMSProduct::whereIn('id', $product_ids)->get()->keyBy('id');

        $months_param = [1, 2, 3, 6];
        $param_sales  = [];

        foreach ($months_param as $param) {
            $param_sales[$param] = Sale::select(
                    'product_id',
                    DB::raw('CASE WHEN TRIM(uom) = "CAS" THEN "CS" ELSE TRIM(uom) END as uom'),
                    DB::raw('SUM(quantity) / ' . $param . ' as total')
                )
                ->whereIn('product_id', $product_ids)
                ->where('account_id', $this->account_branch->account_id)
                ->where('account_branch_id', $this->account_branch->id)
                ->where(function ($query) use ($prev_dates, $param) {
                    for ($i = 1; $i <= $param; $i++) {
                        $query->orWhere(function ($qry) use ($prev_dates, $i) {
                            $qry->where(DB::raw('MONTH(date)'), $prev_dates[$i]->month)
                                ->where(DB::raw('YEAR(date)'), $prev_dates[$i]->year);
                        });
                    }
                })
                ->groupBy('product_id', 'uom')
                ->get()
                ->groupBy('product_id');
        }

        $data = [];
        foreach ($inventories as $inventory) {
            if (!empty($inventory->total)) {
                $product  = $products->get($inventory->product_id);
                $cs_total = $this->convertUom($product, $inventory->uom, $inventory->total, 'CS');

                $months_data = [];
                foreach ($months_param as $param) {
                    $sales_data     = $param_sales[$param]->get($inventory->product_id);
                    $sales_cs_total = 0;

                    if (!empty($sales_data)) {
                        foreach ($sales_data as $val) {
                            $sales_cs_total += $this->convertUom($product, $val->uom, $val->total, 'CS');
                        }
                    }

                    $w_cov = 0;
                    if (!empty($cs_total) && !empty($sales_cs_total)) {
                        $w_cov = $cs_total / $sales_cs_total;
                    }

                    $w_cov_needed = 0;
                    if (!empty($w_cov) && !empty($this->parameter)) {
                        $w_cov_needed = $this->parameter - $w_cov;
                    }

                    $vmi = 0;
                    if (!empty($w_cov_needed) && !empty($sales_cs_total)) {
                        $vmi = $w_cov_needed * $sales_cs_total;
                    }

                    $months_data[$param] = [
                        'sto'          => $sales_cs_total ?? 0,
                        'w_cov'        => $w_cov,
                        'w_cov_needed' => $w_cov_needed,
                        'vmi'          => $vmi,
                    ];
                }

                $data[$inventory->product_id] = [
                    'stock_code'  => $product->stock_code,
                    'description' => $product->description . ' ' . $product->size,
                    'uom'         => $inventory->uom,
                    'total'       => $inventory->total,
                    'cs_total'    => $cs_total,
                    'months_data' => $months_data,
                ];
            }
        }

        return ['data' => $data, 'inventories' => $inventories];
    }
}
