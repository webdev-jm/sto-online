@extends('adminlte::page')

@section('title', 'Customer - '.$account->short_name)

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>[{{$account->account_code}}] {{$account->short_name}} - {{'['.$account_branch->code.'] '.$account_branch->name}} - CUSTOMER</h1>
    </div>
    <div class="col-lg-6 text-right">
        <a href="{{route('customer.index')}}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left mr-1"></i>Back</a>
        @can('customer edit')
            <a href="{{route('customer.edit', encrypt($customer->id))}}" class="btn btn-success btn-sm"><i class="fa fa-pen-alt mr-1"></i>Edit Customer</a>
        @endcan
    </div>
</div>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">CUSTOMER DETAILS</h3>
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
                            <b>Salesman</b>
                            <span class="float-right">[{{$customer->salesman->code ?? '-'}}] {{$customer->salesman->name ?? '-'}}</span>
                        </li>
                        <li class="list-group-item p-1">
                            <b>Address</b>
                            <span class="float-right">{{$customer->address ?? '-'}}</span>
                        </li>
                    </ul>
                    
                </div>
                <div class="card-footer">
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
@stop
