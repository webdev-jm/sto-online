<div>
    <button class="btn btn-warning btn-xs mb-2" wire:click.prevent="showFilter">
        <i class="fa fa-filter mr-1"></i>
        FILTER
    </button>

    @if($show_filter)
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">FILTER</h3>
                <div class="card-tools">
                    
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label for="date-type">DATE TYPE</label>
                            <select id="date-type" class="form-control form-control-sm" wire:model="filters.date_type">
                                <option value="">-SELECT TYPE-</option>
                                <option value="order_date">ORDER DATE</option>
                                <option value="ship_date">SHIP DATE</option>
                                <option value="created_at">UPLOAD DATE</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="form-group">
                            <label for="from">FROM</label>
                            <input type="date" id="from" class="form-control form-control-sm" wire:model="filters.from">
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="form-group">
                            <label for="to">TO</label>
                            <input type="date" id="to" class="form-control form-control-sm" wire:model="filters.to">
                        </div>
                    </div>

                </div>
            </div>
            <div class="card-footer text-right p-1">
                <button class="btn btn-info btn-xs" wire:click.prevent="applyFilter">
                    APPLY FITLER
                </button>
                <button class="btn btn-secondary btn-xs" wire:click.prevent="clearFilter">
                    CLEAR FILTER
                </button>
            </div>
        </div>
    @endif
</div>
