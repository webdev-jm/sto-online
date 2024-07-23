@extends('adminlte::page')

@section('title', 'Stock Transfer - '.$account->short_name)

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>[{{$account->account_code}}] {{$account->short_name}} - {{'['.$account_branch->code.'] '.$account_branch->name}} - STOCK TRANSFERS</h1>
    </div>
    <div class="col-lg-6 text-right">
        <a href="{{route('menu', encrypt($account_branch->id))}}" class="btn btn-secondary btn-sm"><i class="fa fa-home mr-1"></i>Main Menu</a>
    </div>
</div>
@stop

@section('content')
    <div class="card card-outline">
        <div class="card-header">
            <h3 class="card-title">STOCK TRANSFERS</h3>
            <div class="card-tools">
                @can('stock transfer upload')
                    <a href="{{route('stock-transfer.upload')}}" class="btn btn-info btn-sm">
                        <i class="fa fa-upload mr-1"></i>
                        UPLOAD
                    </a>
                @endcan
            </div>
        </div>
        <div class="card-body">

            <div class="row">
                <div class="col-12">
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-bordered table-sm table-hover">
                    <thead>
                        <tr class="text-center">
                            <th class="align-middle p-0">CUSTOMER CODE</th>
                            <th class="align-middle p-0">CUSTOMER NAME</th>
                            <th class="align-middle p-0">YEAR</th>
                            <th class="align-middle p-0">MONTH</th>
                            <th class="align-middle p-0">UPLOAD DATE</th>
                            <th class="align-middle p-0">TOTAL TRANSFER TY</th>
                            <th class="align-middle p-0">TOTAL TRANSFER LY</th>
                            <th class="align-middle p-0">GROWTH %</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stock_transfers as $transfer)
                            <tr data-widget="expandable-table" aria-expanded="false">
                                <td>{{$transfer->customer->code}}</td>
                                <td>{{$transfer->customer->name}}</td>
                                <td class="text-center">{{$transfer->year}}</td>
                                <td class="text-center">{{date('F', strtotime($transfer->year.'-'.$transfer->month.'-01'))}}</td>
                                <td class="text-center">{{date('Y-m-d H:i:s a', strtotime($transfer->created_at))}}</td>
                                <td class="text-right font-weight-bold">{{number_format($transfer->total_units_transferred_ty)}}</td>
                                <td class="text-right font-weight-bold">{{number_format($transfer->total_units_transferred_ly)}}</td>
                                <td class="text-right font-weight-bold">
                                    @php
                                        $growth = 0;
                                        if(!empty($transfer->total_units_transferred_ty) && !empty($transfer->total_units_transferred_ly)) {
                                            $growth = (($transfer->total_units_transferred_ty / $transfer->total_units_transferred_ly) - 1) * 100;
                                        }
                                    @endphp
                                    <span {{$growth < 0 ? 'class=text-danger' : ''}}>{{number_format($growth)}}</span>
                                </td>
                            </tr>
                            <tr class="expandable-body">
                                <td colspan="8" class="p-0 text-center">
                                    <table class="table table-bordered table-sm ml-1">
                                        <thead>
                                            <tr class="bg-gray">
                                                <th>BEVI SKU</th>
                                                <th>SKU CODE</th>
                                                <th>OTHER SKU CODE</th>
                                                <th>PRODUCT DESCRIPTION</th>
                                                <th>SIZE</th>
                                                <th>TRANSFER TY</th>
                                                <th>TRANSFER LY</th>
                                                <th>GROWTH %</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($transfer->products as $product)
                                                <tr>
                                                    <td>{{$product->sku->stock_code ?? ''}}</td>
                                                    <td>{{$product->sku_code}}</td>
                                                    <td>{{$product->sku_code_other}}</td>
                                                    <td>{{$product->sku->description ?? ''}}</td>
                                                    <td>{{$product->sku->size ?? ''}}</td>
                                                    <td>{{$product->transfer_ty}}</td>
                                                    <td>{{$product->transfer_ly}}</td>
                                                    <td>
                                                        @php
                                                            $product_growth = 0;
                                                            if(!empty($product->transfer_ty) && !empty($product->transfer_ly)) {
                                                                $product_growth = (($product->transfer_ty / $product->transfer_ly) - 1) * 100;
                                                            }
                                                        @endphp
                                                        <span {{$product_growth < 0 ? 'class=text-danger' : ''}}>{{number_format($product_growth)}}</span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                    </tfoot>
                </table>
            </div>

        </div>
        <div class="card-footer">
            {{$stock_transfers->links()}}
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
