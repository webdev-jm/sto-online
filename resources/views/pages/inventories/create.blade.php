@extends('adminlte::page')

@section('title', 'Upload Inventory - '.$account->short_name)

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>[{{$account->account_code}}] {{$account->short_name}} - UPLOAD INVENTORY</h1>
    </div>
    <div class="col-lg-6 text-right">
        <a href="{{route('inventory.index')}}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left mr-1"></i>Back</a>
    </div>
</div>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <livewire:inventory.inventory-upload/>
    </div>
</div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
<script>
</script>
@stop
