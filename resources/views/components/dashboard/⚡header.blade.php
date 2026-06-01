<?php

use Livewire\Component;
use App\Http\Traits\SalesDataAggregator;
use App\Jobs\ConsolidateAccountDataJob;
use App\Models\Account;

use App\Exports\ReportsMultiSheetExport;
use Maatwebsite\Excel\Facades\Excel;

new class extends Component
{
    use SalesDataAggregator;

    public $type = 'sales';
    public $selected_tab = 'sales';
    public $globalYear;
    public $header_data = [];
    public $selected_account;

    /** Tab keys to show. All four are on by default. */
    // public array $enabledTabs = ['sales', 'inventories', 'accounts', 'trends'];
    public array $enabledTabs = ['sales', 'inventories', 'accounts'];

    /** Tracks which tabs have ever been visited so components aren't mounted until needed. */
    public array $initializedTabs = ['sales'];

    public function mount(): void
    {
        $this->globalYear = date('Y');
        $this->initializedTabs = array_slice($this->enabledTabs, 0, 1);
        $this->selected_tab = $this->initializedTabs[0] ?? 'sales';
        $this->getData();
    }

    public function selectType($type): void
    {
        $this->type = $type;
    }

    public function updatedGlobalYear(): void
    {
        $this->getData();
    }

    public function getData(): void
    {
        $group_data = collect($this->getYearlySalesData($this->globalYear))
            ->groupBy('account_name')
            ->map(function ($items) {
                $ubo = $items
                    ->groupBy('customer_code')
                    ->filter(fn($ubo_items) => $ubo_items->first()['customer_status'] == 0)
                    ->count();

                return [
                    'account' => $items->first()['account_name'],
                    'total'   => $items->sum('sales'),
                    'ubo'     => $ubo,
                ];
            });

        $this->header_data = [
            'distributors' => $group_data->count(),
            'sales'        => $group_data->sum('total'),
            'ubo'          => $group_data->sum('ubo'),
        ];
    }

    public function selectTab($tab): void
    {
        if (!in_array($tab, $this->enabledTabs)) {
            return;
        }

        $this->selected_tab = $tab;

        if (!in_array($tab, $this->initializedTabs)) {
            $this->initializedTabs[] = $tab;
        }
    }

    public function previousYear(): void
    {
        $this->globalYear--;
        $this->getData();
    }

    public function nextYear(): void
    {
        $this->globalYear++;
        $this->getData();
    }

    public function refreshData(): void
    {
        $jobs = Account::where('id', '>=', 10)
            ->get()
            ->map(fn(Account $account) => new ConsolidateAccountDataJob($account))
            ->all();

        Bus::chain($jobs)->dispatch();

        session()->flash('message', 'Data refresh queued. Reports will update shortly.');
    }

    public function exportData()
    {
        $fileName = 'Reports_' . $this->globalYear . '.xlsx';

        return Excel::download(new ReportsMultiSheetExport($this->globalYear), $fileName);
    }
};
?>

<div>

    {{-- ── TYPE SWITCHER ──────────────────────────────────────────── --}}
    <div class="dash-type-switcher">
        <button
            class="dash-type-btn {{ $this->type === 'sales' ? 'active' : '' }}"
            wire:click.prevent="selectType('sales')"
            wire:loading.attr="disabled"
            wire:target="selectType">
            <i class="fa fa-chart-bar"></i> REPORTS
            <i class="fa fa-spinner fa-spin ml-1" wire:loading wire:target="selectType('sales')"></i>
        </button>
        <button
            class="dash-type-btn {{ $this->type === 'account-monitoring' ? 'active' : '' }}"
            wire:click.prevent="selectType('account-monitoring')"
            wire:loading.attr="disabled"
            wire:target="selectType">
            <i class="fa fa-binoculars"></i> ACCOUNT MONITORING
            <i class="fa fa-spinner fa-spin ml-1" wire:loading wire:target="selectType('account-monitoring')"></i>
        </button>
    </div>

    @if($type === 'sales')

        {{-- ── ACTION BAR ─────────────────────────────────────────── --}}
        <div class="d-flex align-items-center gap-2 mb-2">
            <button class="btn-refresh" wire:click.prevent="refreshData" wire:loading.attr="disabled" wire:target="refreshData">
                <i class="fa fa-sync-alt" wire:loading.class="fa-spin" wire:target="refreshData"></i>
                REFRESH DATA
            </button>

            <button class="btn-refresh btn-primary ml-2" wire:click.prevent="exportData" wire:loading.attr="disabled" wire:target="exportData">
                <i class="fa fa-file-export mr-1"></i> EXPORT
                <i class="fa fa-spinner fa-spin ml-1" wire:loading wire:target="exportData"></i>
            </button>
        </div>

        {{-- ── TABS CARD ──────────────────────────────────────────── --}}
        <div class="dash-tabs-card">

            <ul class="dash-tab-nav" role="tablist">
                @if(in_array('sales', $enabledTabs))
                <li class="nav-item">
                    <a class="nav-link {{ $selected_tab === 'sales' ? 'active' : '' }}"
                       role="tab"
                       wire:click.prevent="selectTab('sales')"
                       href="#">
                        <i class="fa fa-chart-line mr-1"></i> SALES PERFORMANCE
                        <i class="fa fa-spinner fa-spin ml-1" wire:loading wire:target="selectTab('sales')"></i>
                    </a>
                </li>
                @endif
                @if(in_array('inventories', $enabledTabs))
                <li class="nav-item">
                    <a class="nav-link {{ $selected_tab === 'inventories' ? 'active' : '' }}"
                       role="tab"
                       wire:click.prevent="selectTab('inventories')"
                       href="#">
                        <i class="fa fa-boxes mr-1"></i> INVENTORY &amp; SUPPLY CHAIN
                        <i class="fa fa-spinner fa-spin ml-1" wire:loading wire:target="selectTab('inventories')"></i>
                    </a>
                </li>
                @endif
                @if(in_array('accounts', $enabledTabs))
                <li class="nav-item">
                    <a class="nav-link {{ $selected_tab === 'accounts' ? 'active' : '' }}"
                       role="tab"
                       wire:click.prevent="selectTab('accounts')"
                       href="#">
                        <i class="fa fa-building mr-1"></i> ACCOUNTS
                        <i class="fa fa-spinner fa-spin ml-1" wire:loading wire:target="selectTab('accounts')"></i>
                    </a>
                </li>
                @endif
                @if(in_array('trends', $enabledTabs))
                <li class="nav-item">
                    <a class="nav-link {{ $selected_tab === 'trends' ? 'active' : '' }}"
                       role="tab"
                       wire:click.prevent="selectTab('trends')"
                       href="#">
                        <i class="fa fa-chart-area mr-1"></i> TRENDS
                        <i class="fa fa-spinner fa-spin ml-1" wire:loading wire:target="selectTab('trends')"></i>
                    </a>
                </li>
                @endif
            </ul>

            <div class="dash-tab-body">

                {{-- ── TAB SWITCH SKELETON ────────────────────────── --}}
                <div class="tab-switch-sk" wire:loading wire:target="selectTab">
                    <div class="tab-switch-sk-row">
                        <div class="sk-stat-card"><div class="sk-line sk-line-sm"></div><div class="sk-line sk-line-lg"></div></div>
                        <div class="sk-stat-card"><div class="sk-line sk-line-sm"></div><div class="sk-line sk-line-lg"></div></div>
                        <div class="sk-stat-card"><div class="sk-line sk-line-sm"></div><div class="sk-line sk-line-lg"></div></div>
                        <div class="sk-stat-card"><div class="sk-line sk-line-sm"></div><div class="sk-line sk-line-lg"></div></div>
                    </div>
                    <div class="tab-switch-sk-grid">
                        <div class="sk-chart-card"></div>
                        <div class="sk-chart-card"></div>
                        <div class="sk-chart-card"></div>
                        <div class="sk-chart-card"></div>
                    </div>
                </div>

                <div wire:loading.remove wire:target="selectTab">

                {{-- ── SALES TAB ──────────────────────────────────── --}}
                @if(in_array('sales', $enabledTabs) && in_array('sales', $this->initializedTabs))
                    <div class="{{ $selected_tab === 'sales' ? '' : 'd-none' }}">

                        <livewire:dashboard.insight :year="$globalYear" type="sales" wire:key="insight-sales-{{ $globalYear }}" />

                        {{-- Stat cards + year navigator --}}
                        <div class="stat-row">
                            <div class="stat-card stat-col-3">
                                <div class="stat-card-icon"><i class="fa fa-dollar-sign"></i></div>
                                <div class="stat-card-body">
                                    <span class="stat-card-label">Gross Sales</span>
                                    <span class="stat-card-value" wire:loading.class="text-muted">
                                        {{ number_format($header_data['sales'] ?? 0, 2) }}
                                    </span>
                                </div>
                            </div>

                            <div class="stat-card stat-col-3">
                                <div class="stat-card-icon"><i class="fa fa-building"></i></div>
                                <div class="stat-card-body">
                                    <span class="stat-card-label">Total Distributors</span>
                                    <span class="stat-card-value" wire:loading.class="text-muted">
                                        {{ number_format($header_data['distributors'] ?? 0) }}
                                    </span>
                                </div>
                            </div>

                            <div class="stat-card stat-col-3">
                                <div class="stat-card-icon"><i class="fa fa-store"></i></div>
                                <div class="stat-card-body">
                                    <span class="stat-card-label">Number of Outlets</span>
                                    <span class="stat-card-value" wire:loading.class="text-muted">
                                        {{ number_format($header_data['ubo'] ?? 0) }}
                                    </span>
                                </div>
                            </div>

                            <div class="stat-year-wrap stat-col-3">
                                <span class="stat-year-label">Year</span>
                                <div class="stat-year-nav">
                                    <button class="stat-year-btn" wire:click="previousYear" wire:loading.attr="disabled" wire:target="previousYear,nextYear,updatedGlobalYear" title="Previous year">
                                        <i class="fa fa-chevron-left"></i>
                                    </button>
                                    <input type="number" class="stat-year-input" wire:model.live="globalYear" placeholder="Year">
                                    <button class="stat-year-btn" wire:click="nextYear" wire:loading.attr="disabled" wire:target="previousYear,nextYear,updatedGlobalYear" title="Next year">
                                        <i class="fa fa-chevron-right"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Charts row 1 --}}
                        <div class="row">
                            <div class="col-lg-6">
                                <livewire:dashboard.reports.sales-performance :year="$globalYear" />
                            </div>
                            <div class="col-lg-6">
                                <livewire:dashboard.reports.sales-brands :year="$globalYear" />
                            </div>
                            <div class="col-lg-6">
                                <livewire:dashboard.reports.sales-by-channel :year="$globalYear" />
                            </div>
                            <div class="col-lg-6">
                                {{-- <livewire:dashboard.reports.area :year="$globalYear" />
                                <livewire:dashboard.reports.top-distributor :year="$globalYear" /> --}}
                                <livewire:dashboard.accounts.sales-per-address :year="$globalYear" :account_id="$selected_account"/>
                            </div>
                        </div>

                        {{-- Charts row 2 --}}
                        <div class="row">
                            <div class="col-lg-5">
                                <livewire:dashboard.reports.sales-volume :year="$globalYear" />
                                <livewire:dashboard.reports.sales-sku :year="$globalYear" />
                            </div>
                            <div class="col-lg-7">
                                <livewire:dashboard.reports.ubo-matrix :year="$globalYear" />
                                <livewire:dashboard.reports.ubo :year="$globalYear" />
                            </div>
                        </div>
                    </div>
                @endif

                {{-- ── INVENTORIES TAB ────────────────────────────── --}}
                @if(in_array('inventories', $enabledTabs) && in_array('inventories', $this->initializedTabs))
                    <div class="{{ $selected_tab === 'inventories' ? '' : 'd-none' }}">

                        <livewire:dashboard.insight :year="$globalYear" type="inventories" wire:key="insight-inventories-{{ $globalYear }}" />

                        {{-- Stat cards + year navigator --}}
                        <div class="stat-row">
                            <div class="stat-card stat-col-3">
                                <div class="stat-card-icon"><i class="fa fa-dollar-sign"></i></div>
                                <div class="stat-card-body">
                                    <span class="stat-card-label">Gross Sales</span>
                                    <span class="stat-card-value" wire:loading.class="text-muted">
                                        {{ number_format($header_data['sales'] ?? 0, 2) }}
                                    </span>
                                </div>
                            </div>

                            <div class="stat-card stat-col-3">
                                <div class="stat-card-icon"><i class="fa fa-building"></i></div>
                                <div class="stat-card-body">
                                    <span class="stat-card-label">Total Distributors</span>
                                    <span class="stat-card-value" wire:loading.class="text-muted">
                                        {{ number_format($header_data['distributors'] ?? 0) }}
                                    </span>
                                </div>
                            </div>

                            <div class="stat-card stat-col-3">
                                <div class="stat-card-icon"><i class="fa fa-store"></i></div>
                                <div class="stat-card-body">
                                    <span class="stat-card-label">Number of Outlets</span>
                                    <span class="stat-card-value" wire:loading.class="text-muted">
                                        {{ number_format($header_data['ubo'] ?? 0) }}
                                    </span>
                                </div>
                            </div>

                            <div class="stat-year-wrap stat-col-3">
                                <span class="stat-year-label">Year</span>
                                <div class="stat-year-nav">
                                    <button class="stat-year-btn" wire:click="previousYear" wire:loading.attr="disabled" wire:target="previousYear,nextYear,updatedGlobalYear" title="Previous year">
                                        <i class="fa fa-chevron-left"></i>
                                    </button>
                                    <input type="number" class="stat-year-input" wire:model.live="globalYear" placeholder="Year">
                                    <button class="stat-year-btn" wire:click="nextYear" wire:loading.attr="disabled" wire:target="previousYear,nextYear,updatedGlobalYear" title="Next year">
                                        <i class="fa fa-chevron-right"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Inventory charts --}}
                        <div class="row">
                            <div class="col-lg-12">
                                <livewire:dashboard.reports.inventory-aging :year="$globalYear" />
                            </div>
                            <div class="col-lg-6">
                                <livewire:dashboard.reports.inventory-ending :year="$globalYear" />
                                {{-- <livewire:dashboard.reports.oos :year="$globalYear" /> --}}
                            </div>
                            <div class="col-lg-6">
                                <livewire:dashboard.reports.inventory-inactive :year="$globalYear" />
                            </div>
                        </div>
                    </div>
                @endif

                {{-- ── ACCOUNTS TAB ───────────────────────────────── --}}
                @if(in_array('accounts', $enabledTabs) && in_array('accounts', $this->initializedTabs))
                    <div class="{{ $selected_tab === 'accounts' ? '' : 'd-none' }}">
                        <livewire:dashboard.accounts :year="$globalYear" />
                    </div>
                @endif

                {{-- ── TRENDS TAB ─────────────────────────────────── --}}
                @if(in_array('trends', $enabledTabs) && in_array('trends', $this->initializedTabs))
                    <div class="{{ $selected_tab === 'trends' ? '' : 'd-none' }}">
                        <div class="row">
                            <div class="col-lg-6">
                                <livewire:dashboard.reports.trends-sales :year="$globalYear" wire:key="ts-{{ $globalYear }}" />
                            </div>
                            <div class="col-lg-6">
                                <livewire:dashboard.reports.trends-inventory :year="$globalYear" wire:key="ti-{{ $globalYear }}" />
                            </div>
                        </div>
                        <livewire:dashboard.reports.trends-growth :year="$globalYear" wire:key="tg-{{ $globalYear }}" />
                    </div>
                @endif

                </div>{{-- /.wire:loading.remove wrapper --}}

            </div>{{-- /.dash-tab-body --}}
        </div>{{-- /.dash-tabs-card --}}

    @elseif($type === 'account-monitoring')
        <livewire:dashboard.monitoring.sales />
    @endif

</div>

@script
<script>
    Highcharts.setOptions({
        colors: [
            'rgba(5,141,199,0.5)',
            'rgba(80,180,50,0.5)',
            'rgba(237,86,27,0.5)'
        ]
    });
</script>
@endscript
