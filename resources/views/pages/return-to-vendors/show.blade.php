@extends('adminlte::page')

@section('title', 'RTV - '.$account->short_name)

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>[{{$account->account_code}}] {{$account->short_name}} - {{'['.$account_branch->code.'] '.$account_branch->name}} - RTV</h1>
    </div>
    <div class="col-lg-6 text-right">
        <a href="{{route('rtv.index')}}" class="btn btn-default btn-sm">
            <i class="fa fa-arrow-left mr-1"></i>
            BACK
        </a>
        <a href="{{route('menu', encrypt($account_branch->id))}}" class="btn btn-secondary btn-sm"><i class="fa fa-home mr-1"></i>Main Menu</a>
    </div>
</div>
@stop

@section('content')
    <div class="card card-outline">
        <div class="card-header">
            <h3 class="card-title">RTV DETAILS</h3>
            <div class="card-tools">
            </div>
        </div>
        <div class="card-body">
            
            <h4>RTV NUMBER: {{$rtv->rtv_number}}</h4>

            <table class="table table-sm">
                <thead>
                    <tr>
                        <th class="border-top" colspan="6">PURCHASE ORDER</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th class="border-0">ENTRY DATE:</th>
                        <td class="border-0">{{$rtv->entry_date}}</td>
                    </tr>
                    <tr>
                        <th class="border-0">DOCUMENT NUMBER:</th>
                        <td class="border-0">{{$rtv->document_number}}</td>
                    </tr>
                    <tr>
                        <th class="border-0">SHIP DATE:</th>
                        <td class="border-0">{{$rtv->ship_date}}</td>
                    </tr>
                    <tr>
                        <th class="border-0">REASON:</th>
                        <td class="border-0">{{$rtv->reason}}</td>
                    </tr>
                    <tr>
                        <th class="border-0">NAME:</th>
                        <td class="border-0">{{$rtv->ship_to_name}}</td>
                    </tr>
                    <tr>
                        <th class="border-0">ADDRESS:</th>
                        <td class="border-0">{{$rtv->ship_to_address}}</td>
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
                    @foreach($rtv->rtv_products as $product_data)
                        @php
                            $total_qty += $product_data->quantity ?? 0;
                            $total_cost += $product_data->cost ?? 0;
                        @endphp
                        <tr class="text-center">
                            <td>{{$product_data->sku_code}}</td>
                            <td>{{$product_data->other_sku_code}}</td>
                            <td>{{$product_data->description}}</td>
                            <td>{{$product_data->uom}}</td>
                            <td class="text-right">{{number_format($product_data->quantity)}}</td>
                            <td class="text-right">{{number_format($product_data->cost, 2)}}</td>
                            <td>{{$product_data->remarks}}</td>
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
        <div class="card-footer">
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
<script>
    $(function() {

    });
</script>
@stop
