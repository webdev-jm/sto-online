@extends('adminlte::page')

@section('title', 'Purchase Orders - '.$account->short_name)

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>[{{$account->account_code}}] {{$account->short_name}} - {{'['.$account_branch->code.'] '.$account_branch->name}} - PO</h1>
    </div>
    <div class="col-lg-6 text-right">
        <a href="{{route('purchase-order.index')}}" class="btn btn-default">
            <i class="fa fa-arrow-left mr-1"></i>
            BACK TO LIST
        </a>
        <a href="{{route('menu', encrypt($account_branch->id))}}" class="btn btn-secondary btn-sm"><i class="fa fa-home mr-1"></i>Main Menu</a>
    </div>
</div>
@stop

@section('content')
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">DETAILS PREVIEW</h3>
    </div>
    <div class="card-body">

        <h4>PO NO: {{$purchase_order->po_number}}</h4>
        <table class="table table-sm">
            <thead>
                <tr>
                    <th class="border-top" colspan="6">PURCHASE ORDER</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th class="border-0">ORDER DATE:</th>
                    <td class="border-0">{{$purchase_order->order_date}}</td>
                    <th class="border-0">PO NO:</th>
                    <td class="border-0">{{$purchase_order->po_number}}</td>
                </tr>
                <tr>
                    <th class="border-0">DELIVERY DATE:</th>
                    <td class="border-0">{{$purchase_order->ship_date}}</td>
                    <th class="border-0">SHIPPING INSTRUCTION:</th>
                    <td class="border-0">{{$purchase_order->shipping_instruction}}</td>
                </tr>
                <tr>
                    <th class="border-0">SHIP TO NAME:</th>
                    <td colspan="5" class="border-0">{{$purchase_order->ship_to_name}}</td>
                </tr>
                <tr>
                    <th class="border-0">ADDRESS:</th>
                    <td colspan="5" class="border-0">{{$purchase_order->ship_to_address}}</td>
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
                @foreach($purchase_order->details as $detail)
                    <tr>
                        <td class="border-0 text-left">{{$detail->sku_code}}</td>
                        <td class="border-0 text-left">{{$detail->sku_code_other}}</td>
                        <td class="border-0 text-left">{{$detail->product_name}}</td>
                        <td class="border-0 text-left">{{$detail->unit_of_measure}}</td>
                        <td class="text-right border-0">{{number_format($detail->gross_amount, 2)}}</td>
                        <td class="text-right border-0">{{number_format($detail->discount_amount, 2)}}</td>
                        <td class="text-right border-0">{{number_format($detail->net_amount, 2)}}</td>
                        <td class="text-right border-0">{{number_format($detail->quantity)}}</td>
                        <td class="text-right border-0">{{number_format($detail->net_amount_per_uom, 2)}}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th>TOTAL GROSS AMOUNT</th>
                    <th colspan="7" class="text-right">{{number_format($purchase_order->total_quantity)}}</th>
                    <th class="text-right">{{number_format($purchase_order->total_sales, 2)}}</th>
                </tr>
                <tr>
                    <th class="border-0">TOTAL DISCOUNT</th>
                    <th colspan="8" class="border-0 text-right">{{number_format($purchase_order->total_sales - $purchase_order->grand_total, 2)}}</th>
                </tr>
                <tr>
                    <th class="border-0">TOTAL NET AMOUNT</th>
                    <th colspan="8" class="border-0 text-right">{{number_format($purchase_order->grand_total, 2)}}</th>
                </tr>
            </tfoot>
        </table>
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
