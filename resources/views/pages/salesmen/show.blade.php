@extends('adminlte::page')

@section('title', 'Salesman - '.$account->short_name)

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>[{{$account->account_code}}] {{$account->short_name}} - SALESMAN</h1>
    </div>
    <div class="col-lg-6 text-right">
        <a href="{{route('salesman.index')}}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left mr-1"></i>Back</a>
        @can('salesman edit')
            <a href="{{route('salesman.edit', encrypt($salesman->id))}}" class="btn btn-success btn-sm"><i class="fa fa-pen-alt mr-1"></i>Edit Salesman</a>
        @endcan
    </div>
</div>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">SALESMAN DETAILS</h3>
                </div>
                <div class="card-body">
        
                    <ul class="list-group list-group-unbordered">
                        <li class="list-group-item p-1">
                            <b>Salesman Code</b>
                            <span class="float-right">{{$salesman->code ?? '-'}}</span>
                        </li>
                        <li class="list-group-item p-1">
                            <b>Salesman Name</b>
                            <span class="float-right">{{$salesman->name ?? '-'}}</span>
                        </li>
                    </ul>
                    
                </div>
                <div class="card-footer">
                </div>
            </div>
        </div>

        @if(!empty($salesman->areas))
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">SALESMAN AREAS</h3>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-unbordered">
                        @foreach($salesman->areas as $area)
                        <li class="list-group-item p-1">
                            <b>{{$area->code}}</b>
                            <span class="float-right">{{$area->name ?? '-'}}</span>
                        </li>
                        @endforeach
                    </ul>
                    
                </div>
                <div class="card-footer">
                </div>
            </div>
        </div>
        @endif
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
@stop
