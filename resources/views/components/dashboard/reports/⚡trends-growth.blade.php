<?php

use Livewire\Component;
use Livewire\Attributes\Reactive;
use App\Http\Traits\SalesDataAggregator;

new class extends Component
{
    use SalesDataAggregator;

    #[Reactive]
    public $year;
    #[Reactive]
    public ?int $account_id = null;
    public array $growth_stats = [];
    public array $sku_table    = [];
    public string $recent_label = '';
    public string $prior_label  = '';

    public function mount($year, $account_id = null): void
    {
        $this->year = $year;
        $this->account_id = $account_id;
        $this->computeGrowth();
    }

    public function updatedYear(): void
    {
        $this->computeGrowth();
    }

    public function computeGrowth(): void
    {
        $plan  = $this->getRollingMonthPlan(18);
        $years = collect($plan)->pluck('year')->unique();

        $all = collect();
        foreach ($years as $yr) {
            $all = $all->merge($this->getSalesData($yr, $this->account_id)->all());
        }

        // Attach a plan index (0 = oldest, 17 = newest) to every row so we can split into halves
        $planIndex = collect($plan)->mapWithKeys(fn($m, $i) => [$m['year'] . '-' . $m['month'] => $i]);

        $indexed = $all->filter(fn($row) => $planIndex->has($row['year'] . '-' . $row['month']))
            ->map(fn($row) => array_merge($row, ['plan_idx' => $planIndex[$row['year'] . '-' . $row['month']]]));

        // Recent 6 months = plan indices 12–17 | Prior 6 months = plan indices 6–11
        $recent = $indexed->filter(fn($r) => $r['plan_idx'] >= 12);
        $prior  = $indexed->filter(fn($r) => $r['plan_idx'] >= 6 && $r['plan_idx'] < 12);

        $recentTotal = $recent->sum('sales');
        $priorTotal  = $prior->sum('sales');
        $sixMoPct    = $priorTotal > 0 ? (($recentTotal - $priorTotal) / $priorTotal) * 100 : null;

        // MoM: compare the two most recent months in the plan
        $byPlanIdx = $indexed->groupBy('plan_idx')->map(fn($rows) => $rows->sum('sales'))->sortKeys();
        $momPct    = null;
        if ($byPlanIdx->count() >= 2) {
            $last   = $byPlanIdx->last();
            $penult = $byPlanIdx->slice(-2, 1)->first();
            $momPct = $penult > 0 ? (($last - $penult) / $penult) * 100 : null;
        }

        // SKU comparison: recent 6 months vs prior 6 months
        $recentBySku = $recent->groupBy('sku')->map(fn($rows) => ['name' => $rows->first()['name'], 'total' => $rows->sum('sales')]);
        $priorBySku  = $prior->groupBy('sku')->map(fn($rows) => $rows->sum('sales'));

        $skuChanges = $recentBySku
            ->filter(fn($data, $sku) => isset($priorBySku[$sku]) && $priorBySku[$sku] > 0)
            ->map(function ($data, $sku) use ($priorBySku) {
                return [
                    'sku'        => $sku,
                    'name'       => $data['name'],
                    'curr'       => $data['total'],
                    'prev'       => $priorBySku[$sku],
                    'change_pct' => round((($data['total'] - $priorBySku[$sku]) / $priorBySku[$sku]) * 100, 1),
                ];
            })
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
            'growers'   => $skuChanges->sortByDesc('change_pct')->take(5)->values()->toArray(),
            'decliners' => $skuChanges->sortBy('change_pct')->take(5)->values()->toArray(),
        ];
    }
};
?>

<div class="row">

    {{-- ── GROWTH STAT CARDS ─────────────────────────────────────── --}}
    <div class="col-12">
        <div class="stat-row">

            <div class="stat-card stat-col-3">
                <div class="stat-card-icon"><i class="fa fa-calendar-alt"></i></div>
                <div class="stat-card-body">
                    <span class="stat-card-label">6-Month Growth</span>
                    <span class="stat-card-value" style="color: {{ ($growth_stats['six_mo'] ?? 0) >= 0 ? '#28a745' : '#dc3545' }}">
                        @if($growth_stats['six_mo'] !== null)
                            {{ $growth_stats['six_mo'] >= 0 ? '+' : '' }}{{ number_format($growth_stats['six_mo'], 1) }}%
                        @else
                            N/A
                        @endif
                    </span>
                </div>
            </div>

            <div class="stat-card stat-col-3">
                <div class="stat-card-icon"><i class="fa fa-calendar-week"></i></div>
                <div class="stat-card-body">
                    <span class="stat-card-label">MoM Growth</span>
                    <span class="stat-card-value" style="color: {{ ($growth_stats['mom'] ?? 0) >= 0 ? '#28a745' : '#dc3545' }}">
                        @if($growth_stats['mom'] !== null)
                            {{ $growth_stats['mom'] >= 0 ? '+' : '' }}{{ number_format($growth_stats['mom'], 1) }}%
                        @else
                            N/A
                        @endif
                    </span>
                </div>
            </div>

            <div class="stat-card stat-col-3">
                <div class="stat-card-icon"><i class="fa fa-peso-sign"></i></div>
                <div class="stat-card-body">
                    <span class="stat-card-label">Recent 6M Sales</span>
                    <span class="stat-card-value">
                        ₱ {{ number_format($growth_stats['recent_total'] ?? 0, 2) }}
                    </span>
                </div>
            </div>

            <div class="stat-card stat-col-3">
                <div class="stat-card-icon"><i class="fa fa-history"></i></div>
                <div class="stat-card-body">
                    <span class="stat-card-label">Prior 6M Sales</span>
                    <span class="stat-card-value text-muted">
                        ₱ {{ number_format($growth_stats['prior_total'] ?? 0, 2) }}
                    </span>
                </div>
            </div>

        </div>
    </div>

    {{-- ── TOP GROWING SKUs ──────────────────────────────────────── --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fa fa-arrow-up text-success mr-1"></i> TOP GROWING SKUs</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0">
                        <thead>
                            <tr>
                                <th>SKU</th>
                                <th>Description</th>
                                <th class="text-right" title="{{ $prior_label }}">Prior 6M</th>
                                <th class="text-right" title="{{ $recent_label }}">Recent 6M</th>
                                <th class="text-right">Change</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sku_table['growers'] ?? [] as $row)
                                <tr>
                                    <td class="text-nowrap small">{{ $row['sku'] }}</td>
                                    <td class="small">{{ $row['name'] }}</td>
                                    <td class="text-right small">{{ number_format($row['prev'], 0) }}</td>
                                    <td class="text-right small">{{ number_format($row['curr'], 0) }}</td>
                                    <td class="text-right font-weight-bold text-success small">+{{ $row['change_pct'] }}%</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted small">No data available</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ── TOP DECLINING SKUs ────────────────────────────────────── --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fa fa-arrow-down text-danger mr-1"></i> TOP DECLINING SKUs</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0">
                        <thead>
                            <tr>
                                <th>SKU</th>
                                <th>Description</th>
                                <th class="text-right" title="{{ $prior_label }}">Prior 6M</th>
                                <th class="text-right" title="{{ $recent_label }}">Recent 6M</th>
                                <th class="text-right">Change</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sku_table['decliners'] ?? [] as $row)
                                <tr>
                                    <td class="text-nowrap small">{{ $row['sku'] }}</td>
                                    <td class="small">{{ $row['name'] }}</td>
                                    <td class="text-right small">{{ number_format($row['prev'], 0) }}</td>
                                    <td class="text-right small">{{ number_format($row['curr'], 0) }}</td>
                                    <td class="text-right font-weight-bold text-danger small">{{ $row['change_pct'] }}%</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted small">No data available</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
