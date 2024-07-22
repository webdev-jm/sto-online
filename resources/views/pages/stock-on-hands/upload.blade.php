@extends('adminlte::page')

@section('title', 'Purchase Orders - '.$account->short_name)

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>[{{$account->account_code}}] {{$account->short_name}} - {{'['.$account_branch->code.'] '.$account_branch->name}} - STOCK ON HAND</h1>
    </div>
    <div class="col-lg-6 text-right">
        <a href="{{route('stock-on-hand.index')}}" class="btn btn-default btn-sm">
            <i class="fa fa-arrow-left mr-1"></i>
            BACK
        </a>
        <a href="{{route('menu', encrypt($account_branch->id))}}" class="btn btn-secondary btn-sm"><i class="fa fa-home mr-1"></i>Main Menu</a>
    </div>
</div>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <livewire:stock-on-hand.uploads :account_branch="$account_branch"/>
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
