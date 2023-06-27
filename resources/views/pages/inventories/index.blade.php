@extends('adminlte::page')

@section('title', 'Inventory - '.$account->short_name)

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>[{{$account->account_code}}] {{$account->short_name}} - {{'['.$account_branch->code.'] '.$account_branch->name}} - INVENTORIES</h1>
    </div>
    <div class="col-lg-6 text-right">
        <a href="{{route('menu', encrypt($account_branch->id))}}" class="btn btn-secondary btn-sm"><i class="fa fa-home mr-1"></i>Menu</a>
        @can('inventory create')
            <a href="{{route('inventory.create')}}" class="btn btn-primary btn-sm"><i class="fa fa-upload mr-1"></i>Upload Inventory</a>
        @endcan
    </div>
</div>
@stop

@section('content')
{!! Form::open(['method' => 'GET', 'route' => ['inventory.index'], 'id' => 'search_form']) !!}
{!! Form::close() !!}

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">INVENTORY LIST</h3>
        </div>
        <div class="card-body">

            <div class="row mb-1">
                <div class="col-lg-4">
                    <div class="form-group">
                        {!! Form::label('search', 'Search') !!}
                        {!! Form::text('search', $search, ['class' => 'form-control', 'form' => 'search_form', 'placeholder' => 'Search']) !!}
                    </div>
                </div>
            </div>

            <b>{{$inventory_uploads->total()}} total result{{$inventory_uploads->total() > 1 ? 's' : ''}}</b>
            <ul class="list-group">
                @foreach($inventory_uploads as $inventory)
                <li class="list-group-item">
                    <div class="row">
                        <div class="col-lg-4 text-center">
                            <p class="m-0 font-weight-bold">{{$inventory->created_at->diffForHumans()}}</p>
                            <small class="font-weight-bold text-muted">CREATED AT</small>
                        </div>
                        <div class="col-lg-4 text-center">
                            <p class="m-0 font-weight-bold">{{$inventory->user->name}}</p>
                            <small class="font-weight-bold text-muted">NAME</small>
                        </div>
                        <div class="col-lg-4 text-center">
                            <p class="m-0">
                                <a href="{{route('inventory.show', encrypt($inventory->id))}}" class="btn btn-info btn-xs">
                                    <i class="fa fa-list"></i>
                                </a>
                                @can('inventory edit')
                                    <a href="{{route('inventory.edit', encrypt($inventory->id))}}" class="btn btn-success btn-xs">
                                        <i class="fa fa-pen"></i>
                                    </a>
                                @endcan
                                @can('inventory delete')
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
            {{$inventory_uploads->links()}}
        </div>
    </div>

@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
@stop
