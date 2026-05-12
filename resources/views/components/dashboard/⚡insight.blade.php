<?php

use Livewire\Component;
use Livewire\Attributes\Reactive;
use App\Services\OllamaService;
use App\Services\RagService;
use Illuminate\Support\Facades\DB;

new class extends Component
{
    #[Reactive]
    public $year;

    public string $type    = 'sales'; // 'sales' or 'inventories'
    public string $insight = '';
    public string $scope   = 'Overall';
    public bool $isGenerating = false;
    public bool $hasGenerated = false;

    public function mount($year, string $type): void
    {
        $this->year = $year;
        $this->type = $type;
    }

    public function updatedYear(): void
    {
        $this->resetInsight();
    }

    public function resetInsight(): void
    {
        $this->insight      = '';
        $this->hasGenerated = false;
        $this->scope        = 'Overall';
    }

    public function generateInsight(): void
    {
        $this->isGenerating = true;

        $account = session('account');
        $label   = $this->type === 'sales' ? 'Sales Performance' : 'Inventory & Supply Chain';
        $data    = $this->type === 'sales' ? $this->buildSalesContext() : $this->buildInventoryContext();

        $this->scope = $account ? "[{$account->account_code}] {$account->short_name}" : 'Overall';

        $messages = [
            [
                'role'    => 'system',
                'content' => implode("\n", [
                    "You are a senior business analyst for BEV Portal (Beauty Elements Ventures), an FMCG distribution system in the Philippines.",
                    "Your job is to interpret {$this->year} {$label} data and surface actionable intelligence for management.",
                    "",
                    "Before writing your response, silently reason through the following:",
                    "- Month-over-month variance: which months gained or dropped significantly, and why?",
                    "- Year-over-year comparison: is the business growing, flat, or declining vs the prior year?",
                    "- If per-account data is present alongside overall data, identify how this account compares to the overall picture.",
                    "- Flag any concentration risk (e.g. revenue dominated by one SKU, channel, or month).",
                    "",
                    "Then respond using EXACTLY this format — three labelled sections, bullet points only, no preamble, no closing remarks:",
                    "",
                    "Key Trends",
                    "• <finding 1>",
                    "• <finding 2>",
                    "• <finding 3>",
                    "• <finding 4>",
                    "• <finding 5 — omit if not supported by data>",
                    "",
                    "Risks",
                    "• <risk 1>",
                    "• <risk 2>",
                    "• <risk 3>",
                    "• <risk 4 — omit if not supported by data>",
                    "",
                    "Recommendations",
                    "• <action 1>",
                    "• <action 2>",
                    "• <action 3>",
                    "• <action 4 — omit if not supported by data>",
                    "",
                    "Rules:",
                    "- Cite numbers from the data (e.g. PHP #,###.## or %). Do not invent figures.",
                    "- Do not add extra blank lines between bullets within the same section.",
                    "- Do not answer anything outside the scope of the business data provided.",
                ]),
            ],
            [
                'role'    => 'user',
                'content' => "{$this->year} {$label} data:\n\n{$data}",
            ],
        ];

        $reply = trim(app(OllamaService::class)->chat($messages));

        // Collapse 3+ consecutive newlines to at most 2
        $this->insight      = preg_replace('/\n{3,}/', "\n\n", $reply);
        $this->hasGenerated = true;
        $this->isGenerating = false;
    }

    private function buildSalesContext(): string
    {
        $account = session('account');
        $months  = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $overall = '';

        try {
            $db = DB::connection('sqlite_reports');

            $rows = $db->table('sales_data')
                ->selectRaw('month, SUM(sales) as total_sales, SUM(quantity) as total_qty, COUNT(DISTINCT customer_code) as customers, COUNT(DISTINCT stock_code) as skus')
                ->where('year', $this->year)
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            if ($rows->isNotEmpty()) {
                $totalSales  = $rows->sum('total_sales');
                $totalQty    = $rows->sum('total_qty');
                $peak        = $rows->sortByDesc('total_sales')->first();
                $lowest      = $rows->sortBy('total_sales')->first();
                $ytdMonths   = $rows->pluck('month')->all();

                $prevRows = $db->table('sales_data')
                    ->selectRaw('SUM(sales) as total_sales')
                    ->where('year', $this->year - 1)
                    ->whereIn('month', $ytdMonths)
                    ->first();

                $overall  = "== Overall ({$this->year}) ==\n";
                $overall .= "Total Sales: PHP " . number_format($totalSales, 2) . " | Qty: " . number_format($totalQty) . " pcs | Customers: {$rows->max('customers')} | SKUs: {$rows->max('skus')}\n";

                if ($prevRows && $prevRows->total_sales > 0) {
                    $yoyPct      = (($totalSales - $prevRows->total_sales) / $prevRows->total_sales) * 100;
                    $monthLabels = collect($ytdMonths)->map(fn($m) => $months[$m - 1])->implode('–');
                    $overall    .= "YoY ({$monthLabels} only): " . ($yoyPct >= 0 ? '+' : '') . number_format($yoyPct, 1) . "% vs " . ($this->year - 1) . " (PHP " . number_format($prevRows->total_sales, 2) . ")\n";
                }

                $overall .= "Peak: " . ($months[$peak->month - 1] ?? '') . " (PHP " . number_format($peak->total_sales, 2) . ") | Lowest: " . ($months[$lowest->month - 1] ?? '') . " (PHP " . number_format($lowest->total_sales, 2) . ")\n";
                $overall .= "Monthly: " . $rows->map(fn($r) => ($months[$r->month - 1] ?? $r->month) . " PHP " . number_format($r->total_sales, 0))->implode(', ') . "\n";

                // Top 5 SKUs
                $topSkus = $db->table('sales_data')
                    ->selectRaw('stock_code, description, SUM(sales) as total_sales, SUM(quantity) as total_qty')
                    ->where('year', $this->year)
                    ->groupBy('stock_code', 'description')
                    ->orderByDesc('total_sales')
                    ->limit(5)
                    ->get();

                if ($topSkus->isNotEmpty()) {
                    $overall .= "Top SKUs:\n";
                    foreach ($topSkus as $i => $s) {
                        $overall .= ($i + 1) . ". [{$s->stock_code}] {$s->description} — PHP " . number_format($s->total_sales, 2) . ", " . number_format($s->total_qty) . " pcs\n";
                    }
                }

                // Top 5 accounts
                $topAccounts = $db->table('sales_data')
                    ->selectRaw('account_code, account_name, SUM(sales) as total_sales')
                    ->where('year', $this->year)
                    ->groupBy('account_code', 'account_name')
                    ->orderByDesc('total_sales')
                    ->limit(5)
                    ->get();

                if ($topAccounts->isNotEmpty()) {
                    $overall .= "Top Accounts:\n";
                    foreach ($topAccounts as $i => $a) {
                        $overall .= ($i + 1) . ". [{$a->account_code}] {$a->account_name} — PHP " . number_format($a->total_sales, 2) . "\n";
                    }
                }

                // Top 3 channels
                $topChannels = $db->table('sales_data')
                    ->selectRaw('channel_name, SUM(sales) as total_sales')
                    ->where('year', $this->year)
                    ->whereNotNull('channel_name')
                    ->groupBy('channel_name')
                    ->orderByDesc('total_sales')
                    ->limit(3)
                    ->get();

                if ($topChannels->isNotEmpty()) {
                    $overall .= "Top Channels: " . $topChannels->map(fn($c) => "{$c->channel_name} PHP " . number_format($c->total_sales, 0))->implode(' | ') . "\n";
                }
            }
        } catch (\Throwable) {
            $overall = "Overall sales data unavailable.\n";
        }

        if (!$account) {
            return $overall ?: "No sales data available for {$this->year}.";
        }

        // Per-account: aggregate SQL detail for this specific account
        $perAccount = "== Per Account: [{$account->account_code}] {$account->short_name} ==\n";

        try {
            $db = DB::connection('sqlite_reports');

            $acctSummary = $db->table('sales_data')
                ->selectRaw('SUM(sales) as total_sales, SUM(quantity) as total_qty, COUNT(DISTINCT customer_code) as customers, COUNT(DISTINCT stock_code) as skus')
                ->where('year', $this->year)
                ->where('account_code', $account->account_code)
                ->first();

            if ($acctSummary && $acctSummary->total_sales > 0) {
                $perAccount .= "Total Sales: PHP " . number_format($acctSummary->total_sales, 2) . " | Qty: " . number_format($acctSummary->total_qty) . " pcs | Customers: {$acctSummary->customers} | SKUs: {$acctSummary->skus}\n";

                // Top 5 SKUs for this account
                $acctSkus = $db->table('sales_data')
                    ->selectRaw('stock_code, description, SUM(sales) as total_sales, SUM(quantity) as total_qty')
                    ->where('year', $this->year)
                    ->where('account_code', $account->account_code)
                    ->groupBy('stock_code', 'description')
                    ->orderByDesc('total_sales')
                    ->limit(5)
                    ->get();

                if ($acctSkus->isNotEmpty()) {
                    $perAccount .= "Top SKUs:\n";
                    foreach ($acctSkus as $i => $s) {
                        $perAccount .= ($i + 1) . ". [{$s->stock_code}] {$s->description} — PHP " . number_format($s->total_sales, 2) . ", " . number_format($s->total_qty) . " pcs\n";
                    }
                }

                // Top 5 customers for this account
                $acctCustomers = $db->table('sales_data')
                    ->selectRaw('customer_code, customer_name, SUM(sales) as total_sales')
                    ->where('year', $this->year)
                    ->where('account_code', $account->account_code)
                    ->groupBy('customer_code', 'customer_name')
                    ->orderByDesc('total_sales')
                    ->limit(5)
                    ->get();

                if ($acctCustomers->isNotEmpty()) {
                    $perAccount .= "Top Customers:\n";
                    foreach ($acctCustomers as $i => $c) {
                        $perAccount .= ($i + 1) . ". [{$c->customer_code}] {$c->customer_name} — PHP " . number_format($c->total_sales, 2) . "\n";
                    }
                }
            }
        } catch (\Throwable) {
            // account-specific SQL failed; RAG chunks below will still provide context
        }

        // Supplement with RAG chunks for narrative context
        $chunks = $this->retrieveRagChunks($account->account_code, [
            "monthly sales performance {$this->year}",
            "top selling SKU product revenue {$this->year}",
            "distributor channel sales trend {$this->year}",
        ]);

        if (!empty($chunks)) {
            $perAccount .= "Additional context:\n" . implode("\n", $chunks) . "\n";
        }

        return $overall . "\n" . $perAccount;
    }

    private function buildInventoryContext(): string
    {
        $account = session('account');
        $overall = '';

        try {
            $db = DB::connection('sqlite_reports');

            $inv = $db->table('inventory_data')
                ->selectRaw('COUNT(DISTINCT stock_code) as skus, COUNT(DISTINCT location_code) as locations, SUM(total) as total_units')
                ->first();

            if ($inv && $inv->skus) {
                $overall  = "== Overall Inventory ==\n";
                $overall .= "SKUs: {$inv->skus} | Locations: {$inv->locations} | Units: " . number_format($inv->total_units) . "\n";

                // Expiry counts
                $criticalItems = $db->table('inventory_aging')
                    ->selectRaw('stock_code, description, SUM(inventory) as total_units, MIN(expiry_date) as earliest_expiry')
                    ->whereRaw("expiry_date <= date('now','+30 days') AND expiry_date >= date('now')")
                    ->groupBy('stock_code', 'description')
                    ->orderBy('earliest_expiry')
                    ->limit(5)
                    ->get();

                $nearExpiry = $db->table('inventory_aging')
                    ->whereRaw("expiry_date <= date('now','+90 days') AND expiry_date >= date('now')")
                    ->count();

                $expired = $db->table('inventory_aging')
                    ->whereRaw("expiry_date < date('now')")
                    ->count();

                $overall .= "Expiry — Critical (≤30d): {$criticalItems->count()} SKU(s) | Near (≤90d): {$nearExpiry} | Expired: {$expired}\n";

                if ($criticalItems->isNotEmpty()) {
                    $overall .= "Critical Expiry SKUs (≤30 days):\n";
                    foreach ($criticalItems as $i => $item) {
                        $overall .= ($i + 1) . ". [{$item->stock_code}] {$item->description} — " . number_format($item->total_units) . " units, expires {$item->earliest_expiry}\n";
                    }
                }

                // Top 5 SKUs by stock volume
                $topStock = $db->table('inventory_data')
                    ->selectRaw('stock_code, description, SUM(total) as total_units')
                    ->groupBy('stock_code', 'description')
                    ->orderByDesc('total_units')
                    ->limit(5)
                    ->get();

                if ($topStock->isNotEmpty()) {
                    $overall .= "Top SKUs by Volume:\n";
                    foreach ($topStock as $i => $s) {
                        $overall .= ($i + 1) . ". [{$s->stock_code}] {$s->description} — " . number_format($s->total_units) . " units\n";
                    }
                }
            }
        } catch (\Throwable) {
            $overall = "Overall inventory data unavailable.\n";
        }

        if (!$account) {
            return $overall ?: "No inventory data available.";
        }

        // Per-account: specific SKU detail from SQL
        $perAccount = "== Per Account: [{$account->account_code}] {$account->short_name} ==\n";

        try {
            $db = DB::connection('sqlite_reports');

            $acctCritical = $db->table('inventory_aging')
                ->selectRaw('stock_code, description, SUM(inventory) as total_units, MIN(expiry_date) as earliest_expiry')
                ->where('account_code', $account->account_code)
                ->whereRaw("expiry_date <= date('now','+30 days') AND expiry_date >= date('now')")
                ->groupBy('stock_code', 'description')
                ->orderBy('earliest_expiry')
                ->limit(5)
                ->get();

            if ($acctCritical->isNotEmpty()) {
                $perAccount .= "Critical Expiry (≤30 days):\n";
                foreach ($acctCritical as $i => $item) {
                    $perAccount .= ($i + 1) . ". [{$item->stock_code}] {$item->description} — " . number_format($item->total_units) . " units, expires {$item->earliest_expiry}\n";
                }
            }

            $acctStock = $db->table('inventory_data')
                ->selectRaw('stock_code, description, SUM(total) as total_units')
                ->where('account_code', $account->account_code)
                ->groupBy('stock_code', 'description')
                ->orderByDesc('total_units')
                ->limit(5)
                ->get();

            if ($acctStock->isNotEmpty()) {
                $perAccount .= "Top SKUs by Volume:\n";
                foreach ($acctStock as $i => $s) {
                    $perAccount .= ($i + 1) . ". [{$s->stock_code}] {$s->description} — " . number_format($s->total_units) . " units\n";
                }
            }
        } catch (\Throwable) {
            // fall through to RAG
        }

        $chunks = $this->retrieveRagChunks($account->account_code, [
            "inventory stock levels expiry aging",
            "near expiry items critical stock",
            "out of stock OOS inventory",
        ]);

        if (!empty($chunks)) {
            $perAccount .= "Additional context:\n" . implode("\n", $chunks) . "\n";
        }

        return $overall . "\n" . $perAccount;
    }

    /**
     * Run multiple RAG queries, deduplicate results, return top chunks.
     *
     * @param  string[] $queries
     * @return string[]
     */
    private function retrieveRagChunks(string $accountCode, array $queries): array
    {
        $rag = app(RagService::class);

        return collect($queries)
            ->flatMap(fn(string $q) => $rag->retrieve($q, $accountCode, topK: 5))
            ->unique()
            ->values()
            ->all();
    }
};
?>

<div class="ai-insight-card">

    {{-- ── HEADER ──────────────────────────────────────────────────────────── --}}
    <div class="ai-insight-header">
        <div class="ai-insight-meta">
            <div class="ai-insight-icon">
                <i class="fas fa-robot"></i>
            </div>
            <div>
                <div class="ai-insight-label">AI Insight</div>
                <div class="ai-insight-title">
                    {{ $year }} &mdash; {{ $type === 'sales' ? 'Sales Performance' : 'Inventory &amp; Supply Chain' }}
                </div>
                @if($hasGenerated)
                    <div class="ai-insight-scope-badge {{ $scope === 'Overall' ? 'ai-insight-scope-badge--overall' : 'ai-insight-scope-badge--account' }}">
                        <i class="fas {{ $scope === 'Overall' ? 'fa-globe' : 'fa-building' }}"></i>
                        {{ $scope }}
                    </div>
                @endif
            </div>
        </div>

        <button
            type="button"
            class="ai-insight-btn {{ $hasGenerated ? 'ai-insight-btn--regen' : '' }}"
            wire:click="generateInsight"
            wire:loading.attr="disabled"
            wire:target="generateInsight"
        >
            <span wire:loading.remove wire:target="generateInsight">
                <i class="fas {{ $hasGenerated ? 'fa-sync-alt' : 'fa-lightbulb' }}"></i>
                {{ $hasGenerated ? 'Regenerate' : 'Generate Insight' }}
            </span>
            <span wire:loading wire:target="generateInsight" style="display:none;">
                <i class="fas fa-circle-notch fa-spin"></i> Analysing…
            </span>
        </button>
    </div>

    {{-- ── BODY ─────────────────────────────────────────────────────────────── --}}
    @if($isGenerating && !$hasGenerated)

        {{-- Loading state --}}
        <div class="ai-insight-loading">
            <div class="ai-insight-pulse"></div>
            <div class="ai-insight-pulse ai-insight-pulse--delay"></div>
            <div class="ai-insight-pulse ai-insight-pulse--delay2"></div>
            <span>Ollama is analysing your data&hellip;</span>
        </div>

    @elseif($hasGenerated && $insight)

        {{-- Result — parse into labelled sections --}}
        @php
            $sectionDefs = [
                'Key Trends'      => ['icon' => 'fa-chart-line',          'cls' => 'trends'],
                'Risks'           => ['icon' => 'fa-exclamation-triangle', 'cls' => 'risks'],
                'Recommendations' => ['icon' => 'fa-lightbulb',           'cls' => 'recs'],
            ];

            // Split raw text into named sections
            $sections = [];
            $current  = null;
            foreach (explode("\n", $this->insight) as $line) {
                $trimmed = trim($line);
                $matched = false;
                foreach (array_keys($sectionDefs) as $header) {
                    if (stripos($trimmed, $header) !== false && strlen($trimmed) <= strlen($header) + 2) {
                        $current        = $header;
                        $sections[$current] = [];
                        $matched        = true;
                        break;
                    }
                }
                if (!$matched && $current !== null && $trimmed !== '') {
                    // Strip leading bullet characters
                    $sections[$current][] = ltrim($trimmed, '•·-– ');
                }
            }

            // If parsing found nothing (unexpected model format), fall back
            $parseFailed = empty(array_filter($sections));
        @endphp

        @if($parseFailed)
            <div class="ai-insight-body">
                {!! nl2br(e($insight)) !!}
            </div>
        @else
            <div class="ai-insight-sections">
                @foreach($sectionDefs as $header => $def)
                    @if(!empty($sections[$header]))
                        <div class="ai-insight-section ai-insight-section--{{ $def['cls'] }}">
                            <div class="ai-insight-section-header">
                                <span class="ai-insight-section-icon">
                                    <i class="fas {{ $def['icon'] }}"></i>
                                </span>
                                <span class="ai-insight-section-title">{{ $header }}</span>
                            </div>
                            <ul class="ai-insight-bullets">
                                @foreach($sections[$header] as $bullet)
                                    <li>{{ $bullet }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif

    @else

        {{-- Empty state --}}
        <div class="ai-insight-empty">
            <i class="fas fa-lightbulb ai-insight-empty-icon"></i>
            <p>Click <strong>Generate Insight</strong> for an AI-powered analysis of this data.</p>
        </div>

    @endif
</div>

@once
<style>
.ai-insight-card {
    background: var(--glass-light, rgba(255,255,255,0.72));
    backdrop-filter: var(--glass-blur-sm, blur(16px) saturate(1.6));
    -webkit-backdrop-filter: var(--glass-blur-sm, blur(16px) saturate(1.6));
    border: 1px solid var(--glass-border, rgba(255,255,255,0.6));
    border-radius: var(--radius, 18px);
    box-shadow: var(--shadow, 0 2px 12px rgba(0,0,0,.06), 0 8px 32px rgba(0,0,0,.08));
    overflow: hidden;
    margin-bottom: 20px;
    transition: box-shadow .2s;
}
.ai-insight-card:hover {
    box-shadow: var(--shadow-lg, 0 4px 24px rgba(0,0,0,.1), 0 16px 48px rgba(0,0,0,.14));
}

/* Header */
.ai-insight-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 14px 18px;
    background: linear-gradient(135deg, rgba(10,132,255,.10) 0%, rgba(94,92,230,.07) 100%);
    border-bottom: 1px solid rgba(10,132,255,.12);
}
.ai-insight-meta {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
}
.ai-insight-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: var(--col-accent-g, linear-gradient(135deg,#0a84ff 0%,#5e5ce6 100%));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .9rem;
    color: #fff;
    flex-shrink: 0;
    box-shadow: 0 4px 14px rgba(10,132,255,.35);
}
.ai-insight-label {
    font-family: "Roboto", sans-serif;
    font-size: .6rem;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: var(--col-subtle, #8e8e93);
    line-height: 1;
    margin-bottom: 3px;
}
.ai-insight-title {
    font-family: "Roboto", sans-serif;
    font-size: .8rem;
    font-weight: 600;
    color: var(--col-dark, #1c1c1e);
    line-height: 1.2;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Button */
.ai-insight-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 14px;
    border: none;
    border-radius: 10px;
    font-family: "Roboto", sans-serif;
    font-size: .75rem;
    font-weight: 600;
    cursor: pointer;
    white-space: nowrap;
    flex-shrink: 0;
    transition: transform .15s, box-shadow .15s, opacity .15s;
    background: var(--col-accent-g, linear-gradient(135deg,#0a84ff 0%,#5e5ce6 100%));
    color: #fff;
    box-shadow: 0 2px 10px rgba(10,132,255,.35);
}
.ai-insight-btn--regen {
    background: rgba(10,132,255,.1);
    color: var(--col-accent, #0a84ff);
    box-shadow: none;
    border: 1px solid rgba(10,132,255,.25);
}
.ai-insight-btn:hover:not([disabled]) { transform: translateY(-1px); box-shadow: 0 4px 16px rgba(10,132,255,.4); }
.ai-insight-btn--regen:hover:not([disabled]) { background: rgba(10,132,255,.15); box-shadow: none; }
.ai-insight-btn[disabled] { opacity: .55; cursor: not-allowed; transform: none; }

/* Loading */
.ai-insight-loading {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 18px 20px;
    font-family: "Roboto", sans-serif;
    font-size: .8rem;
    color: var(--col-subtle, #8e8e93);
}
.ai-insight-pulse {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: var(--col-accent, #0a84ff);
    animation: ai-pulse 1.2s ease-in-out infinite;
    flex-shrink: 0;
}
.ai-insight-pulse--delay  { animation-delay: .2s; }
.ai-insight-pulse--delay2 { animation-delay: .4s; }
@keyframes ai-pulse {
    0%, 100% { opacity: .25; transform: scale(.85); }
    50%       { opacity: 1;   transform: scale(1.15); }
}

/* Scope badge */
.ai-insight-scope-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    margin-top: 4px;
    padding: 2px 8px;
    border-radius: 20px;
    font-family: "Roboto", sans-serif;
    font-size: .6rem;
    font-weight: 700;
    letter-spacing: .06em;
    text-transform: uppercase;
}
.ai-insight-scope-badge--overall {
    background: rgba(94,92,230,.12);
    color: #5e5ce6;
    border: 1px solid rgba(94,92,230,.25);
}
.ai-insight-scope-badge--account {
    background: rgba(52,199,89,.12);
    color: #1a8c3e;
    border: 1px solid rgba(52,199,89,.3);
}
.dark-mode .ai-insight-scope-badge--overall { color: #9d9bff; background: rgba(94,92,230,.2); }
.dark-mode .ai-insight-scope-badge--account { color: #34c759; background: rgba(52,199,89,.15); }

/* Body — fallback plain text */
.ai-insight-body {
    padding: 14px 18px;
    font-family: "Roboto", sans-serif;
    font-size: .8rem;
    line-height: 1.5;
    color: var(--col-dark, #1c1c1e);
    white-space: pre-line;
}

/* Sections wrapper */
.ai-insight-sections {
    display: flex;
    flex-direction: column;
    gap: 0;
}

/* Individual section */
.ai-insight-section {
    padding: 12px 18px;
    border-top: 1px solid rgba(0,0,0,.05);
    position: relative;
}
.ai-insight-section:first-child { border-top: none; }

/* Coloured left accent bar */
.ai-insight-section::before {
    content: '';
    position: absolute;
    left: 0; top: 0; bottom: 0;
    width: 3px;
    border-radius: 0 2px 2px 0;
}
.ai-insight-section--trends::before  { background: #0a84ff; }
.ai-insight-section--risks::before   { background: #ff9f0a; }
.ai-insight-section--recs::before    { background: #34c759; }

/* Section header row */
.ai-insight-section-header {
    display: flex;
    align-items: center;
    gap: 7px;
    margin-bottom: 7px;
}
.ai-insight-section-icon {
    width: 22px;
    height: 22px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .65rem;
    flex-shrink: 0;
}
.ai-insight-section--trends .ai-insight-section-icon { background: rgba(10,132,255,.12);  color: #0a84ff; }
.ai-insight-section--risks  .ai-insight-section-icon { background: rgba(255,159,10,.12);  color: #d4780a; }
.ai-insight-section--recs   .ai-insight-section-icon { background: rgba(52,199,89,.12);   color: #1e8c3a; }

.ai-insight-section-title {
    font-family: "Roboto", sans-serif;
    font-size: .65rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
}
.ai-insight-section--trends .ai-insight-section-title { color: #0a84ff; }
.ai-insight-section--risks  .ai-insight-section-title { color: #d4780a; }
.ai-insight-section--recs   .ai-insight-section-title { color: #1e8c3a; }

/* Bullet list */
.ai-insight-bullets {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 5px;
}
.ai-insight-bullets li {
    font-family: "Roboto", sans-serif;
    font-size: .78rem;
    line-height: 1.45;
    color: var(--col-dark, #1c1c1e);
    display: flex;
    align-items: flex-start;
    gap: 7px;
}
.ai-insight-bullets li::before {
    content: '';
    width: 5px;
    height: 5px;
    border-radius: 50%;
    flex-shrink: 0;
    margin-top: 5px;
}
.ai-insight-section--trends .ai-insight-bullets li::before { background: #0a84ff; }
.ai-insight-section--risks  .ai-insight-bullets li::before { background: #ff9f0a; }
.ai-insight-section--recs   .ai-insight-bullets li::before { background: #34c759; }

/* Dark mode — sections */
.dark-mode .ai-insight-section { border-top-color: rgba(255,255,255,.06); }
.dark-mode .ai-insight-bullets li { color: rgba(255,255,255,.85); }
.dark-mode .ai-insight-section--trends .ai-insight-section-icon { background: rgba(10,132,255,.2); }
.dark-mode .ai-insight-section--risks  .ai-insight-section-icon { background: rgba(255,159,10,.2); color: #ff9f0a; }
.dark-mode .ai-insight-section--recs   .ai-insight-section-icon { background: rgba(52,199,89,.2); color: #34c759; }
.dark-mode .ai-insight-section--risks  .ai-insight-section-title { color: #ff9f0a; }
.dark-mode .ai-insight-section--recs   .ai-insight-section-title { color: #34c759; }

/* Empty state */
.ai-insight-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 28px 20px;
    text-align: center;
    color: var(--col-subtle, #8e8e93);
    font-family: "Roboto", sans-serif;
    font-size: .8rem;
}
.ai-insight-empty-icon {
    font-size: 1.6rem;
    margin-bottom: 10px;
    opacity: .35;
    color: var(--col-accent, #0a84ff);
}
.ai-insight-empty p { margin: 0; }
.ai-insight-empty strong { color: var(--col-dark, #1c1c1e); }

/* Dark mode */
.dark-mode .ai-insight-card {
    background: rgba(30,30,32,.72);
    border-color: rgba(255,255,255,.08);
}
.dark-mode .ai-insight-header {
    background: linear-gradient(135deg, rgba(10,132,255,.14) 0%, rgba(94,92,230,.1) 100%);
    border-bottom-color: rgba(10,132,255,.18);
}
.dark-mode .ai-insight-title { color: rgba(255,255,255,.9); }
.dark-mode .ai-insight-body  { color: rgba(255,255,255,.85); }
.dark-mode .ai-insight-empty strong { color: rgba(255,255,255,.9); }
.dark-mode .ai-insight-btn--regen {
    background: rgba(10,132,255,.15);
    color: #409cff;
    border-color: rgba(64,156,255,.3);
}
</style>
@endonce
