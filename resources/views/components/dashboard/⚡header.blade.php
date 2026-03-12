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
    public $group = 'sales';
    public $globalYear;

    public function mount() {
        $this->globalYear = 2026;
    }

    public function selectType($type)
    {
        $this->type = $type;
    }

    public function refreshData() {
        // $this->setConsolidatedAccountData($this->globalYear);

        // clear cache
        Cache::forget('sales_data_consolidated_'.$this->globalYear);
        Cache::forget('inventory_data_consolidated_'.$this->globalYear);
        Cache::forget('inventory_aging_data_consolidated_'.$this->globalYear);

        $this->selectType($this->type);
    }

    public function export($export_type) {
        if($export_type == 'excel') {

        }
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
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label for="year">YEAR</label>
                            <input type="number" id="year" class="form-control form-control-sm" wire:model.live="globalYear">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer text-right">
                <button class="btn btn-xs btn-success" wire:click.prevent="export('excel')">
                    EXCEL
                </button>
                <button class="btn btn-xs btn-warning" wire:click.prevent="export('json')">
                    JSON
                </button>
            </div>
        </div>

        {{-- TABS --}}
        <div class="row mb-2">
            <div class="col-lg-12">
                <button class="btn btn-default">
                    SALES
                </button>
                <button class="btn btn-default">
                    INVENTORIES
                </button>
            </div>
        </div>

        <div class="card card-primary card-tabs">
            <div class="card-header p-0 pt-1">
                <ul class="nav nav-tabs" id="custom-tabs-five-tab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="sales-tab" data-toggle="pill" href="#sales" role="tab" aria-controls="sales" aria-selected="true">SALES</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="inventories-tab" data-toggle="pill" href="#inventories" role="tab" aria-controls="inventories" aria-selected="false">INVENTORIES</a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="custom-tabs-five-tabContent">
                    <div class="tab-pane fade show active" id="sales" role="tabpanel" aria-labelledby="sales-tab">
                        <div class="overlay-wrapper">
                            <div class="overlay" wire:loading><i class="fas fa-3x fa-sync-alt fa-spin"></i><div class="text-bold pt-2">Loading...</div></div>

                            <div class="row">
                                <div class="col-lg-6">
                                    <livewire:dashboard.reports.sales-performance :year="$globalYear" />
                                </div>
                                <div class="col-lg-6">
                                    <livewire:dashboard.reports.sales-brands :year="$globalYear"/>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="inventories" role="tabpanel" aria-labelledby="inventories-tab">
                        <div class="overlay-wrapper">
                            <div class="overlay" wire:loading><i class="fas fa-3x fa-sync-alt fa-spin"></i><div class="text-bold pt-2">Loading...</div></div>
                            <div id="chart-container"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <livewire:dashboard.reports.sales-sku :year="$globalYear"/>
            </div>
            <div class="col-lg-12">
                <livewire:dashboard.reports.sales-sku-total :year="$globalYear"/>
            </div>
            <div class="col-lg-6">
                <livewire:dashboard.reports.sales-brands :year="$globalYear"/>
            </div>
            <div class="col-lg-6">
                <livewire:dashboard.reports.sales-volume :year="$globalYear"/>
            </div>

            <div class="col-lg-12">
                <livewire:dashboard.reports.inventory-aging :year="$globalYear"/>
            </div>

            <div class="col-lg-12">
                <livewire:dashboard.reports.inventory-ending :year="$globalYear"/>
            </div>

            <div class="col-lg-6">
                <livewire:dashboard.reports.sales-by-channel :year="$globalYear"/>
            </div>

            <div class="col-lg-6">
                <livewire:dashboard.reports.ubo :year="$globalYear"/>
            </div>

            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">SALES VS TARGET {{ $globalYear }}</h3>
                    </div>
                    <div class="card-body">

                    </div>
                </div>
            </div>
        </div>
    @elseif($type === 'account-monitoring')
        <livewire:dashboard.monitoring.sales />
    @endif
</div>


<style>
    body {
        font-family: 'Gloria Hallelujah', cursive; /* Applied globally */
    }

    h3, .card-title {
        font-family: 'Permanent Marker', cursive; /* For the title */
    }

    .card, .container, .main-data-box {
        border: none;
        border-image: url('/images/sketchy-border.png') 30 stretch;
        border-style: solid;
        font-family: 'Permanent Marker', cursive;
    }
</style>

@assets
<script src="{{ asset('vendor/apexcharts/dist/apexcharts.min.js') }}"></script>
<script src="{{ asset('vendor/highcharts/highcharts.js') }}"></script>
<script src="{{ asset('vendor/highcharts/modules/data.js') }}"></script>
<script src="{{ asset('vendor/highcharts/modules/drilldown.js') }}"></script>
<script src="{{ asset('vendor/highcharts/modules/exporting.js') }}"></script>
<script src="{{ asset('vendor/highcharts/modules/export-data.js') }}"></script>
<script src="{{ asset('vendor/highcharts/modules/accessibility.js') }}"></script>
<script src="{{ asset('vendor/highcharts/modules/map.js') }}"></script>
@endassets

@script
<script>
    var options = {
        series: [{
          name: 'Net Profit',
          data: [44, 55, 57, 56, 61, 58, 63, 60, 66]
        }, {
          name: 'Revenue',
          data: [76, 85, 101, 98, 87, 105, 91, 114, 94]
        }, {
          name: 'Free Cash Flow',
          data: [35, 41, 36, 26, 45, 48, 52, 53, 41]
        }],
          chart: {
          type: 'bar',
          height: 350
        },
        plotOptions: {
          bar: {
            horizontal: false,
            columnWidth: '55%',
            borderRadius: 5,
            borderRadiusApplication: 'end'
          },
        },
        dataLabels: {
          enabled: false
        },
        stroke: {
          show: true,
          width: 2,
          colors: ['transparent']
        },
        xaxis: {
          categories: ['Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct'],
        },
        yaxis: {
          title: {
            text: '$ (thousands)'
          }
        },
        fill: {
          opacity: 1
        },
        tooltip: {
          y: {
            formatter: function (val) {
              return "$ " + val + " thousands"
            }
          }
        }
        };

    var chart = new ApexCharts(document.querySelector("#chart-container"), options);

    chart.render();
</script>
@endscript
