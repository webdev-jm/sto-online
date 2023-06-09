@extends('adminlte::page')

@section('title', 'Channel - '.$account->short_name)

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>[{{$account->account_code}}] {{$account->short_name}} - CHANNEL</h1>
    </div>
    <div class="col-lg-6 text-right">
        <a href="{{route('channel.index')}}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left mr-1"></i>Back</a>
        @can('channel edit')
            <a href="{{route('area.edit', encrypt($channel->id))}}" class="btn btn-success btn-sm"><i class="fa fa-pen-alt mr-1"></i>Edit Area</a>
        @endcan
    </div>
</div>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">CHANNEL DETAILS</h3>
                </div>
                <div class="card-body">
        
                    <ul class="list-group list-group-unbordered">
                        <li class="list-group-item p-1">
                            <b>Channel Code</b>
                            <span class="float-right">{{$channel->code ?? '-'}}</span>
                        </li>
                        <li class="list-group-item p-1">
                            <b>Channel Name</b>
                            <span class="float-right">{{$channel->name ?? '-'}}</span>
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
