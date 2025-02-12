<div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">CREATE PURCHASE ORDER</h3>
                </div>
                <div class="card-body py-2">
                    <h4 class="mb-0">CONTROL NUMBER: <span class="bg-info px-1">{{$control_number}}</span></h4>
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
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="" class="mb-0">PO NUMBER</label>
                                <input type="text" class="form-control form-control-sm" placeholder="PO Number...">
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="" class="mb-0">SHIP DATE</label>
                                <input type="date" class="form-control form-control-sm">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">PRODUCT DETAILS</h3>
                </div>
                <div class="card-body">

                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="" class="mb-0">BRANDS</label>
                                <select class="form-control form-control-sm" wire:model="brand_filter">
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
                                <input type="text" class="form-control form-control-sm" placeholder="Search..." wire:model="search">
                            </div>
                        </div>

                        <div class="col-12 table-responsive">
                            <table class="table table-bordered table-sm">
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
                                            <td class="p-0">
                                                @php
                                                    $uom_arr = array_unique([
                                                        $product->order_uom,
                                                        $product->stock_uom,
                                                        $product->other_uom,
                                                    ]);
                                                @endphp
                                                <select class="form-control form-control-sm border-0" wire:model.live="order_data.{{$product->id}}.uom">
                                                    @foreach($uom_arr as $uom)
                                                        <option value="{{$uom}}" {{$uom == $product->order_uom ? 'selected' : ''}}>{{$uom}}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td class="p-0">
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
                                <th class="p-0 text-center">#</th>
                                <th>PRODUCT</th>
                                <th>UOM</th>
                                <th>QUANTITY</th>
                                <th>PRICE</th>
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
                                        <td class="p-0 px-1">
                                            {{$num}}
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
