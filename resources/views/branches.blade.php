@extends('adminlte::page')

@section('title', 'Inventory - '.$account->short_name)

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>[{{$account->account_code}}] {{$account->short_name}} - BRANCHES</h1>
    </div>
    <div class="col-lg-6 text-right">
        <a href="/home" class="btn btn-secondary btn-sm"><i class="fa fa-user mr-1"></i>Change Account</a>
    </div>
</div>
@stop

@section('content')
    {!! Form::open(['method' => 'GET', 'route' => ['home'], 'id' => 'search_form']) !!}
    {!! Form::close() !!}

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">BRANCHES</h3>
            <div class="card-tools">
                {!! Form::text('search', $search, ['class' => 'form-control form-control-sm', 'placeholder' => 'Search', 'form' => 'search_form']) !!}
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($branches as $branch)
                    <div class="col-lg-3">
                        <a href="{{route('menu', encrypt($branch->id))}}" class="btn btn-block btn-app bg-default ml-0 font-weight-bold">
                            <i class="fa fa-store"></i>
                            [{{$branch->code}}] {{$branch->name}}
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="card-footer">
            {{$branches->links()}}
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
@stop
