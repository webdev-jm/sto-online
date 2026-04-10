<?php

use Livewire\Component;
use App\Http\Traits\ConsolidateAccountData;
use App\Http\Traits\SalesDataAggregator;
use Illuminate\Support\Facades\Artisan;

use App\Exports\ReportsMultiSheetExport;
use Maatwebsite\Excel\Facades\Excel;

new class extends Component
{
    use ConsolidateAccountData;
    use SalesDataAggregator;

    public $type = 'sales';
    public $selected_tab = 'accounts';
    public $globalYear;
    public $header_data = [];

    public function mount() {
        $this->globalYear = date('Y');
        $this->getData();
    }

    public function selectType($type)
    {
        $this->type = $type;
    }

    public function updatedGlobalYear() {
        $this->getData();
    }

    public function getData() {
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

    public function selectTab($tab) {
        $this->selected_tab = $tab;
    }

    public function refreshData() {
        Artisan::call('cache:clear');
        $this->setConsolidatedAccountData();
    }

    public function exportData() {
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
            <i class="fa fa-spinner fa-spin ml-1" wire:loading wire:target="selectType"></i>
        </button>
        <button
            class="dash-type-btn {{ $this->type === 'account-monitoring' ? 'active' : '' }}"
            wire:click.prevent="selectType('account-monitoring')"
            wire:loading.attr="disabled"
            wire:target="selectType">
            <i class="fa fa-binoculars"></i> ACCOUNT MONITORING
            <i class="fa fa-spinner fa-spin ml-1" wire:loading wire:target="selectType"></i>
        </button>
    </div>

    @if($type === 'sales')

        {{-- ── REFRESH ────────────────────────────────────────────── --}}
        <button class="btn-refresh" wire:click.prevent="refreshData" wire:loading.attr="disabled" wire:target="refreshData">
            <i class="fa fa-sync-alt" wire:loading.class="fa-spin" wire:target="refreshData"></i>
            REFRESH DATA
        </button>

        {{-- EXPORT --}}
        <button class="btn btn-primary btn-sm ml-2" wire:click.prevent="exportData" wire:loading.attr="disabled" wire:target="exportData">
            <i class="fa fa-file-export mr-1"></i> EXPORT
            <i class="fa fa-spinner fa-spin ml-1" wire:loading wire:target="exportData"></i>
        </button>

        {{-- ── TABS CARD ──────────────────────────────────────────── --}}
        <div class="dash-tabs-card">

            <ul class="dash-tab-nav" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {{ $selected_tab === 'sales' ? 'active' : '' }}"
                       role="tab"
                       wire:click.prevent="selectTab('sales')"
                       href="#">
                        <i class="fa fa-chart-line mr-1"></i> SALES PERFORMANCE
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $selected_tab === 'inventories' ? 'active' : '' }}"
                       role="tab"
                       wire:click.prevent="selectTab('inventories')"
                       href="#">
                        <i class="fa fa-boxes mr-1"></i> INVENTORY &amp; SUPPLY CHAIN
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ $selected_tab === 'accounts' ? 'active' : '' }}"
                       role="tab"
                       wire:click.prevent="selectTab('accounts')"
                       href="#">
                        <i class="fa fa-building mr-1"></i> ACCOUNTS
                    </a>
                </li>
            </ul>

            <div class="dash-tab-body">

                {{-- ── SALES TAB ──────────────────────────────────── --}}
                <div class="{{ $selected_tab === 'sales' ? '' : 'd-none' }}">
                    <div class="overlay-wrapper" style="position:relative;">
                        <div class="overlay text-center align-middle" wire:loading>
                            <i class="fas fa-3x fa-sync-alt fa-spin"></i>
                            <div class="text-bold pt-2">Loading...</div>
                        </div>

                        {{-- Stat cards --}}
                        <div class="stat-row">
                            <div class="stat-card stat-col-3">
                                <div class="stat-card-icon">
                                    <i class="fa fa-dollar-sign"></i>
                                </div>
                                <div class="stat-card-body">
                                    <span class="stat-card-label">Gross Sales</span>
                                    <span class="stat-card-value">{{ number_format($header_data['sales'] ?? 0, 2) }}</span>
                                </div>
                            </div>

                            <div class="stat-card stat-col-3">
                                <div class="stat-card-icon">
                                    <i class="fa fa-building"></i>
                                </div>
                                <div class="stat-card-body">
                                    <span class="stat-card-label">Total Distributors</span>
                                    <span class="stat-card-value">{{ number_format($header_data['distributors'] ?? 0) }}</span>
                                </div>
                            </div>

                            <div class="stat-card stat-col-3">
                                <div class="stat-card-icon">
                                    <i class="fa fa-store"></i>
                                </div>
                                <div class="stat-card-body">
                                    <span class="stat-card-label">Number of Outlets</span>
                                    <span class="stat-card-value">{{ number_format($header_data['ubo'] ?? 0) }}</span>
                                </div>
                            </div>

                            <div class="stat-year-wrap stat-col-3">
                                <span class="stat-year-label">Year</span>
                                <input type="number" class="stat-year-input" wire:model.live="globalYear" placeholder="Year">
                            </div>
                        </div>

                        {{-- Charts row 1 --}}
                        <div class="row">
                            <div class="col-lg-6">
                                <livewire:dashboard.reports.sales-performance :year="$globalYear"/>
                            </div>
                            <div class="col-lg-6">
                                <livewire:dashboard.reports.sales-brands :year="$globalYear" />
                            </div>
                            <div class="col-lg-6">
                                <livewire:dashboard.reports.sales-by-channel :year="$globalYear" />
                            </div>
                            <div class="col-lg-6">
                                <livewire:dashboard.reports.area :year="$globalYear"/>
                                <livewire:dashboard.reports.top-distributor :year="$globalYear" />
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
                </div>

                {{-- ── INVENTORIES TAB ────────────────────────────── --}}
                <div class="{{ $selected_tab === 'inventories' ? '' : 'd-none' }}">
                    <div class="overlay-wrapper" style="position:relative;">
                        <div class="overlay text-center align-middle" wire:loading>
                            <i class="fas fa-3x fa-sync-alt fa-spin"></i>
                            <div class="text-bold pt-2">Loading...</div>
                        </div>

                        {{-- Stat cards (same data, shared $header_data) --}}
                        <div class="stat-row">
                            <div class="stat-card stat-col-3">
                                <div class="stat-card-icon">
                                    <i class="fa fa-dollar-sign"></i>
                                </div>
                                <div class="stat-card-body">
                                    <span class="stat-card-label">Gross Sales</span>
                                    <span class="stat-card-value">{{ number_format($header_data['sales'] ?? 0, 2) }}</span>
                                </div>
                            </div>

                            <div class="stat-card stat-col-3">
                                <div class="stat-card-icon">
                                    <i class="fa fa-building"></i>
                                </div>
                                <div class="stat-card-body">
                                    <span class="stat-card-label">Total Distributors</span>
                                    <span class="stat-card-value">{{ number_format($header_data['distributors'] ?? 0) }}</span>
                                </div>
                            </div>

                            <div class="stat-card stat-col-3">
                                <div class="stat-card-icon">
                                    <i class="fa fa-store"></i>
                                </div>
                                <div class="stat-card-body">
                                    <span class="stat-card-label">Number of Outlets</span>
                                    <span class="stat-card-value">{{ number_format($header_data['ubo'] ?? 0) }}</span>
                                </div>
                            </div>

                            <div class="stat-year-wrap stat-col-3">
                                <span class="stat-year-label">Year</span>
                                <input type="number" class="stat-year-input" wire:model.live="globalYear" placeholder="Year">
                            </div>
                        </div>

                        {{-- Inventory charts --}}
                        <div class="row">
                            <div class="col-lg-8">
                                <livewire:dashboard.reports.inventory-aging :year="$globalYear"/>
                                <livewire:dashboard.reports.inventory-ending :year="$globalYear"/>
                            </div>
                            <div class="col-lg-4">
                                <livewire:dashboard.reports.oos :year="$globalYear"/>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ACCOUNTS TAB --}}
                <div class="{{ $selected_tab === 'accounts' ? '' : 'd-none' }}">
                    <div class="overlay-wrapper" style="position:relative;">
                        <div class="overlay text-center align-middle" wire:loading>
                            <i class="fas fa-3x fa-sync-alt fa-spin"></i>
                            <div class="text-bold pt-2">Loading...</div>
                        </div>

                        <livewire:dashboard.accounts :year="$globalYear"/>

                    </div>
                </div>

            </div>{{-- /.dash-tab-body --}}
        </div>{{-- /.dash-tabs-card --}}

    @elseif($type === 'account-monitoring')
        <livewire:dashboard.monitoring.sales />
    @endif

</div>

@assets
<script src="{{ asset('vendor/highcharts/highcharts.js') }}"></script>
<script src="{{ asset('vendor/highcharts/modules/drilldown.js') }}"></script>
<script src="{{ asset('vendor/highcharts/modules/data.js') }}"></script>
<script src="{{ asset('vendor/highcharts/modules/map.js') }}"></script>
<script src="{{ asset('vendor/highcharts/modules/mouse-wheel-zoom.js') }}"></script>
<script src="{{ asset('vendor/highcharts/modules/accessibility.js') }}"></script>
@endassets

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
