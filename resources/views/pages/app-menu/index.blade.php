@extends('adminlte::page')

@section('title', 'APP MENU - '.$account->short_name)

@section('content_header')
    <div class="row">
        <div class="col-lg-6">
            <h1>[{{$account->account_code}}] {{$account->short_name}}</h1>
        </div>
        <div class="col-lg-6 text-right">
            <a href="/home" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left mr-1"></i>Change Account</a>
        </div>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-lg-3">
        <a href="#" class="btn btn-block btn-app bg-info ml-0">
            <i class="fa fa-money-check-alt"></i>
            Sales
        </a>
    </div>

    <div class="col-lg-3">
        <a href="#" class="btn btn-block btn-app bg-secondary ml-0">
            <i class="fa fa-warehouse"></i>
            Inventory
        </a>
    </div>
</div>

<hr>

<div class="row">
    @can('customer access')
    <div class="col-lg-3">
        <a href="{{route('customer.index')}}" class="btn btn-block btn-app bg-primary ml-0">
            <i class="fa fa-people-arrows"></i>
            Customers
        </a>
    </div>
    @endcan

    @can('channel access')
    <div class="col-lg-3">
        <a href="{{route('channel.index')}}" class="btn btn-block btn-app bg-warning ml-0">
            <i class="fa fa-store"></i>
            Channels
        </a>
    </div>
    @endcan

    @can('area access')
    <div class="col-lg-3">
        <a href="{{route('area.index')}}" class="btn btn-block btn-app bg-success ml-0">
            <i class="fa fa-map-marked-alt"></i>
            Areas
        </a>
    </div>
    @endcan

    @can('salesman access')
    <div class="col-lg-3">
        <a href="{{route('salesman.index')}}" class="btn btn-block btn-app bg-danger ml-0">
            <i class="fa fa-user-tie"></i>
            Salesman
        </a>
    </div>
    @endcan
</div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
@stop
