<?php

use Livewire\Component;
use App\Models\Account;

new class extends Component
{
    public $globalYear;
    public string $date_from;
    public string $date_to;
    public int $account_id;
    public string $account_name  = '';
    public string $selected_tab  = 'sales';
    public array  $initializedTabs = ['sales'];

    public function mount(Account $account): void
    {
        $this->globalYear   = date('Y');
        $this->date_from    = date('Y') . '-01';
        $this->date_to      = date('Y') . '-12';
        $this->account_id   = $account->id;
        $this->account_name = $account->account_name;
    }

    public function previousYear(): void
    {
        $this->globalYear--;
    }

    public function nextYear(): void
    {
        $this->globalYear++;
    }

    public function selectTab(string $tab): void
    {
        $this->selected_tab = $tab;

        if (!in_array($tab, $this->initializedTabs)) {
            $this->initializedTabs[] = $tab;
        }
    }
};
?>

<div>

    {{-- ── TABS CARD ──────────────────────────────────────────────── --}}
    <div class="dash-tabs-card">

        <ul class="dash-tab-nav" role="tablist">
            <li class="nav-item">
                <a class="nav-link {{ $selected_tab === 'sales' ? 'active' : '' }}"
                   role="tab"
                   wire:click.prevent="selectTab('sales')"
                   href="#">
                    <i class="fa fa-chart-line mr-1"></i> SALES PERFORMANCE
                    <i class="fa fa-spinner fa-spin ml-1" wire:loading wire:target="selectTab('sales')"></i>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $selected_tab === 'inventories' ? 'active' : '' }}"
                   role="tab"
                   wire:click.prevent="selectTab('inventories')"
                   href="#">
                    <i class="fa fa-boxes mr-1"></i> INVENTORY &amp; SUPPLY CHAIN
                    <i class="fa fa-spinner fa-spin ml-1" wire:loading wire:target="selectTab('inventories')"></i>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $selected_tab === 'accounts' ? 'active' : '' }}"
                   role="tab"
                   wire:click.prevent="selectTab('accounts')"
                   href="#">
                    <i class="fa fa-building mr-1"></i> ACCOUNTS
                    <i class="fa fa-spinner fa-spin ml-1" wire:loading wire:target="selectTab('accounts')"></i>
                </a>
            </li>
        </ul>

        <div class="dash-tab-body">

            {{-- ── TAB SWITCH SKELETON ────────────────────────────── --}}
            <div class="tab-switch-sk" wire:loading wire:target="selectTab">
                <div class="tab-switch-sk-row">
                    <div class="sk-stat-card"><div class="sk-line sk-line-sm"></div><div class="sk-line sk-line-lg"></div></div>
                    <div class="sk-stat-card"><div class="sk-line sk-line-sm"></div><div class="sk-line sk-line-lg"></div></div>
                    <div class="sk-stat-card"><div class="sk-line sk-line-sm"></div><div class="sk-line sk-line-lg"></div></div>
                </div>
                <div class="tab-switch-sk-grid">
                    <div class="sk-chart-card"></div>
                    <div class="sk-chart-card"></div>
                </div>
            </div>

            <div wire:loading.remove wire:target="selectTab">

            {{-- ── SALES TAB ──────────────────────────────────────── --}}
            @if(in_array('sales', $this->initializedTabs))
                <div class="{{ $selected_tab === 'sales' ? '' : 'd-none' }}">

                    {{-- Year navigator --}}
                    <div class="stat-row">
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

                    <div class="row">
                        <div class="col-lg-6">
                            <livewire:dashboard.reports.sales-performance :year="$globalYear" :account_id="$account_id" />
                        </div>
                        <div class="col-lg-6">
                            <livewire:dashboard.reports.sales-brands :year="$globalYear" :account_id="$account_id" />
                        </div>
                        <div class="col-lg-6">
                            <livewire:dashboard.reports.sales-by-channel :year="$globalYear" :account_id="$account_id" />
                        </div>
                        <div class="col-lg-6">
                            <livewire:dashboard.reports.sales-volume :year="$globalYear" :account_id="$account_id" />
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <livewire:dashboard.reports.ubo-matrix :year="$globalYear" :account_id="$account_id" />
                        </div>
                        <div class="col-lg-6">
                            <livewire:dashboard.reports.sales-sku :year="$globalYear" :account_id="$account_id" />
                        </div>
                        <div class="col-lg-6">
                            <livewire:dashboard.reports.ubo :year="$globalYear" :account_id="$account_id" />
                        </div>
                    </div>
                </div>
            @endif

            {{-- ── INVENTORIES TAB ────────────────────────────────── --}}
            @if(in_array('inventories', $this->initializedTabs))
                <div class="{{ $selected_tab === 'inventories' ? '' : 'd-none' }}">

                    {{-- Year navigator --}}
                    <div class="stat-row">
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

                    <div class="row">
                        <div class="col-lg-12">
                            <livewire:dashboard.reports.inventory-aging :year="$globalYear" :account_id="$account_id" />
                            <livewire:dashboard.reports.inventory-ending :year="$globalYear" :account_id="$account_id" />
                        </div>
                    </div>
                </div>
            @endif

            {{-- ── ACCOUNTS TAB ────────────────────────────────── --}}
            @if(in_array('accounts', $this->initializedTabs))
                <div class="{{ $selected_tab === 'accounts' ? '' : 'd-none' }}">

                    {{-- Date range filter --}}
                    <div class="row mb-3">
                        <div class="col-lg-3">
                            <label class="form-label">From</label>
                            <input type="month" class="stat-year-input" wire:model.live="date_from">
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label">To</label>
                            <input type="month" class="stat-year-input" wire:model.live="date_to">
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-lg-6">
                            <livewire:dashboard.accounts.sales-per-salesman-type :date_from="$date_from" :date_to="$date_to" :account_id="$account_id" />
                        </div>
                        <div class="col-lg-6">
                            <livewire:dashboard.accounts.sales-per-address :year="(int) substr($date_from, 0, 4)" :account_id="$account_id" />
                        </div>
                        <div class="col-lg-6">
                            <livewire:dashboard.accounts.sales-per-salesman :year="(int) substr($date_from, 0, 4)" :account_id="$account_id" />
                        </div>
                        <div class="col-lg-6">
                            <livewire:dashboard.accounts.productivity-calls :date_from="$date_from" :date_to="$date_to" :account_id="$account_id" />
                        </div>
                    </div>

                    <hr>

                    {{-- Trends --}}
                    <div class="row mt-4">
                        <div class="col-lg-6">
                            <livewire:dashboard.reports.trends-sales :date_from="$date_from" :date_to="$date_to" :account_id="$account_id" wire:key="ts-{{ $date_from }}-{{ $date_to }}-{{ $account_id }}" />
                        </div>
                        <div class="col-lg-6">
                            <livewire:dashboard.reports.trends-inventory :date_from="$date_from" :date_to="$date_to" :account_id="$account_id" wire:key="ti-{{ $date_from }}-{{ $date_to }}-{{ $account_id }}" />
                        </div>
                    </div>
                    <livewire:dashboard.reports.trends-growth :date_from="$date_from" :date_to="$date_to" :account_id="$account_id" wire:key="tg-{{ $date_from }}-{{ $date_to }}-{{ $account_id }}" />
                </div>
            @endif

            </div>{{-- /.wire:loading.remove wrapper --}}

        </div>{{-- /.dash-tab-body --}}
    </div>{{-- /.dash-tabs-card --}}

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
