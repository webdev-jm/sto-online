@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')
    {!! Form::open(['method' => 'GET', 'route' => ['home'], 'id' => 'search_form']) !!}
    {!! Form::close() !!}

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">ACCOUNTS</h3>
            <div class="card-tools">
                {!! Form::text('search', $search, ['class' => 'form-control form-control-sm', 'placeholder' => 'Search', 'form' => 'search_form']) !!}
            </div>
        </div>
        <div class="card-body">
            @if(!empty($accounts->total()))
                <div class="row">
                    @foreach($accounts as $account)
                        <div class="col-lg-3">
                            <a href="{{route('branches', encrypt($account->account_id))}}" class="btn btn-block btn-app bg-default ml-0 font-weight-bold">
                                <i class="fa fa-user"></i>
                                [{{$account->account_code}}] {{$account->short_name}}
                            </a>
                        </div>
                    @endforeach
                </div>
            @else
                <ul class="list-group">
                    <li class="list-group-item text-center">No data available.</li>
                </ul>
            @endif
        </div>
        <div class="card-footer">
            {{$accounts->links()}}
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
@stop
