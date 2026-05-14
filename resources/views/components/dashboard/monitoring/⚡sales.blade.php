<?php

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithoutUrlPagination;
use Livewire\Attributes\Computed;
use App\Models\Account;
use App\Models\AccountDatabase;
use Illuminate\Support\Facades\DB;

new class extends Component
{
    use WithPagination, WithoutUrlPagination;
    protected $paginationTheme = 'bootstrap';

    public string $year = '';

    public function mount(): void
    {
        $this->year = date('Y');
    }

    #[Computed]
    public function accounts()
    {
        return Account::where('id', '>=', 10)
            ->paginate(10);
    }

    public function checkSalesStatus(int $account_id, int $month): string
    {
        $db_connection = AccountDatabase::where('account_id', $account_id)->first();

        try {
            $check = DB::connection($db_connection->connection_name)
                ->table('sales')
                ->where(DB::raw('MONTH(date)'), $month)
                ->where(DB::raw('YEAR(date)'), $this->year)
                ->first();

            return $check ? 'Has Data' : 'No Data';
        } catch (\Exception $e) {
            return 'No Data';
        }
    }

    public function checkInventoryStatus(int $account_id, int $month): string
    {
        $db_connection = AccountDatabase::where('account_id', $account_id)->first();

        try {
            $check = DB::connection($db_connection->connection_name)
                ->table('inventory_uploads')
                ->where(DB::raw('MONTH(date)'), $month)
                ->where(DB::raw('YEAR(date)'), $this->year)
                ->first();

            return $check ? 'Has Data' : 'No Data';
        } catch (\Exception $e) {
            return 'No Data';
        }
    }
};
?>

@once
<style>
/* ── Wrapper ──────────────────────────────────────────── */
.mon-wrap {
    font-family: "Roboto", sans-serif;
}

/* ── Card ─────────────────────────────────────────────── */
.mon-card {
    background: var(--glass-light, rgba(255,255,255,.72));
    backdrop-filter: var(--glass-blur-sm, blur(16px) saturate(1.6));
    -webkit-backdrop-filter: var(--glass-blur-sm, blur(16px) saturate(1.6));
    border: 1px solid var(--glass-border, rgba(255,255,255,.6));
    border-radius: var(--radius, 18px);
    box-shadow: var(--shadow, 0 2px 12px rgba(0,0,0,.06), 0 8px 32px rgba(0,0,0,.08));
    overflow: hidden;
    transition: box-shadow .2s;
}
.mon-card:hover {
    box-shadow: var(--shadow-lg, 0 4px 24px rgba(0,0,0,.10), 0 16px 48px rgba(0,0,0,.14));
}

/* ── Header ───────────────────────────────────────────── */
.mon-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 14px 18px;
    background: linear-gradient(135deg, rgba(10,132,255,.10) 0%, rgba(94,92,230,.07) 100%);
    border-bottom: 1px solid rgba(10,132,255,.12);
    flex-wrap: wrap;
}
.mon-card-title-group {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
}
.mon-card-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: linear-gradient(135deg, #0a84ff 0%, #5e5ce6 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .9rem;
    color: #fff;
    flex-shrink: 0;
    box-shadow: 0 4px 14px rgba(10,132,255,.35);
}
.mon-card-label {
    font-size: .6rem;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: var(--col-subtle, #8e8e93);
    line-height: 1;
    margin-bottom: 3px;
}
.mon-card-title {
    font-size: .85rem;
    font-weight: 600;
    color: var(--col-dark, #1c1c1e);
    line-height: 1.2;
}
.mon-spinner { color: var(--col-accent, #0a84ff); margin-left: 5px; }

/* ── Year nav ─────────────────────────────────────────── */
.mon-year-nav {
    display: flex;
    align-items: center;
    gap: 4px;
    background: rgba(0,0,0,.04);
    border: 1px solid rgba(0,0,0,.08);
    border-radius: 10px;
    padding: 3px 4px;
}
.mon-year-btn {
    width: 28px;
    height: 28px;
    border-radius: 7px;
    border: none;
    background: transparent;
    color: var(--col-accent, #0a84ff);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .7rem;
    transition: background .15s;
}
.mon-year-btn:hover:not([disabled]) { background: rgba(10,132,255,.12); }
.mon-year-btn[disabled] { opacity: .4; cursor: not-allowed; }
.mon-year-input {
    width: 58px;
    text-align: center;
    border: none;
    background: transparent;
    font-family: "Roboto", sans-serif;
    font-size: .8rem;
    font-weight: 600;
    color: var(--col-dark, #1c1c1e);
    outline: none;
}
/* hide spinner arrows */
.mon-year-input::-webkit-inner-spin-button,
.mon-year-input::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
.mon-year-input[type=number] { -moz-appearance: textfield; }

/* ── Legend ───────────────────────────────────────────── */
.mon-legend {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 8px 18px;
    border-bottom: 1px solid rgba(0,0,0,.05);
    font-size: .7rem;
    color: var(--col-subtle, #8e8e93);
    flex-wrap: wrap;
}
.mon-legend-item { display: flex; align-items: center; gap: 5px; }
.mon-legend-abbr { margin-left: auto; }
.mon-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
    flex-shrink: 0;
}
.mon-dot--ok   { background: #34c759; }
.mon-dot--none { background: rgba(0,0,0,.15); }

/* ── Table wrapper (horizontal scroll) ───────────────── */
.mon-table-wrap {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

/* ── Table ────────────────────────────────────────────── */
.mon-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    font-size: .72rem;
}

/* Sticky columns */
.mon-th-sticky,
.mon-td-sticky {
    position: sticky;
    background: var(--glass-light, rgba(255,255,255,.92));
    z-index: 2;
}
.mon-th-account,
.mon-td-code { left: 0; min-width: 72px; }
.mon-th-name,
.mon-td-name { left: 72px; min-width: 120px; border-right: 2px solid rgba(10,132,255,.15); }

/* Header cells */
.mon-th-sticky,
.mon-th-month,
.mon-th-sub {
    padding: 7px 4px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: var(--col-subtle, #8e8e93);
    border-bottom: 1px solid rgba(0,0,0,.07);
    white-space: nowrap;
    text-align: center;
    background: rgba(250,250,252,.95);
}
.mon-th-account { text-align: left; padding-left: 14px; }
.mon-th-name    { text-align: left; padding-left: 10px; }

.mon-th-month {
    font-size: .65rem;
    padding: 6px 3px 4px;
    border-left: 1px solid rgba(0,0,0,.05);
    position: relative;
}
.mon-th-month--current {
    color: #0a84ff;
    background: rgba(10,132,255,.06) !important;
}
.mon-current-dot {
    display: inline-block;
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background: #0a84ff;
    vertical-align: middle;
    margin-left: 3px;
    margin-bottom: 1px;
}

.mon-sub-row th { border-bottom: 2px solid rgba(0,0,0,.1); }
.mon-th-sub {
    font-size: .6rem;
    color: var(--col-subtle, #b0b0b8);
    padding: 3px 2px 5px;
    border-left: none;
    font-weight: 500;
    letter-spacing: .04em;
}
.mon-th-sub--current { background: rgba(10,132,255,.06) !important; color: #0a84ff; }

/* Body rows */
.mon-row {
    transition: background .12s;
}
.mon-row:hover .mon-td,
.mon-row:hover .mon-td-sticky {
    background: rgba(10,132,255,.04);
}

.mon-td-code,
.mon-td-name {
    padding: 6px 8px;
    border-bottom: 1px solid rgba(0,0,0,.04);
    color: var(--col-dark, #1c1c1e);
    white-space: nowrap;
}
.mon-td-code {
    font-weight: 700;
    font-size: .7rem;
    color: var(--col-accent, #0a84ff);
    letter-spacing: .03em;
    padding-left: 14px;
}
.mon-td-name { font-size: .72rem; }

.mon-td {
    padding: 5px 3px;
    text-align: center;
    border-bottom: 1px solid rgba(0,0,0,.04);
    border-left: 1px solid rgba(0,0,0,.025);
}
.mon-td--current {
    background: rgba(10,132,255,.04);
}

/* ── Badges ───────────────────────────────────────────── */
.mon-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 20px;
    height: 20px;
    border-radius: 6px;
    font-size: .6rem;
    line-height: 1;
    font-weight: 700;
}
.mon-badge--ok {
    background: rgba(52,199,89,.15);
    color: #1a8c3e;
    border: 1px solid rgba(52,199,89,.3);
}
.mon-badge--none {
    background: rgba(0,0,0,.05);
    color: rgba(0,0,0,.2);
    border: 1px solid rgba(0,0,0,.07);
}

/* ── Footer ───────────────────────────────────────────── */
.mon-footer {
    padding: 10px 16px;
    border-top: 1px solid rgba(0,0,0,.06);
    display: flex;
    align-items: center;
    justify-content: flex-end;
}
.mon-footer .pagination { margin: 0; }

/* ── Dark mode ────────────────────────────────────────── */
.dark-mode .mon-card {
    background: rgba(30,30,32,.72);
    border-color: rgba(255,255,255,.08);
}
.dark-mode .mon-card-header {
    background: linear-gradient(135deg, rgba(10,132,255,.14) 0%, rgba(94,92,230,.10) 100%);
    border-bottom-color: rgba(10,132,255,.18);
}
.dark-mode .mon-card-title { color: rgba(255,255,255,.9); }
.dark-mode .mon-year-nav { background: rgba(255,255,255,.06); border-color: rgba(255,255,255,.1); }
.dark-mode .mon-year-input { color: rgba(255,255,255,.9); }
.dark-mode .mon-th-sticky,
.dark-mode .mon-td-sticky { background: rgba(28,28,30,.95); }
.dark-mode .mon-th-month,
.dark-mode .mon-th-sub { background: rgba(28,28,30,.95); color: rgba(255,255,255,.4); }
.dark-mode .mon-th-month--current { background: rgba(10,132,255,.12) !important; color: #409cff; }
.dark-mode .mon-th-sub--current   { background: rgba(10,132,255,.10) !important; color: #409cff; }
.dark-mode .mon-td-code { color: #409cff; }
.dark-mode .mon-td-name { color: rgba(255,255,255,.8); }
.dark-mode .mon-td { border-bottom-color: rgba(255,255,255,.04); border-left-color: rgba(255,255,255,.03); }
.dark-mode .mon-td--current { background: rgba(10,132,255,.08); }
.dark-mode .mon-row:hover .mon-td,
.dark-mode .mon-row:hover .mon-td-sticky { background: rgba(10,132,255,.07); }
.dark-mode .mon-badge--ok   { background: rgba(52,199,89,.18); color: #34c759; border-color: rgba(52,199,89,.3); }
.dark-mode .mon-badge--none { background: rgba(255,255,255,.05); color: rgba(255,255,255,.18); border-color: rgba(255,255,255,.08); }
.dark-mode .mon-legend      { border-bottom-color: rgba(255,255,255,.05); }
.dark-mode .mon-dot--none   { background: rgba(255,255,255,.2); }
.dark-mode .mon-footer      { border-top-color: rgba(255,255,255,.06); }
</style>
@endonce

<div class="mon-wrap">

    {{-- ── CARD ───────────────────────────────────────────────────── --}}
    <div class="mon-card">

        {{-- ── HEADER ─────────────────────────────────────────────── --}}
        <div class="mon-card-header">
            <div class="mon-card-title-group">
                <div class="mon-card-icon">
                    <i class="fa fa-binoculars"></i>
                </div>
                <div>
                    <div class="mon-card-label">ACCOUNT MONITORING</div>
                    <div class="mon-card-title">
                        Sales &amp; Inventory Status
                        <i class="fa fa-spinner fa-spin mon-spinner" wire:loading></i>
                    </div>
                </div>
            </div>

            {{-- Year navigator --}}
            <div class="mon-year-nav">
                <button class="mon-year-btn"
                    wire:click="$set('year', {{ (int)$this->year - 1 }})"
                    wire:loading.attr="disabled"
                    title="Previous year">
                    <i class="fa fa-chevron-left"></i>
                </button>
                <input type="number"
                    class="mon-year-input"
                    wire:model.live="year"
                    min="2000" max="2100"
                    placeholder="Year">
                <button class="mon-year-btn"
                    wire:click="$set('year', {{ (int)$this->year + 1 }})"
                    wire:loading.attr="disabled"
                    title="Next year">
                    <i class="fa fa-chevron-right"></i>
                </button>
            </div>
        </div>

        {{-- ── LEGEND ──────────────────────────────────────────────── --}}
        <div class="mon-legend">
            <span class="mon-legend-item">
                <span class="mon-dot mon-dot--ok"></span> Has Data
            </span>
            <span class="mon-legend-item">
                <span class="mon-dot mon-dot--none"></span> No Data
            </span>
            <span class="mon-legend-item mon-legend-abbr">
                <strong>S</strong> = Sales &nbsp;|&nbsp; <strong>I</strong> = Inventory
            </span>
        </div>

        {{-- ── TABLE ──────────────────────────────────────────────── --}}
        <div class="mon-table-wrap">
            <table class="mon-table">
                <thead>
                    <tr>
                        <th class="mon-th-sticky mon-th-account">CODE</th>
                        <th class="mon-th-sticky mon-th-name">NAME</th>
                        @php $currentMonth = (int) date('m'); $currentYear = date('Y'); @endphp
                        @foreach(['JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC'] as $idx => $mon)
                            @php $isCurrent = $this->year == $currentYear && $idx + 1 === $currentMonth; @endphp
                            <th colspan="2" class="mon-th-month {{ $isCurrent ? 'mon-th-month--current' : '' }}">
                                {{ $mon }}
                                @if($isCurrent)<span class="mon-current-dot"></span>@endif
                            </th>
                        @endforeach
                    </tr>
                    <tr class="mon-sub-row">
                        <th class="mon-th-sticky mon-th-account"></th>
                        <th class="mon-th-sticky mon-th-name"></th>
                        @foreach(range(1, 12) as $m)
                            @php $isCurrent = $this->year == $currentYear && $m === $currentMonth; @endphp
                            <th class="mon-th-sub {{ $isCurrent ? 'mon-th-sub--current' : '' }}">S</th>
                            <th class="mon-th-sub {{ $isCurrent ? 'mon-th-sub--current' : '' }}">I</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($this->accounts as $account)
                        <tr class="mon-row" wire:key="account-{{ $account->id }}">
                            <td class="mon-td-sticky mon-td-code">{{ $account->account_code }}</td>
                            <td class="mon-td-sticky mon-td-name">{{ $account->short_name }}</td>
                            @for($m = 1; $m <= 12; $m++)
                                @php $isCurrent = $this->year == $currentYear && $m === $currentMonth; @endphp
                                <td class="mon-td {{ $isCurrent ? 'mon-td--current' : '' }}">
                                    @if($this->checkSalesStatus($account->id, $m) === 'Has Data')
                                        <span class="mon-badge mon-badge--ok"><i class="fa fa-check"></i></span>
                                    @else
                                        <span class="mon-badge mon-badge--none"><i class="fa fa-minus"></i></span>
                                    @endif
                                </td>
                                <td class="mon-td {{ $isCurrent ? 'mon-td--current' : '' }}">
                                    @if($this->checkInventoryStatus($account->id, $m) === 'Has Data')
                                        <span class="mon-badge mon-badge--ok"><i class="fa fa-check"></i></span>
                                    @else
                                        <span class="mon-badge mon-badge--none"><i class="fa fa-minus"></i></span>
                                    @endif
                                </td>
                            @endfor
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- ── FOOTER / PAGINATION ─────────────────────────────────── --}}
        <div class="mon-footer">
            {{ $this->accounts->links(data: ['scrollTo' => false]) }}
        </div>

    </div>{{-- /.mon-card --}}
</div>
