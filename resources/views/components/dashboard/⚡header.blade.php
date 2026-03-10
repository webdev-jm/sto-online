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

        <div class="row">
            <div class="col-lg-6">
                <livewire:dashboard.reports.sales-performance :year="$globalYear" />
            </div>
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
