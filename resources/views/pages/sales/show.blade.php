@extends('adminlte::page')

@section('title', 'Sales - '.$account->short_name)

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>[{{$account->account_code}}] {{$account->short_name}} - {{'['.$account_branch->code.'] '.$account_branch->name}} - SALES</h1>
    </div>
    <div class="col-lg-6 text-right">
        <a href="{{route('sales.index')}}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left mr-1"></i>Back</a>
        @can('sales edit')
            <a href="{{route('sales.edit', encrypt($sales_upload->id))}}" class="btn btn-success btn-sm"><i class="fa fa-pen-alt mr-1"></i>Edit</a>
        @endcan
    </div>
</div>
@stop

@section('content')
<div class="row">
    <div class="col-lg-4">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">HEADER</h3>
            </div>
            <div class="card-body py-0">
                <ul class="list-group list-group-unbordered">
                    <li class="list-group-item py-1">
                        <b>USER</b>
                        <span class="float-right">
                            <img class="img-circle elevation-2 mr-1" src="{{asset(!empty($sales_upload->user->profile_picture_url) ? $sales_upload->user->profile_picture_url.'-small.jpg': '/images/Windows_10_Default_Profile_Picture.svg')}}" alt="User Avatar" width="20px" height="20px">
                            {{$sales_upload->user->name ?? '-'}}
                        </span>
                    </li>
                    <li class="list-group-item py-1">
                        <b>TOTAL AMOUNT</b>
                        <span class="float-right">{{number_format($sales_upload->total_amount, 2) ?? '-'}}</span>
                    </li>
                    <li class="list-group-item py-1">
                        <b>TOTAL AMOUNT INC VAT</b>
                        <span class="float-right">{{number_format($sales_upload->total_amount_vat, 2) ?? '-'}}</span>
                    </li>
                    <li class="list-group-item py-1">
                        <b>TOTAL CM AMOUNT</b>
                        <span class="float-right">{{number_format($sales_upload->total_cm_amount, 2) ?? '-'}}</span>
                    </li>
                    <li class="list-group-item py-1">
                        <b>TOTAL CM AMOUNT INC VAT</b>
                        <span class="float-right">{{number_format($sales_upload->total_cm_amount_vat, 2) ?? '-'}}</span>
                    </li>
                </ul>
            </div>
            <div class="card-footer">
            </div>
        </div>
    </div>
    <div class="col-lg-12">
        <livewire:sales.products-view :sales_upload="$sales_upload"/>
    </div>
</div>
    
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
@stop
