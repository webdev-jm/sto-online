@extends('adminlte::page')

@section('title', 'Parked Customers - '.$account->short_name)

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>[{{$account->account_code}}] {{$account->short_name}} - {{'['.$account_branch->code.'] '.$account_branch->name}} - CUSTOMERS</h1>
    </div>
    <div class="col-lg-6 text-right">
        <a href="{{route('customer.parked')}}" class="btn btn-warning btn-sm"><i class="fa fa-handshake-slash mr-1"></i>Parked Customers</a>
    </div>
</div>
@stop

@section('content')
<div class="row">
    <div class="col-lg-6">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">CUSTOMER DETAILS</h3>
                @can('customer parked validation')
                    <div class="card-tools">
                        <a href="{{route('customer.same-customer', [encrypt($customer->id), $customer_ubo->ubo_id ?? 0])}}" class="btn btn-info btn-sm"><i class="fa fa-clone mr-1"></i>Same Customer</a>
                        <a href="{{route('customer.different-customer', encrypt($customer->id))}}" class="btn btn-danger btn-sm"><i class="fa fa-store-slash mr-1"></i>Different Customer</a>
                    </div>
                @endcan
            </div>
            <div class="card-body">
                <ul class="list-group list-group-unbordered">
                    <li class="list-group-item p-1">
                        <b>Customer Code</b>
                        <span class="float-right">{{$customer->code ?? '-'}}</span>
                    </li>
                    <li class="list-group-item p-1">
                        <b>Customer Name</b>
                        <span class="float-right">{{$customer->name ?? '-'}}</span>
                    </li>
                    <li class="list-group-item p-1">
                        <b>Address</b>
                        <span class="float-right">{{$customer->address ?? '-'}}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">MAIN CUSTOMER</h3>
                @if(!empty($customer_ubo->ubo_id))
                <div class="card-tools">
                    <label>UBO ID: {{$customer_ubo->ubo_id}}</label>
                </div>
                @endif
            </div>
            <div class="card-body">
                @if(!empty($customer_ubo))
                <ul class="list-group list-group-unbordered">
                    <li class="list-group-item p-1">
                        <b>Customer Code</b>
                        <span class="float-right">{{$customer_ubo->customer->code ?? '-'}}</span>
                    </li>
                    <li class="list-group-item p-1">
                        <b>Customer Name</b>
                        <span class="float-right">{{$customer_ubo->customer->name ?? '-'}}</span>
                    </li>
                    <li class="list-group-item p-1">
                        <b>Address</b>
                        <span class="float-right">{{$customer_ubo->customer->address ?? '-'}}</span>
                    </li>
                </ul>
                @endif
            </div>
        </div>
    </div>
     
    {{-- <div class="col-lg-12">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">CHILD CUSTOMERS</h3>
            </div>
            <div class="card-body">
                <ul class="list-group">
                    @if(!empty($customer_ubo_details))
                        @foreach($customer_ubo_details as $detail)
                            @php
                                $similarity = ($detail->similarity + $detail->address_similarity) / 2;
                            @endphp
                            <li class="list-group-item">
                                <div class="row">
                                    <div class="col-lg-2 text-center">
                                        <p class="m-0 font-weight-bold">{{$detail->ubo_id}}</p>
                                        <small class="font-weight-bold text-muted">UBO ID</small>
                                    </div>
                                    <div class="col-lg-2 text-center">
                                        <p class="m-0 font-weight-bold">{{$similarity}}</p>
                                        <small class="font-weight-bold text-muted">SIMILARITY</small>
                                    </div>
                                    <div class="col-lg-2 text-center">
                                        <p class="m-0 font-weight-bold">{{$detail->customer->code}}</p>
                                        <small class="font-weight-bold text-muted">CODE</small>
                                    </div>
                                    <div class="col-lg-3 text-center">
                                        <p class="m-0 font-weight-bold">{{$detail->customer->name}}</p>
                                        <small class="font-weight-bold text-muted">NAME</small>
                                    </div>
                                    <div class="col-lg-3 text-center">
                                        <p class="m-0 font-weight-bold">{{$detail->customer->address}}</p>
                                        <small class="font-weight-bold text-muted">ADDRESS</small>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    @endif
                </ul>
            </div>
        </div>
    </div> --}}

</div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
@stop
