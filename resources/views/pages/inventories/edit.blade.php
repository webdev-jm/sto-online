@extends('adminlte::page')

@section('title', 'Inventories - '.$account->short_name)

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>[{{$account->account_code}}] {{$account->short_name}} - {{'['.$account_branch->code.'] '.$account_branch->name}} - EDIT INVENTORY</h1>
    </div>
    <div class="col-lg-6 text-right">
        <a href="{{route('inventory.index')}}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left mr-1"></i>Back</a>
        <a href="{{route('inventory.show', encrypt($inventory_upload->id))}}" class="btn btn-info btn-sm"><i class="fa fa-list mr-1"></i>DETAILS</a>
    </div>
</div>
@stop

@section('content')
<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">INVENTORY DETAILS</h3>
            </div>
            <div class="card-body py-0">
                <ul class="list-group list-group-unbordered">
                    <li class="list-group-item py-1">
                        <b>USER</b>
                        <span class="float-right">{{$inventory_upload->user->name ?? '-'}}</span>
                    </li>
                    <li class="list-group-item py-1">
                        <b>TOTAL INVENTORY</b>
                        <span class="float-right">{{number_format($inventory_upload->total_inventory, 2) ?? '-'}}</span>
                    </li>
                    <li class="list-group-item py-1">
                        <b>DATE</b>
                        <span class="float-right">{{$inventory_upload->date}}</span>
                    </li>
                </ul>
            </div>
            <div class="card-footer">
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">INVENTORY TOTAL</h3>
            </div>
            <div class="card-body py-0">
                <ul class="list-group list-group-unbordered">
                    @foreach($inventory_locations as $location)
                    <li class="list-group-item py-1">
                        <b>{{$location->code}}</b>
                        <span class="float-right">{{number_format($location->total) ?? '-'}}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
            <div class="card-footer">
            </div>
        </div>
    </div>

    <div class="col-lg-12">
        <livewire:inventory.inventory-details :inventory_upload="$inventory_upload" type="edit"/>
    </div>
</div>
    
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
@stop
