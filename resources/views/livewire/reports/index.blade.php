<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">REPORTS</h3>
        </div>
        <div class="card-body">

            <div class="row">
                <div class="col-lg-3">
                    <div class="form-group">
                        <label for="date_from">DATE FROM</label>
                        <input type="date" class="form-control" wire:model.live="date_from">
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="form-group">
                        <label for="date_to">DATE TO</label>
                        <input type="date" class="form-control" wire:model.live="date_to">
                    </div>
                </div>
            </div>

        </div>
        <div class="card-footer text-right py-1 pr-2">
            <button class="btn btn-secondary btn-sm" wire:click.prevent="clearFilter">
                <i class="fa fa-eraser mr-1"></i>
                RESET FILTER
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-header py-1">
            <h3 class="card-title">SALES DATA</h3>
            <div class="card-tools">
                <button class="btn btn-sm btn-success" wire:click.prevent="exportData" wire:loading.attr="disabled">
                    <i class="fa fa-download mr-1"></i>
                    EXPORT
                </button>
            </div>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-sm table-bordered table-striped">
                <thead>
                    <tr class="text-center">
                        <th class="p-0 align-middle">DATE</th>
                        <th class="p-0 align-middle">INVOICE NUMBER</th>
                        <th class="p-0 align-middle">CUSTOMER</th>
                        <th class="p-0 align-middle">SALESMAN</th>
                        <th class="p-0 align-middle">CHANNEL</th>
                        <th class="p-0 align-middle">LOCATION</th>
                        <th class="p-0 align-middle">SKU CODE</th>
                        <th class="p-0 align-middle">DESCRIPTION</th>
                        <th class="p-0 align-middle">UOM</th>
                        <th class="p-0 align-middle">QUANTITY</th>
                        <th class="p-0 align-middle">PRICE INC. VAT</th>
                        <th class="p-0 align-middle">AMOUNT</th>
                        <th class="p-0 align-middle">AMOUNT INC. VAT</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sales as $sale)
                        <tr>
                            <td class="align-middle p-0 pl-1 text-center">{{$sale->date}}</td>
                            <td class="align-middle text-center p-0">{{$sale->document_number}}</td>
                            <td class="align-middle p-0 text-center">[{{$sale->customer->code ?? ''}}] {{$sale->customer->name ?? ''}}</td>
                            <td class="align-middle p-0 text-center">
                                @if(!empty($sale->salesman))
                                    [{{$sale->salesman->code ?? ''}}] {{$sale->salesman->name ?? ''}}
                                @else
                                    [{{ $sale->customer->salesman->code ?? '' }}] {{ $sale->customer->salesman->name ?? '' }}
                                @endif
                            </td>
                            <td class="align-middle p-0 text-center">
                                @if(!empty($sale->channel))
                                    [{{$sale->channel->code ?? ''}}] {{$sale->channel->name ?? ''}}
                                @else
                                    [{{ $sale->customer->channel->code ?? '' }}] {{ $sale->customer->channel->name ?? '' }}
                                @endif
                            </td>
                            <td class="align-middle p-0 text-center">[{{$sale->location->code ?? ''}}] {{$sale->location->name ?? ''}}</td>
                            <td class="align-middle p-0 text-center">{{$sale->product->stock_code ?? ''}}</td>
                            <td class="align-middle p-0 text-center">{{$sale->product->description ?? ''}} {{$sale->product->size ?? ''}}</td>
                            <td class="align-middle p-0 text-center">{{$sale->uom ?? ''}}</td>
                            <td class="text-right align-middle">{{$sale->quantity ?? ''}}</td>
                            <td class="text-right align-middle">{{$sale->price_inc_vat ?? ''}}</td>
                            <td class="text-right align-middle">{{$sale->amount ?? ''}}</td>
                            <td class="text-right align-middle">{{$sale->amount_inc_vat ?? ''}}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{$sales->links(data: ['scrollTo' => false])}}
        </div>
    </div>
</div>
