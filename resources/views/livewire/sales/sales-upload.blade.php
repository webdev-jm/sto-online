<div>

    @if(!empty($err_msg))
    <div class="alert alert-danger">
        {{$err_msg}}
    </div>
    @endif

    <div class="card card-default">
        <div class="card-header">
            <h3 class="card-title">UPLOAD SALES DATA</h3>
        </div>
        <div class="card-body">
            
            <div class="row">
                <div class="col-lg-4">
                    <div class="form-group">
                        <label for="">Upload File</label>
                        <input type="file" class="form-control" wire:model="file">
                    </div>
                </div>
            </div>

        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">NOTE:</h3>
            <div class="card-tools" wire:loading.remove >
                <button class="btn btn-primary btn-sm" wire:click.prevent="saveUpload"><i class="fa fa-save mr-1"></i>Save Data</button>
            </div>
            <div class="card-tools" wire:loading>
                <button class="btn btn-primary btn-sm"><i class="fa fa-spinner fa-spin mr-1"></i>Loading..</button>
            </div>
        </div>
        <div class="card-body">
            <p class="mb-0">
                All lines marked with <i class="fa fa-times-circle fa-sm text-danger"></i> will not be uploaded and only lines marked with <i class="fa fa-check-circle fa-sm text-success"></i> will be uploaded.
            </p>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">UPLOAD PREVIEW <span wire:loading><i class="fa fa-spinner fa-spin ml-1"></i> Loading</span></h3>
            @if(!empty($sales_data))
                <div class="card-tools">
                    <b>COUNT: {{count($sales_data)}}</b>
                </div>
            @endif
        </div>
        <div class="card-body table-responsive py-0 px-1">
            @if(!empty($sales_data))
                <table class="table table-striped table-bordered table-sm">
                    <thead>
                        <tr class="text-center">
                            <th class="align-middle p-1"></th>
                            <th class="align-middle p-1">DATE</th>
                            <th class="align-middle">DOCUMENT NO.</th>
                            <th class="align-middle">CUSTOMER CODE</th>
                            <th class="align-middle">LOCATION</th>
                            <th class="align-middle">SKU CODE</th>
                            <th class="align-middle">DESCRIPTION</th>
                            <th class="align-middle">QUANTITY</th>
                            <th class="align-middle">UOM</th>
                            <th class="align-middle">PRICE INCL. VAT</th>
                            <th class="align-middle">AMOUNT</th>
                            <th class="align-middle">AMOUNT INCL. VAT</th>
                            <th class="align-middle">LINE DISCOUNT</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($paginatedData as $key => $data)
                            <tr class="text-center">
                                <td class="align-middle p-1">
                                    @if($data['check'] == 0)
                                        <i class="fa fa-check-circle fa-sm text-success"></i>
                                    @else
                                        <i class="fa fa-times-circle fa-sm text-danger"></i>
                                        @switch($data['check'])
                                            @case(1)
                                                {{-- Customer --}}
                                                <small class="text-danger">Customer</small>
                                                @break
                                            @case(2)
                                                {{-- Location --}}
                                                <small class="text-danger">Location</small>
                                                @break
                                            @case(3)
                                                {{-- Product --}}
                                                <small class="text-danger">Product</small>
                                                @break
                                            @default
                                                
                                        @endswitch
                                    @endif
                                </td>
                                <td class="align-middle p-1">{{date('Y-m-d', strtotime($data['date'])) ?? '-'}}</td>
                                <td class="align-middle">{{$data['document'] ?? '-'}}</td>
                                <td class="align-middle">{{$data['customer_code'] ?? '-'}}</td>
                                <td class="align-middle">{{$data['location_code'] ?? '-'}}</td>
                                <td class="align-middle">{{$data['sku_code'] ?? '-'}}</td>
                                <td class="align-middle">{{$data['description'] ?? '-'}} {{$data['size'] ?? '-'}}</td>
                                <td class="align-middle text-right">{{number_format($data['quantity']) ?? '-'}}</td>
                                <td class="align-middle">{{$data['uom'] ?? '-'}}</td>
                                <td class="align-middle text-right">{{number_format($data['price_inc_vat'], 2) ?? '-'}}</td>
                                <td class="align-middle text-right">{{number_format($data['amount'], 2) ?? '-'}}</td>
                                <td class="align-middle text-right">{{number_format($data['amount_inc_vat'], 2) ?? '-'}}</td>
                                <td class="align-middle text-right">{{number_format($data['line_discount'], 2) ?? '-'}}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
        @if(!empty($sales_data))
            <div class="card-footer">
                {{$paginatedData->links()}}
            </div>
        @endif
    </div>
</div>
