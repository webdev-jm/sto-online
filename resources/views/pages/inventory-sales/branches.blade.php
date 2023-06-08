@extends('adminlte::page')

@section('title', 'Branches')

@section('content_header')
    <div class="row">
        <div class="col-lg-6">
            <h1>[{{$account->account_code}}] {{$account->short_name}}</h1>
        </div>
        <div class="col-lg-6 text-right">
            <a href="/home" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left mr-1"></i>BACK</a>
        </div>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">BRANCHES</h3>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($branches as $branch)
                    <div class="col-lg-3">
                        <a href="{{route('inventory-sales.index', encrypt($branch->branch_id))}}" class="btn btn-block btn-app bg-default" style="height: 90%;">
                            <i class="fa fa-user"></i>
                            [{{$branch->branch_code}}] {{$branch->branch_name}}
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="card-footer pb-0">
            {{$branches->links()}}
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
@stop
