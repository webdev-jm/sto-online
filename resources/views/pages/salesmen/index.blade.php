@extends('adminlte::page')

@section('title', 'Salesmen - '.$account->short_name)

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>[{{$account->account_code}}] {{$account->short_name}} - SALESMEN</h1>
    </div>
    <div class="col-lg-6 text-right">
        <a href="{{route('menu', encrypt($account->id))}}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left mr-1"></i>Menu</a>
        @can('salesman create')
            <a href="{{route('salesman.create')}}" class="btn btn-primary btn-sm"><i class="fa fa-plus mr-1"></i>Add Salesman</a>
        @endcan
    </div>
</div>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">SALESMEN LIST</h3>
        </div>
        <div class="card-body">

            <ul class="list-group">
                @foreach($salesmen as $salesman)
                <li class="list-group-item">
                    <div class="row">
                        <div class="col-lg-4 text-center">
                            <p class="m-0">{{$salesman->code}}</p>
                            <b>CODE</b>
                        </div>
                        <div class="col-lg-4 text-center">
                            <p class="m-0">{{$salesman->name}}</p>
                            <b>NAME</b>
                        </div>
                        <div class="col-lg-4 text-center">
                            <p class="m-0">
                                <a href="{{route('salesman.show', encrypt($salesman->id))}}" class="btn btn-info btn-xs">
                                    <i class="fa fa-list"></i>
                                </a>
                                @can('salesman edit')
                                    <a href="{{route('salesman.edit', encrypt($salesman->id))}}" class="btn btn-success btn-xs">
                                        <i class="fa fa-pen"></i>
                                    </a>
                                @endcan
                                @can('salesman delete')
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
            {{$salesmen->links()}}
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
@stop
