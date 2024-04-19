@extends('adminlte::page')

@section('title', 'Edit Location - '.$account->short_name)

@section('content_header')
<div class="row">
    <div class="col-lg-6">
        <h1>[{{$account->account_code}}] {{$account->short_name}} - {{'['.$account_branch->code.'] '.$account_branch->name}} - EDIT LOCATION</h1>
    </div>
    <div class="col-lg-6 text-right">
        <a href="{{route('location.index')}}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left mr-1"></i>Back</a>
        <a href="{{route('location.show', encrypt($location->id))}}" class="btn btn-info btn-sm"><i class="fa fa-list mr-1"></i>DETAILS</a>
    </div>
</div>
@stop

@section('content')
    {!! Form::open(['method' => 'POST', 'route' => ['location.update', encrypt($location->id)], 'id' => 'update_location', 'autocomplete' => 'off']) !!}
    {!! Form::close() !!}

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">EDIT LOCATION</h3>
        </div>
        <div class="card-body">
            
            <div class="row">
                {{-- CODE --}}
                <div class="col-lg-4">
                    <div class="form-group">
                        {!! Form::label('code', 'Location Code') !!}
                        {!! Form::text('code', $location->code, ['class' => 'form-control'.($errors->has('code') ? ' is-invalid' : ''), 'form' => 'update_location']) !!}
                        <p class="text-danger">{{$errors->first('code')}}</p>
                    </div>
                </div>

                {{-- NAME --}}
                <div class="col-lg-4">
                    <div class="form-group">
                        {!! Form::label('name', 'Location Name') !!}
                        {!! Form::text('name', $location->name, ['class' => 'form-control'.($errors->has('name') ? ' is-invalid' : ''), 'form' => 'update_location']) !!}
                        <p class="text-danger">{{$errors->first('name')}}</p>
                    </div>
                </div>

            </div>

        </div>
        <div class="card-footer text-right">
            {!! Form::submit('Edit Location', ['class' => 'btn btn-primary', 'form' => 'update_location']) !!}
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
@stop
