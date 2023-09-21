@extends('adminlte::page')

@section('title', 'Sales - '.$account->short_name)

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>[{{$account->account_code}}] {{$account->short_name}} - {{'['.$account_branch->code.'] '.$account_branch->name}} - SALES</h1>
    </div>
    <div class="col-lg-6 text-right">
        <a href="{{route('menu', encrypt($account_branch->id))}}" class="btn btn-secondary btn-sm"><i class="fa fa-home mr-1"></i>Menu</a>
        <a href="{{route('sales.index')}}" class="btn btn-primary btn-sm"><i class="fa fa-list mr-1"></i>Upload List</a>
    </div>
</div>
@stop

@section('content')
{!! Form::open(['method' => 'GET', 'route' => ['sales.dashboard'], 'id' => 'filter-sales']) !!}
{!! Form::close() !!}

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">SALES UPLOADS</h3>
        </div>
        <div class="card-body">

            <div class="row">

                <div class="col-lg-2">
                    <div class="form-group">
                        {!! Form::number('year', date('Y'), ['class' => 'form-control form-control-sm', 'form' => 'filter-sales', 'placeholder' => 'Year']) !!}
                    </div>
                </div>

                <div class="col-lg-2">
                    <div class="form-group">
                        {!! Form::number('month', '', ['class' => 'form-control form-control-sm', 'form' => 'filter-sales', 'placeholder' => 'Month']) !!}
                    </div>
                </div>

                <div class="col lg-2">
                    <button type="submit" class="btn btn-info btn-sm" form="filter-sales"><i class="fa fa-filter mr-1"></i>Filter</button>
                </div>

            </div>

            <ul class="list-group">
                @foreach($sales as $sale)
                <li class="list-group-item">
                    <div class="row">
                        <div class="col-lg-3 text-center">
                            <p class="m-0 font-weight-bold">{{$sale->year}}</p>
                            <small class="font-weight-bold text-muted">YEAR</small>
                        </div>
                        <div class="col-lg-3 text-center">
                            <p class="m-0 font-weight-bold">{{date('F', strtotime($sale->year.'-'.($sale->month < 10 ? '0'.$sale->month : $sale->month).'-01'))}}</p>
                            <small class="font-weight-bold text-muted">MONTH</small>
                        </div>
                        <div class="col-lg-3 text-center">
                            <p class="m-0 font-weight-bold">{{number_format($sale->si_total, 2) ?? 0}}</p>
                            <small class="font-weight-bold text-muted">SI AMOUNT</small>
                        </div>
                        <div class="col-lg-3 text-center">
                            <p class="m-0 font-weight-bold">{{number_format($sale->cm_total, 2) ?? 0}}</p>
                            <small class="font-weight-bold text-muted">CM AMOUNT</small>
                        </div>
                    </div>
                </li>
                @endforeach
            </ul>
            
        </div>
        <div class="card-footer">
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
@stop
