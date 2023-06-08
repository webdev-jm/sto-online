@extends('adminlte::page')

@section('title', 'Area - '.$account->short_name)

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>[{{$account->account_code}}] {{$account->short_name}} - AREA</h1>
    </div>
    <div class="col-lg-6 text-right">
        <a href="{{route('menu', encrypt($account->id))}}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left mr-1"></i>Back</a>
        @can('area edit')
            <a href="{{route('area.edit', encrypt($area->id))}}" class="btn btn-success btn-sm"><i class="fa fa-plus mr-1"></i>Edit Area</a>
        @endcan
    </div>
</div>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">AREA DETAILS</h3>
                </div>
                <div class="card-body">
        
                    <ul class="list-group list-group-unbordered">
                        <li class="list-group-item p-1">
                            <b>Area Code</b>
                            <span class="float-right">{{$area->code ?? '-'}}</span>
                        </li>
                        <li class="list-group-item p-1">
                            <b>Area Name</b>
                            <span class="float-right">{{$area->name ?? '-'}}</span>
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
