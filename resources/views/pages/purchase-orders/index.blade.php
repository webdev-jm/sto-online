@extends('adminlte::page')

@section('title', 'Purchase Orders - '.$account->short_name)

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>[{{$account->account_code}}] {{$account->short_name}} - {{'['.$account_branch->code.'] '.$account_branch->name}} - PO</h1>
    </div>
    <div class="col-lg-6 text-right">
        <a href="{{route('menu', encrypt($account_branch->id))}}" class="btn btn-secondary btn-sm"><i class="fa fa-home mr-1"></i>Main Menu</a>
    </div>
</div>
@stop

@section('content')
    <div class="card card-outline">
        <div class="card-header">
            <h3 class="card-title">PO LIST</h3>
            <div class="card-tools">
                <a href="" class="btn btn-warning btn-xs">
                    <i class="fa fa-filter"></i>
                    FILTER
                </a>
                <a href="{{route('purchase-order.upload')}}" class="btn btn-primary btn-xs">
                    <i class="fa fa-upload"></i>
                    UPLOAD
                </a>
            </div>
        </div>
        <div class="card-body">
            
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr class="text-center">
                            <th class="align-middle p-0">PO Number</th>
                            <th class="align-middle p-0">Status</th>
                            <th class="align-middle p-0">Order Date</th>
                            <th class="align-middle p-0">Ship Date</th>
                            <th class="align-middle p-0">Shipping Instruction</th>
                            <th class="align-middle p-0">Ship to Name</th>
                            <th class="align-middle p-0">Ship to Address</th>
                            <th class="align-middle p-0">Total Quantity</th>
                            <th class="align-middle p-0">Total Gross Amount</th>
                            <th class="align-middle p-0">Total Net Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(empty($purchase_orders->count()))
                            <tr>
                                <td colspan="10" class="text-center align-middle p-0">
                                    - no data availble -
                                </td>
                            </tr>
                        @else
                            @foreach($purchase_orders as $purchase_order)
                                <tr>
                                    <td class="p-0 px-1 align-middle">
                                        {{$purchase_order->po_number}}
                                    </td>
                                    <td class="p-0 px-1 align-middle">
                                        {{$purchase_order->status}}
                                    </td>
                                    <td class="p-0 px-1 align-middle">
                                        {{$purchase_order->order_date}}
                                    </td>
                                    <td class="p-0 px-1 align-middle">
                                        {{$purchase_order->ship_date}}
                                    </td>
                                    <td class="p-0 px-1 align-middle">
                                        {{$purchase_order->shipping_instruction}}
                                    </td>
                                    <td class="p-0 px-1 align-middle">
                                        {{$purchase_order->ship_to_name}}
                                    </td>
                                    <td class="p-0 px-1 align-middle">
                                        {{$purchase_order->ship_to_address}}
                                    </td>
                                    <td class="p-0 px-1 align-middle text-right">
                                        {{$purchase_order->total_quantity}}
                                    </td>
                                    <td class="p-0 px-1 align-middle text-right">
                                        {{$purchase_order->total_sales}}
                                    </td>
                                    <td class="p-0 px-1 align-middle text-right">
                                        {{$purchase_order->grand_total}}
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>

        </div>
        <div class="card-footer">
            {{$purchase_orders->links()}}
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
