<div>
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">PO UPLOAD</h3>
            <div class="card-tools">
            </div>
        </div>
        <div class="card-body">
            <form wire:submit="checkUploads">
                @csrf
                <div class="row">
                    <div class="col-12">
                        <div class="form-group">
                            <strong>MAX 100 FILES</strong>
                            <input type="file" class="form-control" id="file-upload-form" wire:model.live="files" multiple>
                            <p class="text-danger">{{$errors->first('files')}}</p>
                            <span wire:loading wire:target="files">Uploading...</span>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-sm">
                    CHECK FILES
                </button>
            </form>
            
        </div>
        @if(!empty($po_data))
            <div class="card-footer text-right">
                <button class="btn btn-info btn-sm" wire:click.prevent="uploadData" wire:loading.attr="disabled">
                    UPLOAD
                </button>
            </div>
        @endif
    </div>

    @if(!empty($success_msg))
    <div class="alert alert-success">
        <ul class="mb-0">
            @foreach($success_msg as $msg)
            <li>{{$msg}}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @if(!empty($po_data))
        @foreach($po_data as $po_number => $data)
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">DETAILS PREVIEW</h3>
                </div>
                <div class="card-body">
                    @if(!empty($po_errors[$po_number]))
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                            @foreach($po_errors[$po_number] as $key => $msg)
                                <li><b>{{$key}}</b> - {{$msg}}</li>        
                            @endforeach
                            </ul>
                        </div>
                    @endif

                    <h4>PO NO: {{$po_number}}</h4>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th class="border-top" colspan="6">PURCHASE ORDER</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th class="border-0">ORDER DATE:</th>
                                <td class="border-0">{{$data['headers']['order_date'] ?? ''}}</td>
                                <th class="border-0">PO NO:</th>
                                <td class="border-0">{{$po_number}}</td>
                            </tr>
                            <tr>
                                <th class="border-0">DELIVERY DATE:</th>
                                <td class="border-0">{{$data['headers']['ship_date'] ?? ''}}</td>
                                <th class="border-0">SHIPPING INSTRUCTION:</th>
                                <td class="border-0">{{$data['headers']['shipping_instruction'] ?? ''}}</td>
                            </tr>
                            <tr>
                                <th class="border-0">SHIP TO NAME:</th>
                                <td colspan="5" class="border-0">{{$data['headers']['ship_to_name'] ?? ''}}</td>
                            </tr>
                            <tr>
                                <th class="border-0">ADDRESS:</th>
                                <td colspan="5" class="border-0">{{$data['headers']['ship_to_address'] ?? ''}}</td>
                            </tr>
                        </tbody>
                    </table>
                    <hr class="my-0">
                    <table class="table table-sm">
                        <thead>
                            <tr class="text-center">
                                <th class="text-left">SKU CODE</th>
                                <th class="text-left">OTHER SKU CODE</th>
                                <th class="text-left">SKU DESCRIPTION</th>
                                <th class="text-left">UOM</th>
                                <th class="text-right">COST</th>
                                <th class="text-right">DISC %</th>
                                <th class="text-right">NET COST PER UOM</th>
                                <th class="text-right">QTY ORDERED</th>
                                <th class="text-right">TOTAL NET COST</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $gross_amount = 0;
                                $net_amount = 0;
                                $quantity = 0;
                                $discount_amount = 0;
                            @endphp
                            @foreach($data['products'] as $product)
                                @php
                                    $product['gross_amount'] = empty($product['gross_amount']) ? 0 : $product['gross_amount'];
                                    $product['net_amount'] = empty($product['net_amount']) ? 0 : $product['net_amount'];
                                    $product['quantity'] = empty($product['quantity']) ? 0 : $product['quantity'];
                                    $product['discount_amount'] = empty($product['discount_amount']) ? 0 : $product['discount_amount'];

                                    $gross_amount += (float)$product['gross_amount'];
                                    $net_amount += (float)$product['net_amount'];
                                    $quantity += $product['quantity'];
                                    $discount_amount += (float)$product['discount_amount'];
                                @endphp
                                <tr>
                                    <td class="border-0 text-left">{{$product['sku_code']}}</td>
                                    <td class="border-0 text-left">{{$product['sku_code_other']}}</td>
                                    <td class="border-0 text-left">{{$product['product_name']}}</td>
                                    <td class="border-0 text-left">{{$product['unit_of_measure']}}</td>
                                    <td class="text-right border-0">{{number_format((float)$product['gross_amount'], 2)}}</td>
                                    <td class="text-right border-0">{{number_format((float)$product['discount'], 2)}}</td>
                                    <td class="text-right border-0">{{number_format((float)$product['net_amount'], 2)}}</td>
                                    <td class="text-right border-0">{{number_format((int)$product['quantity'])}}</td>
                                    <td class="text-right border-0">{{number_format((float)$product['net_amount_per_uom'], 2)}}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>TOTAL GROSS AMOUNT</th>
                                <th colspan="7" class="text-right">{{number_format($quantity)}}</th>
                                <th class="text-right">{{number_format($gross_amount, 2)}}</th>
                            </tr>
                            <tr>
                                <th class="border-0">TOTAL DISCOUNT</th>
                                <th colspan="8" class="border-0 text-right">{{number_format($discount_amount, 2)}}</th>
                            </tr>
                            <tr>
                                <th class="border-0">TOTAL NET AMOUNT</th>
                                <th colspan="8" class="border-0 text-right">{{number_format($net_amount, 2)}}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endforeach
    @endif

    <script>
        document.addEventListener('livewire:load', function () {
            
        });
    </script>
</div>
