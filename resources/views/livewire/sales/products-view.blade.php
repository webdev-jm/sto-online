<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">LINE DETAILS</h3>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped table-bordered table-sm">
                <thead>
                    <tr class="text-center">
                        <th class="align-middle pl-0">DATE</th>
                        <th class="align-middle">CUSTOMER</th>
                        <th class="align-middle">SALESMAN</th>
                        <th class="align-middle">LOCATION</th>
                        <th class="align-middle">SKU CODE</th>
                        <th class="align-middle">DESCRIPTION</th>
                        <th class="align-middle">UOM</th>
                        <th class="align-middle">QUANTITY</th>
                        <th class="align-middle">AMOUNT</th>
                        <th class="align-middle">AMOUNT INC VAT</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sales as $sale)
                    <tr class="text-center">
                        <td class="align-middle pl-1">{{$sale->date}}</td>
                        <td class="align-middle text-left">[{{$sale->customer->code}}] {{$sale->customer->name}}</td>
                        <td class="align-middle text-left">[{{$sale->salesman->code}}] {{$sale->name}}</td>
                        <td class="align-middle">{{$sale->location->code}}</td>
                        <td class="align-middle">{{$sale->product->stock_code}}</td>
                        <td class="align-middle">{{$sale->product->description}} - {{$sale->product->size}}</td>
                        <td class="align-middle">{{$sale->uom}}</td>
                        <td class="align-middle text-right">{{number_format($sale->quantity)}}</td>
                        <td class="align-middle text-right">{{number_format($sale->amount, 2)}}</td>
                        <td class="align-middle text-right">{{number_format($sale->amount_inc_vat, 2)}}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{$sales->links()}}
        </div>
    </div>
</div>
