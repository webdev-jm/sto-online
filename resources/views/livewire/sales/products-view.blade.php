<div>
    <div class="row mb-1">
        <div class="col-12 text-right" wire:loading.remove>
            <button class="btn btn-primary btn-sm" wire:click.prevent="export">
                <i class="fa fa-download mr-1"></i>
                Download
            </button>
        </div>
        <div class="col-12 text-right" wire:loading>
            <button class="btn btn-primary btn-sm">
                <i class="fa fa-spinner fa-spin mr-1"></i>
                Downloading
            </button>
        </div>
    </div>
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">LINE DETAILS</h3>
            <div class="card-tools">
                <b>COUNT: {{$sales->total()}}</b>
            </div>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped table-bordered table-sm">
                <thead>
                    <tr class="text-center">
                        <th class="align-middle pl-0">DATE</th>
                        <th class="align-middle">DOCUMENT NUMBER</th>
                        <th class="align-middle">CUSTOMER</th>
                        <th class="align-middle">SALESMAN</th>
                        <th class="align-middle">LOCATION</th>
                        <th class="align-middle">SKU CODE</th>
                        <th class="align-middle">DESCRIPTION</th>
                        <th class="align-middle">UOM</th>
                        <th class="align-middle">QUANTITY</th>
                        <th class="align-middle">AMOUNT</th>
                        <th class="align-middle">AMOUNT INC VAT</th>
                        <th class="align-middle">TYPE</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sales as $sale)
                    <tr class="text-center">
                        <td class="align-middle pl-1">{{$sale->date}}</td>
                        <td class="align-middle text-left">{{$sale->document_number}}</td>
                        <td class="align-middle text-left">[{{$sale->customer->code ?? '-'}}] {{$sale->customer->name ?? '-'}}</td>
                        <td class="align-middle text-left">{{$sale->salesman->code ?? ''}} - {{$sale->salesman->name ?? ''}}</td>
                        <td class="align-middle">{{$sale->location->code ?? '-'}}</td>
                        <td class="align-middle">{{$sale->product->stock_code ?? '-'}}</td>
                        <td class="align-middle">{{$sale->product->description ?? '-'}} - {{$sale->product->size ?? '-'}}</td>
                        <td class="align-middle">{{$sale->uom}}</td>
                        <td class="align-middle text-right">{{number_format($sale->quantity)}}</td>
                        <td class="align-middle text-right">{{number_format($sale->amount, 2)}}</td>
                        <td class="align-middle text-right">{{number_format($sale->amount_inc_vat, 2)}}</td>
                        <td class="align-middle text-center">{{ $sale->type == 2 ? 'FREE GOODS' : ($sale->type == 3 ? 'PROMO' : '') }}</td>
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
