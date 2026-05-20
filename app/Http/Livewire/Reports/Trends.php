<?php

namespace App\Http\Livewire\Reports;

use Livewire\Component;

use App\Models\ConsolidatedSalesReport;
use App\Http\Traits\SalesDataAggregator;

class Trends extends Component
{
    use SalesDataAggregator;

    public $account_branch;

    public array $sales_chart_data     = [];
    public array $inventory_chart_data = [];
    public array $growth_stats         = [];
    public array $sku_table            = [];
    public string $recent_label        = '';
    public string $prior_label         = '';

    public function mount($account_branch): void
    {
        $this->account_branch = $account_branch;
        $this->loadData();
    }

    public function loadData(): void
    {
        $this->loadSalesTrend();
        $this->loadInventoryTrend();
        $this->loadGrowthStats();
    }

    private function loadSalesTrend(): void
    {
        $accountCode = $this->account_branch->account->account_code;
        $plan        = $this->getRollingMonthPlan(18);
        $yearsNeeded = collect($plan)->pluck('year')->unique()->toArray();

        $rows = ConsolidatedSalesReport::where('account_code', $accountCode)
            ->whereIn('year', $yearsNeeded)
            ->selectRaw('year, month, SUM(sales) as total')
            ->groupBy('year', 'month')
            ->get()
            ->keyBy(fn($r) => $r->year . '-' . $r->month);

        $categories = [];
        $seriesData = [];

        foreach ($plan as $m) {
            $key          = $m['year'] . '-' . $m['month'];
            $categories[] = $m['label'];
            $seriesData[] = ['name' => $m['label'], 'y' => round((float) ($rows->get($key)?->total ?? 0), 2)];
        }

        $this->sales_chart_data = [
            'categories' => $categories,
            'series'     => [['name' => 'Monthly Sales', 'data' => $seriesData, 'color' => 'rgba(5,141,199,1)']],
        ];

        $this->dispatch('update-trends-sales-report', data: $this->sales_chart_data);
    }

    private function loadInventoryTrend(): void
    {
        $accountId   = $this->account_branch->account->id;
        $plan        = $this->getRollingMonthPlan(18);
        $years       = collect($plan)->pluck('year')->unique();

        $all = collect();
        foreach ($years as $yr) {
            $all = $all->merge($this->getInventoryData($yr, $accountId)->all());
        }

        $byYearMonth = $all->groupBy(fn($r) => $r['year'] . '-' . $r['month'])
            ->map(fn($rows) => round($rows->sum('total'), 2));

        $categories = [];
        $seriesData = [];

        foreach ($plan as $m) {
            $key          = $m['year'] . '-' . $m['month'];
            $categories[] = $m['label'];
            $seriesData[] = ['name' => $m['label'], 'y' => $byYearMonth[$key] ?? 0];
        }

        $this->inventory_chart_data = [
            'categories' => $categories,
            'series'     => [['name' => 'Inventory', 'data' => $seriesData, 'color' => 'rgba(80,180,50,1)']],
        ];

        $this->dispatch('update-trends-inventory-report', data: $this->inventory_chart_data);
    }

    private function loadGrowthStats(): void
    {
        $accountCode = $this->account_branch->account->account_code;
        $plan        = $this->getRollingMonthPlan(18);
        $yearsNeeded = collect($plan)->pluck('year')->unique()->toArray();

        $planIndex = collect($plan)->mapWithKeys(fn($m, $i) => [$m['year'] . '-' . $m['month'] => $i]);

        $rows = ConsolidatedSalesReport::where('account_code', $accountCode)
            ->whereIn('year', $yearsNeeded)
            ->selectRaw('year, month, stock_code, description, SUM(sales) as total')
            ->groupBy('year', 'month', 'stock_code', 'description')
            ->get()
            ->filter(fn($r) => $planIndex->has($r->year . '-' . $r->month))
            ->map(fn($r) => (object) array_merge((array) $r->toArray(), ['plan_idx' => $planIndex[$r->year . '-' . $r->month]]));

        // Split: recent 6 months (indices 12–17) vs prior 6 months (indices 6–11)
        $recent = $rows->filter(fn($r) => $r->plan_idx >= 12);
        $prior  = $rows->filter(fn($r) => $r->plan_idx >= 6 && $r->plan_idx < 12);

        $recentTotal = $recent->sum('total');
        $priorTotal  = $prior->sum('total');
        $sixMoPct    = $priorTotal > 0 ? (($recentTotal - $priorTotal) / $priorTotal) * 100 : null;

        // MoM: two most recent months
        $byIdx  = $rows->groupBy('plan_idx')->map(fn($g) => $g->sum('total'))->sortKeys();
        $momPct = null;
        if ($byIdx->count() >= 2) {
            $last   = $byIdx->last();
            $penult = $byIdx->slice(-2, 1)->first();
            $momPct = $penult > 0 ? (($last - $penult) / $penult) * 100 : null;
        }

        // SKU comparison: recent 6 vs prior 6
        $recentBySku = $recent->groupBy('stock_code')->map(fn($g) => ['name' => $g->first()->description, 'total' => $g->sum('total')]);
        $priorBySku  = $prior->groupBy('stock_code')->map(fn($g) => ['name' => $g->first()->description, 'total' => $g->sum('total')]);

        $allSkus = $recentBySku->keys()->merge($priorBySku->keys())->unique();

        $skuChanges = $allSkus->map(function ($sku) use ($recentBySku, $priorBySku) {
            $prior  = $priorBySku->get($sku);
            $recent = $recentBySku->get($sku);

            if (!$prior || $prior['total'] <= 0) {
                return null;
            }

            $curr      = $recent ? (float) $recent['total'] : 0.0;
            $prev      = (float) $prior['total'];
            $name      = $recent ? $recent['name'] : $prior['name'];
            $changePct = round((($curr - $prev) / $prev) * 100, 1);

            return ['sku' => $sku, 'name' => $name, 'curr' => $curr, 'prev' => $prev, 'change_pct' => $changePct];
        })
        ->filter()
        ->values();

        $this->recent_label = $plan[12]['label'] . ' – ' . $plan[17]['label'];
        $this->prior_label  = $plan[6]['label']  . ' – ' . $plan[11]['label'];

        $this->growth_stats = [
            'six_mo'       => $sixMoPct !== null ? round($sixMoPct, 1) : null,
            'mom'          => $momPct   !== null ? round($momPct, 1)   : null,
            'recent_total' => $recentTotal,
            'prior_total'  => $priorTotal,
        ];

        $this->sku_table = [
            'growers'   => $skuChanges->filter(fn($r) => $r['change_pct'] > 0)
                                      ->sortByDesc('change_pct')->take(5)->values()->toArray(),
            'decliners' => $skuChanges->filter(fn($r) => $r['change_pct'] < 0)
                                      ->sortBy('change_pct')->take(5)->values()->toArray(),
        ];
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.reports.trends');
    }
}
