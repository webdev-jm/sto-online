@extends('adminlte::page')

@section('title', 'RTV - '.$account->short_name)

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>[{{$account->account_code}}] {{$account->short_name}} - {{'['.$account_branch->code.'] '.$account_branch->name}} - RTV</h1>
    </div>
    <div class="col-lg-6 text-right">
        <a href="{{route('menu', encrypt($account_branch->id))}}" class="btn btn-secondary btn-sm"><i class="fa fa-home mr-1"></i>Main Menu</a>
    </div>
</div>
@stop

@section('content')
    <div class="card card-outline">
        <div class="card-header">
            <h3 class="card-title">RTV LIST</h3>
            <div class="card-tools">
                @can('rtv create')
                    <a href="{{route('rtv.create')}}" class="btn btn-xs btn-primary">
                        <i class="fa fa-plus mr-1"></i>
                        ADD RTV
                    </a>
                @endcan
                @can('rtv upload')
                    <a href="{{route('rtv.upload')}}" class="btn btn-xs btn-success">
                        <i class="fa fa-upload mr-1"></i>
                        UPLOAD RTV
                    </a>
               @endcan
            </div>
        </div>
        <div class="card-body">

        </div>
        <div class="card-footer">
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
