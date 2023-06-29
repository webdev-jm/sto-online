@extends('adminlte::page')

@section('title', 'Sales - '.$account->short_name)

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>[{{$account->account_code}}] {{$account->short_name}} - {{'['.$account_branch->code.'] '.$account_branch->name}} - SALES</h1>
    </div>
    <div class="col-lg-6 text-right">
        <a href="{{route('menu', encrypt($account_branch->id))}}" class="btn btn-secondary btn-sm"><i class="fa fa-home mr-1"></i>Menu</a>
        @can('sales create')
            <a href="{{route('sales.create')}}" class="btn btn-primary btn-sm"><i class="fa fa-upload mr-1"></i>Upload Sales</a>
        @endcan
    </div>
</div>
@stop

@section('content')
{!! Form::open(['method' => 'GET', 'route' => ['sales.index'], 'id' => 'search_form']) !!}
{!! Form::close() !!}

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">SALES UPLOADS</h3>
        </div>
        <div class="card-body">

            <div class="row mb-3">
                <div class="col-lg-4">
                    {!! Form::label('search', 'Search') !!}
                    {!! Form::text('search', $search, ['class' => 'form-control', 'form' => 'search_form', 'placeholder' => 'Search']) !!}
                </div>
            </div>

            <b>{{$sales_uploads->total()}} total result{{$sales_uploads->total() > 1 ? 's' : ''}}</b>
            <ul class="list-group">
                @foreach($sales_uploads as $sale)
                <li class="list-group-item">
                    <div class="row">
                        <div class="col-lg-2 text-center">
                            <p class="m-0 font-weight-bold">{{date('Y-m-d H:i:s a', strtotime($sale->created_at))}}</p>
                            <small class="font-weight-bold text-muted">CREATED AT</small>
                        </div>
                        <div class="col-lg-3 text-center">
                            <p class="m-0 font-weight-bold">{{$sale->user->name ?? '-'}}</p>
                            <small class="font-weight-bold text-muted">USER</small>
                        </div>
                        <div class="col-lg-2 text-center">
                            <p class="m-0 font-weight-bold">{{number_format($sale->sku_count) ?? 0}}</p>
                            <small class="font-weight-bold text-muted">COUNT</small>
                        </div>
                        <div class="col-lg-3 text-center">
                            <p class="m-0 font-weight-bold">{{number_format($sale->total_amount, 2) ?? 0}}</p>
                            <small class="font-weight-bold text-muted">AMOUNT</small>
                        </div>
                        <div class="col-lg-2 text-center">
                            <p class="m-0">
                                <a href="{{route('sales.show', encrypt($sale->id))}}" class="btn btn-info btn-xs">
                                    <i class="fa fa-list"></i>
                                </a>
                                @can('sales edit')
                                    <a href="{{route('sales.edit', encrypt($sale->id))}}" class="btn btn-success btn-xs">
                                        <i class="fa fa-pen"></i>
                                    </a>
                                @endcan
                                @can('sales delete')
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
            {{$sales_uploads->links()}}
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
@stop
