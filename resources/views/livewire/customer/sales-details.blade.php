<div>
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">CUSTOMER SALES</h3>
        </div>
        <div class="card-body">

            <div class="row">
                <div class="col-lg-4">
                    <div class="form-group">
                        <label for="search">Search</label>
                        <input type="text" class="form-control" placeholder="Search" wire:model="search">
                    </div>
                </div>

                <div class="col-12 table-responsive">
                    <table class="table table-striped table-sm table-bordered">
                        <thead>
                            <tr class="text-center">
                                <th class="align-middle">DATE</th>
                                <th class="align-middle">DOCUMENT NO.</th>
                                <th class="align-middle">CUSTOMER</th>
                                <th class="align-middle">SALESMAN</th>
                                <th class="align-middle">LOCATION</th>
                                <th class="align-middle">SKU CODE</th>
                                <th class="align-middle">DESCRIPTION</th>
                                <th class="align-middle">UOM</th>
                                <th class="align-middle">QUANTITY</th>
                                <th class="align-middle">PRICE INC. VAT</th>
                                <th class="align-middle">AMOUNT</th>
                                <th class="align-middle">AMOUNT INC. VAT</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(!empty($sales->total()))
                                @foreach($sales as $sale)
                                <tr>
                                    <td class="text-center align-middle">{{$sale->date}}</td>
                                    <td class="text-center align-middle">{{$sale->document_number}}</td>
                                    <td class="text-left align-middle">{{'['.($sale->customer->code ?? '-').'] '.($sale->customer->name ?? '-')}}</td>
                                    <td class="text-left align-middle">{{'['.($sale->salesman->code ?? '-').'] '.($sale->salesman->name ?? '-')}}</td>
                                    <td class="text-center align-middle">{{$sale->location->code ?? '-'}}</td>
                                    <td class="text-center align-middle">{{$sale->product->stock_code ?? '-'}}</td>
                                    <td class="text-left align-middle">{{($sale->product->description ?? '-').' '.($sale->product->size ?? '-')}}</td>
                                    <td class="text-center align-middle">{{$sale->uom}}</td>
                                    <td class="text-right align-middle">{{number_format($sale->quantity)}}</td>
                                    <td class="text-right align-middle">{{number_format($sale->price_inc_vat, 2)}}</td>
                                    <td class="text-right align-middle">{{number_format($sale->amount, 2)}}</td>
                                    <td class="text-right align-middle">{{number_format($sale->amount_inc_vat, 2)}}</td>
                                </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
        <div class="card-footer">
            {{$sales->links()}}
        </div>
    </div>
</div>
