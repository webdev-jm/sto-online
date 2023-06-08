@extends('adminlte::page')

@section('title', 'Iventory Sales')

@section('content_header')
    <div class="row">
        <div class="col-lg-6">
            <h1>[{{$branch->branch_code ?? ''}}] {{$branch->branch_name}}</h1>
        </div>
        <div class="col-lg-6 text-right">
            <a href="/home" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left mr-1"></i>BACK</a>
        </div>
    </div>
@stop

@section('content')
    
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
@stop
