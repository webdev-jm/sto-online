<div>
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">LOCATION INVENTORY</h3>
            <div class="card-tools">
                <div class="row">
                    <div class="col">
                        <input type="number" class="form-control form-control-sm" wire:model.live="year" placeholder="Year">
                    </div>
                    <div class="col">
                        <input type="number" class="form-control form-control-sm" wire:model.live="month" placeholder="Month">
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row">

                <div class="col-lg-4">
                    <div class="form-group">
                        <label>Search</label>
                        <input type="text" class="form-control" placeholder="Search" wire:model.live="search">
                    </div>
                </div>

                <div class="col-12 table-responsive">
                    <table class="table table-bordered table-striped table-sm">
                        <thead>
                            <tr class="text-center">
                                <th>SKU CODE</th>
                                <th>DESCRIPTION</th>
                                <th>TYPE</th>
                                <th>UOM</th>
                                <th>QUANTITY</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(!empty($inventories->total()))
                                @foreach($inventories as $inventory)
                                <tr>
                                    <td>{{$inventory->product->stock_code ?? '-'}}</td>
                                    <td>{{$inventory->product->description ?? '-'}} {{$inventory->product->size ?? '-'}}</td>
                                    <td class="text-center align-middle">
                                        @switch($inventory->type)
                                            @case(2)
                                                FREE GOODS
                                                @break
                                            @case(3)
                                                PROMO
                                                @break
                                        @endswitch
                                    </td>
                                    <td class="text-center">{{$inventory->uom}}</td>
                                    <td class="text-right">{{number_format($inventory->total)}}</td>
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5" class="text-center">No available data.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
        <div class="card-footer">
            {{$inventories->links()}}
        </div>
    </div>
</div>
