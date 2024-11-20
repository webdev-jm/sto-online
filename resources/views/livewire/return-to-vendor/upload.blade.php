<div>
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">UPLOAD RTV</h3>
            <div class="card-tools">
            </div>
        </div>
        <div class="card-body">

            <div class="row">
                <div class="col-12">
                    <div class="form-group">
                        <label for="" class="mb-0">UPLOAD FILE/S</label>
                        <input type="file" class="form-control{{$errors->has('upload_files') ? ' is-invalid' : ''}}" wire:model="upload_files" multiple>
                        <small class="text-danger">{{$errors->first('upload_files')}}</small>
                    </div>
                </div>

                <div class="col-12">
                    <button class="btn btn-sm btn-primary" wire:click.prevent="checkFiles">
                        CHECK FILES
                    </button>
                </div>
            </div>

        </div>
        @if(!empty($rtv_data))
            <div class="card-footer text-right">
                <button class="btn btn-info btn-sm" wire:click="uploadRTV">
                    <i class="fa fa-save mr-1"></i>
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

    @if(!empty($rtv_data))
        @foreach($rtv_data as $rtv_number => $data)
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">RTV PREVIEW</h3>
                </div>
                <div class="card-body">

                    @if(!empty($rtv_errors[$rtv_number]))
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                            @foreach($rtv_errors[$rtv_number] as $key => $msg)
                                <li><b>{{$key}}</b> - {{$msg}}</li>        
                            @endforeach
                            </ul>
                        </div>
                    @endif

                    <h4>RTV NUMBER: {{$rtv_number}}</h4>

                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th class="border-top" colspan="6">PURCHASE ORDER</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th class="border-0">ENTRY DATE:</th>
                                <td class="border-0">{{$data['headers']['entry_date']}}</td>
                            </tr>
                            <tr>
                                <th class="border-0">DOCUMENT NUMBER:</th>
                                <td class="border-0">{{$data['headers']['document_number']}}</td>
                            </tr>
                            <tr>
                                <th class="border-0">SHIP DATE:</th>
                                <td class="border-0">{{$data['headers']['ship_date']}}</td>
                            </tr>
                            <tr>
                                <th class="border-0">REASON:</th>
                                <td class="border-0">{{$data['headers']['reason']}}</td>
                            </tr>
                            <tr>
                                <th class="border-0">NAME:</th>
                                <td class="border-0">{{$data['headers']['ship_to_name']}}</td>
                            </tr>
                            <tr>
                                <th class="border-0">ADDRESS:</th>
                                <td class="border-0">{{$data['headers']['ship_to_address']}}</td>
                            </tr>
                        </tbody>
                    </table>

                    <hr class="my-0">

                    <table class="table table-sm">
                        <thead>
                            <tr class="text-center">
                                <th>SKU CODE</th>
                                <th>SKU CODE OTHER</th>
                                <th>DESCRIPTION</th>
                                <th>UOM</th>
                                <th class="text-right">QUANTITY</th>
                                <th class="text-right">COST</th>
                                <th>REMARKS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $total_qty = 0;
                                $total_cost = 0;
                            @endphp
                            @foreach($data['products'] as $product_data)
                                @php
                                    $total_qty += $product_data['quantity'] ?? 0;
                                    $total_cost += $product_data['cost'] ?? 0;
                                @endphp
                                <tr class="text-center">
                                    <td>{{$product_data['sku_code']}}</td>
                                    <td>{{$product_data['sku_code_other']}}</td>
                                    <td>{{$product_data['description']}}</td>
                                    <td>{{$product_data['uom']}}</td>
                                    <td class="text-right">{{number_format($product_data['quantity'])}}</td>
                                    <td class="text-right">{{number_format($product_data['cost'], 2)}}</td>
                                    <td>{{$product_data['remarks']}}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="4" class="text-right">TOTAL:</th>
                                <th class="text-right">{{number_format($total_qty)}}</th>
                                <th class="text-right">{{number_format($total_cost, 2)}}</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                    
                </div>
            </div>
        @endforeach
    @endif
</div>
