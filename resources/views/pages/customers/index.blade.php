@extends('adminlte::page')

@section('title', 'Customers - '.$account->short_name)

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>[{{$account->account_code}}] {{$account->short_name}} - CUSTOMERS</h1>
    </div>
    <div class="col-lg-6 text-right">
        <a href="{{route('menu', encrypt($account->id))}}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left mr-1"></i>Main Menu</a>
        @can('customer create')
            <a href="{{route('customer.create')}}" class="btn btn-primary btn-sm"><i class="fa fa-plus mr-1"></i>Add Customer</a>
        @endcan
    </div>
</div>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">CUSTOMER LIST</h3>
        </div>
        <div class="card-body">

            <ul class="list-group">
                @foreach($customers as $customer)
                <li class="list-group-item">
                    <div class="row">
                        <div class="col-lg-2 text-center">
                            <p class="m-0">{{$customer->area->name}}</p>
                            <b>AREA</b>
                        </div>
                        <div class="col-lg-2 text-center">
                            <p class="m-0">{{$customer->channel->name}}</p>
                            <b>CHANNEL</b>
                        </div>
                        <div class="col-lg-3 text-center">
                            <p class="m-0">{{$customer->code}}</p>
                            <b>CODE</b>
                        </div>
                        <div class="col-lg-3 text-center">
                            <p class="m-0">{{$customer->name}}</p>
                            <b>NAME</b>
                        </div>
                        <div class="col-lg-2 text-center">
                            <p class="m-0">
                                <a href="{{route('customer.show', encrypt($customer->id))}}" class="btn btn-info btn-xs">
                                    <i class="fa fa-list"></i>
                                </a>
                                @can('customer edit')
                                    <a href="{{route('customer.edit', encrypt($customer->id))}}" class="btn btn-success btn-xs">
                                        <i class="fa fa-pen"></i>
                                    </a>
                                @endcan
                                @can('customer delete')
                                    <a href="" class="btn btn-danger btn-xs">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                @endcan
                            </p>
                            <b>ACTION</b>
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
