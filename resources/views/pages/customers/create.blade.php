@extends('adminlte::page')

@section('title', 'Add Customer - '.$account->short_name)

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>[{{$account->account_code}}] {{$account->short_name}} - ADD CUSTOMER</h1>
    </div>
    <div class="col-lg-6 text-right">
        <a href="{{route('customer.index')}}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left mr-1"></i>Back</a>
    </div>
</div>
@stop

@section('content')
    {!! Form::open(['method' => 'POST', 'route' => ['customer.store'], 'id' => 'add_customer', 'autocomplete' => 'off']) !!}
    {!! Form::close() !!}

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">ADD CUSTOMER</h3>
        </div>
        <div class="card-body">
            
            <div class="row">
                {{-- AREA --}}
                <div class="col-lg-4">
                    <div class="form-group">
                        {!! Form::label('area_id', 'Area') !!}
                        {!! Form::select('area_id', $areas, NULL, ['class' => 'form-control'.($errors->has('area_id') ? ' is-invalid' : ''), 'form' => 'add_customer']) !!}
                        <p class="text-danger">{{$errors->first('area_id')}}</p>
                    </div>
                </div>

                {{-- CHANNEL --}}
                <div class="col-lg-4">
                    <div class="form-group">
                        {!! Form::label('channel_id', 'Channel') !!}
                        {!! Form::select('channel_id', $channels, NULL, ['class' => 'form-control'.($errors->has('channel_id') ? ' is-invalid' : ''), 'form' => 'add_customer']) !!}
                        <p class="text-danger">{{$errors->first('channel_id')}}</p>
                    </div>
                </div>

                {{-- CODE --}}
                <div class="col-lg-4">
                    <div class="form-group">
                        {!! Form::label('code', 'Customer Code') !!}
                        {!! Form::text('code', '', ['class' => 'form-control'.($errors->has('code') ? ' is-invalid' : ''), 'form' => 'add_customer']) !!}
                        <p class="text-danger">{{$errors->first('code')}}</p>
                    </div>
                </div>

                {{-- NAME --}}
                <div class="col-lg-4">
                    <div class="form-group">
                        {!! Form::label('name', 'Customer Name') !!}
                        {!! Form::text('name', '', ['class' => 'form-control'.($errors->has('name') ? ' is-invalid' : ''), 'form' => 'add_customer']) !!}
                        <p class="text-danger">{{$errors->first('name')}}</p>
                    </div>
                </div>

            </div>

        </div>
        <div class="card-footer text-right">
            {!! Form::submit('Add Customer', ['class' => 'btn btn-primary', 'form' => 'add_customer']) !!}
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
@stop
