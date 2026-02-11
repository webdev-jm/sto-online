<div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">CREATE PURCHASE ORDER</h3>
                    <div class="card-tools">
                        <button class="btn btn-xs btn-info" id="btn-upload">
                            <i class="fa fa-upload"></i>
                            UPLOAD PURCHASE ORDER
                        </button>
                        <button class="btn btn-xs btn-secondary" wire:click.prevent="savePO('draft')">
                            <i class="fa fa-save"></i>
                            DRAFT
                        </button>
                        <button class="btn btn-xs btn-success" wire:click.prevent="savePO('submitted')">
                            <i class="fa fa-save"></i>
                            SUBMIT
                        </button>
                    </div>
                </div>
                <div class="card-body py-2">
                    <div class="row">
                        <!-- CONTROL NUMBER -->
                        <div class="col-lg-6">
                            <h4 class="mb-0">
                                CONTROL NUMBER: 
                                <span class="bg-info px-1 rounded font-weight">{{$control_number}}</span>
                            </h4>
                        </div>

                        <!-- ATTACHMENT -->
                        <div class="col-lg-6">
                            <div class="form-group mb-0">
                                <label for="" class="mb-0">ATTACHMENT</label>
                                <input type="file" class="form-control form-control-sm{{$errors->has('attachment') ? ' is-invalid' : ''}}" wire:model.live="attachment">
                                <small class="text-danger">{{$errors->first('attachment')}}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- order form -->
        <div class="col-lg-6">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">HEADER DETAILS</h3>
                </div>
                <div class="card-body">
                    <!-- header details -->
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="" class="mb-0">PO NUMBER</label>
                                <input type="text" class="form-control form-control-sm{{$errors->has('po_number')  ? ' is-invalid' : ''}}" placeholder="PO Number..." wire:model.live="po_number">
                                <small class="text-danger">{{$errors->first('po_number')}}</small>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="" class="mb-0">SHIP DATE</label>
                                <input type="date" class="form-control form-control-sm{{$errors->has('ship_date') ? ' is-invalid' : ''}}" wire:model.live="ship_date">
                                <small class="text-danger">{{$errors->first('ship_date')}}</small>
                            </div>
                        </div>
                    </div>

                    <label class="mb-0">SHIPPING DETAILS</label>
                    <hr class="mt-0">

                    <!-- shipping details -->
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="ship_to_name" class="mb-0">SHIP TO NAME</label>
                                <input type="text" class="form-control form-control-sm{{$errors->has('ship_to_name') ? ' is-invalid' : ''}}" placeholder="Ship to name..." wire:model.live="ship_to_name">
                                <small class="text-danger">{{$errors->first('ship_to_name')}}</small>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="ship_to_address" class="mb-0">SHIP TO ADDRESS</label>
                                <input type="text" class="form-control form-control-sm{{$errors->has('ship_to_address') ? ' is-invalid' : ''}}" placeholder="Ship to address..."wire:model.live="ship_to_address">
                                <small class="text-danger">{{$errors->first('ship_to_address')}}</small>
                            </div>
                        </div>

                        <div class="col-lg-12">
                            <div class="form-group">
                                <label for="" class="mb-0">SHIPPING INSTRUCTION</label>
                                <textarea class="form-control form-control-sm{{$errors->has('shipping_instruction') ? ' is-invalid' : ''}}" placeholder="Shipping instruction" wire:model.live="shipping_instruction"></textarea>
                                <small class="text-danger">{{$errors->first('shipping_instruction')}}</small>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="card card-primary card-outline">
                <div class="card-header py-2">
                    <h3 class="card-title">
                        PRODUCT DETAILS
                        <br>
                        <small class="text-danger">{{$errors->first('order_details')}}</small>
                    </h3>
                    @if($errors->has('order_details'))
                        <div class="card-tools">
                            <span class="bg-danger px-1 rounded">REQUIRED</span>
                        </div>
                    @endif
                </div>
                <div class="card-body">

                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="" class="mb-0">BRANDS</label>
                                <select class="form-control form-control-sm" wire:model.live="brand_filter">
                                    <option value="ALL">ALL BRAND</option>
                                    @foreach($brands as $brand)
                                        <option value="{{$brand->brand}}">{{$brand->brand}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="" class="mb-0">SEARCH</label>
                                <input type="text" class="form-control form-control-sm" placeholder="Search..." wire:model.live="search">
                            </div>
                        </div>

                        <div class="col-12 table-responsive">
                            <table class="table table-bordered table-sm mb-0">
                                <thead>
                                    <tr class="text-center bg-gray">
                                        <th>PRODUCT</th>
                                        <th>BRAND</th>
                                        <th>UNIT</th>
                                        <th>ORDER</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($products as $product)
                                        <tr>
                                            <td class="align-middle">
                                                <span class="font-weight-bold">[{{$product->stock_code}}]</span>
                                                {{$product->description}}
                                                <span class="text-muted">[{{$product->size}}]</span>
                                            </td>
                                            <td class="text-center align-middle">
                                                {{$product->brand}}
                                            </td>
                                            <td class="p-0 align-middle">
                                                @php
                                                    $uom_arr = array_unique([
                                                        $product->order_uom,
                                                        $product->stock_uom,
                                                        $product->other_uom,
                                                    ]);
                                                @endphp
                                                <select class="form-control form-control-sm border-0" wire:model.live="order_data.{{$product->id}}.uom">
                                                    @foreach($uom_arr as $uom)
                                                        <option value="{{$uom}}" {{$uom == $product->order_uom ? 'selected=selected' : ''}}>{{$uom}}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td class="p-0 align-middle">
                                                <input type="number" class="form-control form-control-sm border-0" wire:model.live="order_data.{{$product->id}}.order">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                </div>
                <div class="card-footer">
                    {{$products->links()}}
                </div>
            </div>
        </div>

        <!-- order details -->
         <div class="col-lg-6">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">ORDER DETAILS</h3>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr class="text-center bg-gray">
                                <th class="p-0 text-center align-middle">#</th>
                                <th class="align-middle">PRODUCT</th>
                                <th class="align-middle">UOM</th>
                                <th class="align-middle">QUANTITY</th>
                                <th class="align-middle">PRICE</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(!empty($order_details['details']))
                                @php
                                    $num = 0;
                                @endphp
                                @foreach($order_details['details'] as $detail)
                                    @php
                                        $num++;
                                    @endphp
                                    <tr>
                                        <td class="p-0 px-1 text-center">
                                            {{$num}}.
                                        </td>
                                        <td class="text-left p-0 pl-1">
                                            <span class="font-weight-bold">[{{$detail['product']['stock_code']}}]</span>
                                            {{$detail['product']['description']}}
                                            <span class="text-muted">[{{$detail['product']['size']}}]</span>
                                        </td>
                                        <td class="text-center align-middle p-0">
                                            {{$detail['uom']}}
                                        </td>
                                        <td class="text-right p-0 pr-1 align-middle">
                                            {{number_format($detail['quantity'])}}
                                        </td>
                                        <td class="text-right align-middle p-0 pr-1">
                                            {{number_format($detail['price'], 2)}}
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray">
                                <th colspan="3" class="p-0 pl-1 align-middle">TOTAL</th>
                                <th class="text-right align-middle p-0 pr-1">
                                    {{number_format($order_details['total_quantity'] ?? 0)}}
                                </th>
                                <th class="text-right align-middle p-0 px-1">
                                    {{number_format($order_details['total_price'] ?? 0, 2)}}
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            
         </div>
    </div>


    
</div>
