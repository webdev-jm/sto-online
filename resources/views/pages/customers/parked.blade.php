@extends('adminlte::page')

@section('title', 'Parked Customers - '.$account->short_name)

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>[{{$account->account_code}}] {{$account->short_name}} - {{'['.$account_branch->code.'] '.$account_branch->name}} - CUSTOMERS</h1>
    </div>
    <div class="col-lg-6 text-right">
        <a href="{{route('customer.index')}}" class="btn btn-primary btn-sm"><i class="fa fa-people-carry mr-1"></i>Customers</a>
    </div>
</div>
@stop

@section('content')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">PARKED CUSTOMER LIST</h3>
        </div>
        <div class="card-body">

            <div class="row mb-2">
                <div class="col-lg-6">
                </div>
            </div>
            <ul class="list-group">
                @foreach($customers as $customer)
                <li class="list-group-item">
                    <div class="row">
                        <div class="col-lg-2 text-center">
                            <p class="m-0 font-weight-bold">{{$customer->code}}</p>
                            <small class="font-weight-bold text-muted">CODE</small>
                        </div>
                        <div class="col-lg-3 text-center">
                            <p class="m-0 font-weight-bold">{{$customer->name}}</p>
                            <small class="font-weight-bold text-muted">NAME</small>
                        </div>
                        <div class="col-lg-3 text-center">
                            <p class="m-0 font-weight-bold">{{$customer->address}}</p>
                            <small class="font-weight-bold text-muted">ADDRESS</small>
                        </div>
                        <div class="col-lg-2 text-center">
                            <p class="m-0 font-weight-bold">{{$customer->salesman->code ?? '-'}}</p>
                            <small class="font-weight-bold text-muted">SALESMAN</small>
                        </div>
                        <div class="col-lg-2 text-center">
                            <p class="m-0">
                                <a href="{{route('customer.show', encrypt($customer->id))}}" class="btn btn-info btn-xs" title="view details">
                                    <i class="fa fa-list"></i>
                                </a>
                                <a href="" class="btn btn-success btn-xs" data-id="{{encrypt($customer->id)}}" title="validate">
                                    <i class="fa fa-user-check"></i>
                                </a>
                            </p>
                            <small class="font-weight-bold text-muted">ACTION</small>
                        </div>
                    </div>
                </li>
                @endforeach
            </ul>
            
        </div>
        <div class="card-footer">
            {{$customers->links()}}
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
@stop
