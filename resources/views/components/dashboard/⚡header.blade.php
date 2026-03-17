<?php

use Livewire\Component;
use App\Http\Traits\ConsolidateAccountData;
use App\Http\Traits\SalesDataAggregator;
use Illuminate\Support\Facades\Cache;

new class extends Component
{
    use ConsolidateAccountData;
    use SalesDataAggregator;

    public $type = 'sales';
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

    public function refreshData() {
        // $this->setConsolidatedAccountData($this->globalYear);

        $this->selectType($this->type);
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
};
?>

<div>
    <div class="card">
        <div class="card-body">
            <button class="btn btn-{{ $this->type == 'sales' ? 'primary' : 'default'}} btn-sm" wire:click.prevent="selectType('sales')">REPORTS</button>
            <button class="btn btn-{{ $this->type == 'account-monitoring' ? 'primary' : 'default'}} btn-sm" wire:click.prevent="selectType('account-monitoring')">ACCOUNT MONITORING</button>
        </div>
    </div>

    @if($type === 'sales')
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">REPORTS</h3>
                <div class="card-tools">
                    <button class="btn btn-sm btn-success" wire:click.prevent="refreshData" wire:loading.attr="disabled"><i class="fa fa-spinner fa-spin mr-1" wire:loading></i>REFRESH DATA</button>
                </div>
            </div>
        </div>

        <div class="card card-secondary card-tabs">
            <div class="card-header p-0 pt-1">
                <ul class="nav nav-tabs" id="custom-tabs-five-tab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="sales-tab" data-toggle="pill" href="#sales" role="tab" aria-controls="sales" aria-selected="true">
                            SALES PERFORMANCE
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="inventories-tab" data-toggle="pill" href="#inventories" role="tab" aria-controls="inventories" aria-selected="false">
                            INVENTORY AND SUPPLY CHAIN OVERVIEW
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body" style="background-color: rgba(136, 136, 136, 0.6)">
                <div class="tab-content" id="custom-tabs-five-tabContent">
                    <div class="tab-pane fade show active" id="sales" role="tabpanel" aria-labelledby="sales-tab">
                        <div class="overlay-wrapper">
                            <div class="overlay" wire:loading><i class="fas fa-3x fa-sync-alt fa-spin"></i>
                                <div class="text-bold pt-2">Loading...</div>
                            </div>

                            <div class="row">
                                <div class="col-lg-3">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-info"><i class="far fa-envelope"></i></span>

                                        <div class="info-box-content">
                                            <span class="info-box-text">GROSS SALES</span>
                                            <span class="info-box-number">{{ number_format($header_data['sales'] ?? 0, 2) }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-3">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-info"><i class="far fa-envelope"></i></span>

                                        <div class="info-box-content">
                                            <span class="info-box-text">TOTAL DISTRIBUTORS</span>
                                            <span class="info-box-number">{{ number_format($header_data['distributors'] ?? 0) }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-3">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-info"><i class="far fa-envelope"></i></span>

                                        <div class="info-box-content">
                                            <span class="info-box-text">NUMBER OF OUTLETS</span>
                                            <span class="info-box-number">{{ number_format($header_data['ubo'] ?? 0) }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label for="year">YEAR</label>
                                        <input type="number" id="year" class="form-control form-control-sm" wire:model.live="globalYear">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-4">
                                    <livewire:dashboard.reports.sales-performance :year="$globalYear" />
                                </div>
                                <div class="col-lg-4">
                                    <livewire:dashboard.reports.sales-brands :year="$globalYear"/>
                                </div>
                                <div class="col-lg-4">
                                    <livewire:dashboard.reports.sales-by-channel :year="$globalYear"/>
                                </div>
                                <div class="col-lg-8">
                                    <livewire:dashboard.reports.area :year="$globalYear"/>
                                </div>
                                <div class="col-lg-4">
                                    <livewire:dashboard.reports.top-distributor :year="$globalYear"/>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-5">
                                    <livewire:dashboard.reports.sales-volume :year="$globalYear"/>

                                    <livewire:dashboard.reports.sales-sku :year="$globalYear"/>
                                </div>
                                <div class="col-lg-7">
                                    <livewire:dashboard.reports.ubo-matrix :year="$globalYear"/>

                                    <livewire:dashboard.reports.ubo :year="$globalYear"/>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="inventories" role="tabpanel" aria-labelledby="inventories-tab">
                        <div class="overlay-wrapper">
                            <div class="overlay" wire:loading><i class="fas fa-3x fa-sync-alt fa-spin"></i>
                                <div class="text-bold pt-2">Loading...</div>
                            </div>

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
                </div>
            </div>
        </div>


    @elseif($type === 'account-monitoring')
        <livewire:dashboard.monitoring.sales />
    @endif
</div>

@assets
<script src="{{ asset('vendor/highcharts/highcharts.js') }}"></script>
<script src="{{ asset('vendor/highcharts/modules/data.js') }}"></script>
<script src="{{ asset('vendor/highcharts/modules/drilldown.js') }}"></script>
<script src="{{ asset('vendor/highcharts/modules/accessibility.js') }}"></script>
<script src="{{ asset('vendor/highcharts/modules/map.js') }}"></script>
@endassets

@script
<script>
    Highcharts.setOptions({
        colors: [
            'rgba(5,141,199,0.5)', 'rgba(80,180,50,0.5)', 'rgba(237,86,27,0.5)'
        ]
    });
</script>
@endscript
